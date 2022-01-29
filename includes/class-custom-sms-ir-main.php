<?php

require_once __DIR__ . '/../config.php';

class MainSMSClass
{

    public function __construct()
    {
        $this->setup_order_status();

        add_action('woocommerce_order_status_changed', [$this, 'order_status_sms'], 99, 4);

        add_action('update_post_metadata', [$this, 'update_order_metadata'], 10, 4);
    }

    public function setup_order_status()
    {
        function sms_wc_register_post_statuses()
        {
            register_post_status('wc-box', array(
                'label' => _x('Boxing', 'WooCommerce Order status', 'text_domain'),
                'public' => true,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop('Approved (%s)', 'Approved (%s)', 'text_domain')
            ));
        }

        add_filter('init', 'sms_wc_register_post_statuses');

        function sms_wc_add_order_statuses($order_statuses)
        {
            $order_statuses['wc-box'] = _x('Boxing', 'WooCommerce Order status', 'text_domain');
            return $order_statuses;
        }

        add_filter('wc_order_statuses', 'sms_wc_add_order_statuses');
    }

    public function update_order_metadata($check, $object_id, $meta_key, $meta_value)
    {
        if ('marsule' === $meta_key && (mb_strlen($meta_value) > 1) && $meta_value !== get_post_meta($object_id, $meta_key, true)) {
            $this->order_marsule_meta_updated($object_id, $meta_value);
        }

        return $check;
    }

    public function order_marsule_meta_updated($object_id, $meta_value)
    {
        $order = new WC_Order($object_id);

        $this->order_completed_send_marsule_sms($object_id, $meta_value);

        $order->set_status('completed');

        $order->save();
    }

    public function order_status_sms($order_id, $old_status, $new_status, $order)
    {
        if ($new_status == "on-hold") {
            $this->order_on_hold($order);
        }
        if ($new_status == "box") {
            $this->order_boxing($order);
        }
        if ($new_status == "processing") {
            $this->order_processing($order);
        }
        if ($new_status == "cancelled") {
            $this->order_cancelled($order);
        }
    }

    protected function get_name_variable($order, $with_title = true)
    {
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();

        return $first_name . ' ' . $last_name . ($with_title ? " عزیز،" : '') . "";
    }

    protected function get_order_phone_number($order)
    {
        return $order->get_billing_phone();
    }


