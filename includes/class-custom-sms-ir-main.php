<?php

require_once __DIR__ . '/../config.php';

class MainSMSClass
{

    public function __construct()
    {
        add_action('woocommerce_order_status_changed', [$this, 'order_status_sms'], 99, 4);

        add_action('update_post_metadata', [$this, 'update_order_metadata'], 10, 4);

    }

    public function update_order_metadata($check, $object_id, $meta_key, $meta_value)
    {
        if ('marsule' === $meta_key && $meta_value !== get_post_meta($object_id, $meta_key, true)) {
            $this->order_marsule_meta_updated($object_id, $meta_value);
        }
    }

    public function order_marsule_meta_updated($object_id, $meta_value)
    {
        $order = new WC_Order($object_id);

        $order->set_status('completed');

        $order->save();
    }

    public function order_status_sms($order_id, $old_status, $new_status, $order)
    {
        if ($new_status == "completed") {
            $this->order_completed($order_id);
        }
        if ($new_status == "processing") {
            $this->order_processing($order_id);
        }
    }

    public function order_completed($order_id)
    {
        $order = new WC_Order($order_id);

        $phone = $order->get_billing_phone();

        $template = '
        مشتری گرامی
        سفارش کد [id] شما جهت ارسال با "پست‌پیشتاز"، به اداره پست تحویل داده شد.
        لطفا قبل از تحویل مرسوله از سالم بودن آن اطمینان حاصل فرمائید.

        کد رهگیری: [code]
        سامانه رهگیری: https://tracking.post.ir

        باتشکر از اعتماد شما
        [support]';

        $order->add_order_note($template);


        $this->send_sms($phone, '61245', [
            'id' => $order_id,
            'code' => get_post_meta($order_id, 'marsule', true),
            'support' => "\n" . SMS_SITE_FA_NAME . "\n" . SMS_SITE_URL
        ]);
    }

    public function order_processing($order_id)
    {
        $order = new WC_Order($order_id);

        $phone = $order->get_billing_phone();
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();

        $template = '
        [name]
        سفارش‌تان ثبت شد و در حال پردازش آن هستیم.
        شما می‌توانید وضعیت‌ آن را از نشانی زیر پیگیری کنید:
        شماره سفارش: [order]';

        $order->add_order_note($template);

        $this->send_sms($phone, '61576', [
            'name' => "${$first_name} ${last_name} عزیز،",
            'order' => "${order_id}\n" . "\n" . SMS_SITE_MY_ACCOUNT_URL,
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

    function background_curl_request($url, $method, $post_parameters)
    {
        $path = __DIR__ . '/../logs/';
        $command = "/usr/bin/curl -H 'Content-Type: application/json'  -X '" . $method . "' -d '" . $post_parameters . "' --url '" . $url . "' >> '{$path}exec-curl.log' ";

        file_put_contents($path . 'backend-commands.log', $command . "\n\n", FILE_APPEND);

        if (SMS_PROD) {
            shell_exec($command);
        }
    }

    private function _execute($postData)
    {
        $run_in_backend = function_exists('exec');
        $log = "Phone:" . $postData['Mobile'];
        $log .= "\nTemplate: " . $postData['TemplateId'];
        $log .= "\nBackend: " . $run_in_backend;
        $log .= "\nData: ";
        foreach ($postData['ParameterArray'] as $parameter) {
            $log .= "\n(" . $parameter['Parameter'] . ": " . $parameter['ParameterValue'] . ")";
        }
        $log .= "\n----------------";

        file_put_contents((__DIR__ . '/../logs/') . 'send.log', $log, FILE_APPEND);

        $postData['UserApiKey'] = SMS_USER_API_KEY;
        $postData['SecretKey'] = SMS_SECRET_KEY;
        $postString = json_encode($postData);

        $url = 'https://RestfulSms.com/api/UltraFastSend/UserApiKey';

        if ($run_in_backend) {
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
        curl_close($ch);

        die(var_dump($result));
        return 'php';
    }

}