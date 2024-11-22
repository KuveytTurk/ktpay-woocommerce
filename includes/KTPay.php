<?php

class KTPay
{
    public $success_url;
    public $fail_url;
    public $check_onus_card_test_url ="https://boatest.kuveytturk.com.tr/boa.virtualpos.services/KTPay/IsOnusCard";
    public $check_onus_card_prod_url ="https://sanalpos.kuveytturk.com.tr/ServiceGateWay/KTPay/IsOnusCard";
    public $check_installment_definition_test_url = "https://boatest.kuveytturk.com.tr/boa.virtualpos.services/KTPay/GetMerchantInstallmentDefinition";
    public $check_installment_definition_prod_url = "https://sanalpos.kuveytturk.com.tr/ServiceGateWay/KTPay/GetMerchantInstallmentDefinition";
    public $td_payment_prod_url = "https://sanalpos.kuveytturk.com.tr/ServiceGateWay/KTPay/Payment";
    public $td_payment_test_url = "https://boatest.kuveytturk.com.tr/boa.virtualpos.services/KTPay/Payment";
    public $ntd_payment_prod_url = "https://sanalpos.kuveytturk.com.tr/ServiceGateWay/Home/Non3DPayGate";
    public $ntd_payment_test_url = "https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/Non3DPayGate";
    public $provision_url = "https://sanalpos.kuveytturk.com.tr/ServiceGateWay/KTPay/Provision"; // Provizyon için xml'in post edileceği adres
    public $provision_test_url = "https://boatest.kuveytturk.com.tr/boa.virtualpos.services/KTPay/Provision"; // Provizyon için xml'in post edileceği adres
    public $environment = "TEST"; // Test ortamı "TEST", gerçek ortam için "PROD"
    public $merchant_id; // Üye işyeri numarası
    public $pos_terminal_id; // Pos Terminal numarası
    public $api_user_name; // Api Kullanıcı Adı
    public $api_user_password; // Api Şifresi
    public $customer_id;
    public $currency_code = "949";
    public $lang = "tr";
    public $hash_data;
    public $merchant_order_id;
    public $order_id;
    public $amount;
    public $installment_count;
    public $card_holder_name;
    public $card_number;
    public $card_expire_month;
    public $card_expire_year;
    public $card_cvv;
    public $customer_ip;
    public $customer_mail;
    public $phone_number;
    public $md;

    public function __construct()
    {
    }

    public function set_payment_params($params)
    {
        $this->environment = $params['environment'];
        $this->merchant_id = $params['merchant_id'];
        //$this->pos_terminal_id = $params['pos_terminal_id'];
        $this->customer_id = $params['customer_id'];
        $this->api_user_name = $params['api_user_name'];
        $this->api_user_password = $params['api_user_password'];
        $this->success_url = $params['success_url'];
        $this->fail_url = $params['fail_url'];
        $this->merchant_order_id = $params['merchant_order_id'];
        $amount=$params['amount']*100;
        $amount=str_replace(',','',$amount);
        $amount=str_replace('.','',$amount);
        
        $this->amount =  $amount;
        $this->installment_count = $params['installment_count'] > 1 ? $params['installment_count'] : "1";
        $this->currency_code = $params['currency_code'];
        $this->customer_ip = $params['customer_ip'];
        $this->customer_mail = $params['customer_mail'];
        $this->phone_number = $params['phone_number'];
        $this->card_holder_name = $params['card_holder_name'];
        $this->card_number = $params['card_number'];
        $this->card_expire_month = $params['card_expire_month'];
        $this->card_expire_year = $params['card_expire_year'];
        $this->card_cvv = $params['card_cvv'];

        //hash 
        $hashValue = $this->merchant_id . $this->merchant_order_id . $this->amount . $this->success_url . $this->fail_url . $this->api_user_name;

        $this->hash_data = KTPayConfig::get_hash_data($hashValue, $this->api_user_password);
    }

    function set_provision_params($params) {
        $this->api_user_name = $params['api_user_name'];
        $this->api_user_password = $params['api_user_password'];
        $this->merchant_id = $params['merchant_id'];
        $this->customer_id = $params['customer_id'];
        $this->merchant_order_id = $params['merchant_order_id'];
        $this->order_id = $params['order_id'];
        $this->md = $params['md'];
        
        $amount=$params['amount']*100;
        $amount=str_replace(',','',$amount);
        $amount=str_replace('.','',$amount);
        $this->amount=$amount;

        $hashValue = $this->merchant_id . $this->merchant_order_id . $this->amount . $this->api_user_name;
        $this->hash_data = KTPayConfig::get_hash_data($hashValue, $this->api_user_password);
    }

    function send_request($params)
    {
        $response = wp_remote_post($params['url'], array(
            'headers' => array('Content-type' => $params['isJson'] ? 'application/json' : 'text/plain', 'user-agent'=>$_SERVER['HTTP_USER_AGENT']),
            'body' => $params['data'],
            'timeout' => 30,
            'method' => 'POST',
            'data_format' => 'body',
            'blocking' => true
        ));

        if(is_wp_error($response))
        {
            return array(
                'success'=>false,
                'message'=>$response->get_error_message()
            );
        }
        else
        {
            return array(
                'success'=>true,
                'data'=>$params['returnTypeJsn'] ? json_decode(wp_remote_retrieve_body($response),true) : wp_remote_retrieve_body($response)
            );
        }

    }