    public function order_on_hold($order)
    {
        $order->add_order_note('[name]،
            سفارش شما با موفقیت ثبت شد و در انتظار پرداخت است
            
            روش پرداخت: [payment]
            [order_info]
            جمع مبلغ: [amount]
            شماره سفارش: [order]');


        $this->send_sms($this->get_order_phone_number($order), '61629', [
            'name' => $this->get_name_variable($order, false),
            'payment' => 'کارت به کارت 💳' . "\n" . "\n" . "به‌نام: علیرضا اشرفی",
            'order_info' => "" . "شماره کارت: " . SMS_BANK_CARD . "\n" . "بانک ملی ایران" . "\n",
            'amount' => number_format($order->get_total()) . " تومان",
            'order' => $order->id . "\n" . "\n" . SMS_SITE_URL
        ]);
    }


    public function order_processing($order)
    {
        $order->add_order_note(
            '[name]
            سفارش‌تان ثبت شد و در حال پردازش آن هستیم.
            شما می‌توانید وضعیت‌ آن را از نشانی زیر پیگیری کنید:
            شماره سفارش: [order]');

        $this->send_sms($this->get_order_phone_number($order), '61576', [
            'name' => $this->get_name_variable($order),
            'order' => $order->id . "\n" . "\n" . SMS_SITE_MY_ACCOUNT_URL,
        ]);
    }

    public function order_boxing($order)
    {
        $order->add_order_note('[name]
            سفارش‌تان درحال بسته‌بندی است
            کد مرسوله پستی برای شما پیامک خواهد شد
            
            شماره سفارش: [order]');


        $this->send_sms($this->get_order_phone_number($order), '61588', [
            'name' => $this->get_name_variable($order),
            'order' => $order->id . "\n" . "\n" . SMS_SITE_URL
        ]);
    }

    public function order_cancelled($order)
    {
        $order->add_order_note('[name]
            متاسفانه سفارش شما به علت عدم پرداخت لغو و آرشیو شد.
            شناسه سفارش: [order]');


        $this->send_sms($this->get_order_phone_number($order), '61579', [
            'name' => $this->get_name_variable($order),
            'order' => $order->id . "\n" . "مبلغ: " . number_format($order->get_total()) . " تومان" . "\n" . SMS_SITE_URL
        ]);
    }

    public function order_completed_send_marsule_sms($order_id, $code)
    {
        $order = new WC_Order($order_id);

        $phone = $order->get_billing_phone();

        $order->add_order_note('مشتری گرامی
            سفارش کد [id] شما جهت ارسال با "پست‌پیشتاز"، به اداره پست تحویل داده شد.
            لطفا قبل از تحویل مرسوله از سالم بودن آن اطمینان حاصل فرمائید.
            
            کد رهگیری: [code]
            سامانه رهگیری: https://tracking.post.ir
            
            باتشکر از اعتماد شما
            [support]');


        $this->send_sms($phone, '61245', [
            'id' => $order_id,
            'code' => $this->normalize_marsule_code($code),
            'support' => "\n" . SMS_SITE_FA_NAME . "\n" . SMS_SITE_URL
        ]);
    }


    public function send_sms($phones, $template, $parameters)
    {
        $phones = explode(',', $phones);

        $ParameterArray = [];
        foreach ($parameters as $key => $value) {
            $ParameterArray[] = [
                "Parameter" => $key,
                "ParameterValue" => $value
            ];
        }

        foreach ($phones as $phone) {

            try {
                $data = array(
                    "ParameterArray" => $ParameterArray,
                    "Mobile" => $phone,
                    "TemplateId" => $template
                );

                $this->_execute($data);
            } catch (Exception $e) {
                var_dump($e->getMessage());
            }
        }

        return true;
    }

    function normalize_marsule_code($code)
    {
        $code = str_replace('|', '1', $code);
        $code = str_replace('.', '0', $code);
        $code = str_replace(' ', '', $code);

        $newNumbers = range(0, 9);
        // 1. Persian HTML decimal
        $persianDecimal = array('&#1776;', '&#1777;', '&#1778;', '&#1779;', '&#1780;', '&#1781;', '&#1782;', '&#1783;', '&#1784;', '&#1785;');
        // 2. Arabic HTML decimal
        $arabicDecimal = array('&#1632;', '&#1633;', '&#1634;', '&#1635;', '&#1636;', '&#1637;', '&#1638;', '&#1639;', '&#1640;', '&#1641;');
        // 3. Arabic Numeric
        $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        // 4. Persian Numeric
        $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');

        $code = str_replace($persianDecimal, $newNumbers, $code);
        $code = str_replace($arabicDecimal, $newNumbers, $code);
        $code = str_replace($persian, $newNumbers, $code);
        $code = str_replace($arabic, $newNumbers, $code);


        $code = preg_replace('/\D/', '', $code);
        return $code;
    }

    function background_curl_request($url, $method, $post_parameters)
    {
        $path = __DIR__ . '/../logs/';
        $command = "/usr/bin/curl -H 'Content-Type: application/json'  -X '" . $method . "' -d '" . $post_parameters . "' --url '" . $url . "' >> '{$path}exec-curl.log' > /dev/null &";

        file_put_contents($path . 'backend-commands.log', $command . "\n\n", FILE_APPEND);

        if (SMS_PROD) {
            exec($command);
        }
    }

    private function _execute($postData)
    {
        $log = "Phone:" . $postData['Mobile'];
        $log .= "\nTemplate: " . $postData['TemplateId'];
        $log .= "\nBackend: " . SMS_RUN_IN_BACKGROUND;
        $log .= "\nData: ";
        foreach ($postData['ParameterArray'] as $parameter) {
            $log .= "\n(" . $parameter['Parameter'] . ": " . $parameter['ParameterValue'] . ")";
        }
        $log .= "\n----------------\n";

        file_put_contents((__DIR__ . '/../logs/') . 'send.log', $log, FILE_APPEND);

        $postData['UserApiKey'] = SMS_USER_API_KEY;
        $postData['SecretKey'] = SMS_SECRET_KEY;
        $postString = json_encode($postData);

        $url = 'https://RestfulSms.com/api/UltraFastSend/UserApiKey';


        if (!SMS_PROD) {
            return false;
        }

        if (SMS_RUN_IN_BACKGROUND) {
            $this->background_curl_request($url, 'post', $postString);
            return 'exec';
        }

        $ch = curl_init($url);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            )
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);

        $result = curl_exec($ch);
        file_put_contents((__DIR__ . '/../logs/') . 'php-curl.log', $result . "\n", FILE_APPEND);

        curl_close($ch);

        return 'php';
    }

}