    function init_non3d_request_body()
    {
        $xml_body = "<KuveytTurkVPosMessage xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">
        <HashData>$this->hash_data</HashData>
        <MerchantId>$this->merchant_id</MerchantId>
        <CustomerId>$this->customer_id</CustomerId>
        <UserName>$this->api_user_name</UserName>
        <CardNumber>$this->card_number</CardNumber>
        <CardExpireDateYear>$this->card_expire_year</CardExpireDateYear>
        <CardExpireDateMonth>$this->card_expire_month</CardExpireDateMonth>
        <CardCVV2>$this->card_cvv</CardCVV2>
        <CardHolderName>$this->card_holder_name</CardHolderName>
        <BatchID>0</BatchID>
        <TransactionType>Sale</TransactionType>
        <InstallmentCount>$this->installment_count</InstallmentCount>
        <InstallmentMaturityCommisionFlag>0</InstallmentMaturityCommisionFlag>
        <Amount>$this->amount</Amount>
        <DisplayAmount>$this->amount</DisplayAmount>
        <CurrencyCode>$this->currency_code</CurrencyCode>
        <MerchantOrderId>$this->merchant_order_id</MerchantOrderId>
        <TransactionSecurity>1</TransactionSecurity>
        <TransactionSide>Sale</TransactionSide>
        </KuveytTurkVPosMessage>";
        return $xml_body;
    }

    function init_3d_request_body()
    {
        $json_body="{
            \"paymentType\": 1,
            \"language\": 1,
            \"merchantOrderId\": \"".$this->merchant_order_id."\",
            \"successUrl\": \"".$this->success_url."\",
            \"failUrl\": \"".$this->fail_url."\",
            \"merchantId\":" .$this->merchant_id.",
            \"customerId\":".$this->customer_id.",
            \"username\": \"".$this->api_user_name."\",
            \"hashData\": \"".$this->hash_data."\",
            \"amount\": \"".$this->amount."\",
            \"currency\": \"".$this->currency_code."\",
            \"installmentCount\":". $this->installment_count. ",
            \"customer\": {";

            if($this->phone_number!=null || $this->phone_number!= "")
            {
                $json_body.="\"phoneNumber\": {
                \"cc\": \"90\",
                \"subscriber\": \"".$this->phone_number."\"},";
            }

            if($this->customer_mail!=null || $this->customer_mail!="")
            {
                $json_body.= "\"email\": \"$this->customer_mail\",";
            }
              
            $json_body.= "\"ipAddress\": \"$this->customer_ip\"},
            \"card\": {
            \"cardNumber\": \"".$this->card_number."\",
            \"cardHolderName\": \"".$this->card_holder_name."\",
            \"expireMonth\": \"".$this->card_expire_month."\",
            \"expireYear\": \"".$this->card_expire_year."\",
            \"securityCode\": \"".$this->card_cvv."\"}}";

        return $json_body;
    }

    function init_provision_request_body()
    {
        $json_body = "{
         \"language\":1,
         \"merchantId\":\"$this->merchant_id\",
         \"customerId\":\"$this->customer_id\",
         \"username\":\"$this->api_user_name\",
         \"hashData\":\"$this->hash_data\",
         \"amount\":\"$this->amount\",
         \"orderId\":\"$this->order_id\",
         \"merchantOrderId\":\"$this->merchant_order_id\",
         \"successUrl\": \"".$this->success_url."\",
         \"failUrl\": \"".$this->fail_url."\",
         \"md\":\"$this->md\"}
        ";

        return $json_body;
    }

    function init_check_installment_definition_body($merchant_id)
    {
        $json_body = "{\"merchantId\": ".$merchant_id."}";

        return $json_body;
    }

    function callback($action, $params)
    {
        if($action == 'success')
        {     
            $response=$this->provision($params);
            
            if($response['success'])
            {
                if($response['data']['ResponseCode'] == "00")
                {
                    $result = array(
                        'status' => 'success',
                        'message' => $response['data']['ResponseMessage']
                    );
                }
                else
                {
                    $result = array(
                        'status' => 'error',
                        'message' => $response['data']['ResponseMessage']
                    );
                }
            }
            else
            {
                $result = array(
                    'status' => 'error',
                    'message' => $response['message']
                );
            }

        }
        else
        {
            $result = array(
                'status' => 'error',
                'message' => $params['response_message']
            );
        }
        return $result;
    }

    private function provision($params)
    {
        $this->set_provision_params($params);      
        $params=array(
            'data'=>$this->init_provision_request_body(),
            'url'=> $this->environment=='TEST' ? $this->provision_test_url : $this->provision_url,
            'isJson' => true,
            'returnTypeJsn' => true
        );
        $response=$this->send_request($params);

        return $response;
    }

    function check_installment_definition($environment, $merchant_id){
        try 
        {
            $params = array(
                'data' => $this->init_check_installment_definition_body($merchant_id),
                'url' => $environment == 'TEST' ? $this->check_installment_definition_test_url : $this->check_installment_definition_prod_url,
                'isJson' => true,
                'returnTypeJsn' => true,
            );
            $response=$this->send_request($params);
        
            if($response['success'] && $response['data']['success'])
            {
                if(count($response['data']['success'])> 0)
                {
                    KTPayConfig::update_all_installments($response['data']['value']);
                    header("Refresh:0");
                }               
            }
            else
            {
                echo "<script type='text/javascript'>alert('Taksit tanımı kontrol edilemedi');</script>";
            }
        } catch (Exception $e) {
            echo "<script type='text/javascript'>alert('Taksit tanımı kontrol edilemedi');</script>";
        }
        
    }
    
}