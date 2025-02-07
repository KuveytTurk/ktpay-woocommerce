<?php

/*
 * Plugin Name: KT Payment for woocommerce
 * Plugin URI: 
 * Description: KT Payment for woocommerce
 * Version: 1.0.0
 * Author: Architecht
 * Author URI: https://architecht.com
 * Domain Path: /i18n/languages/
 */

 if (!defined('ABSPATH')) {
    exit;
}

include plugin_dir_path(__FILE__) . 'includes/class-ktconfig.php';
include plugin_dir_path(__FILE__) . 'includes/KTPay.php';
add_action('plugins_loaded', 'woocommerce_ktpay_init', 0);


function woocommerce_ktpay_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_KTPay_Gateway extends WC_Payment_Gateway{
        public function __construct(){
            $this->id = 'ktpay';
            $this->method_title = __('KT Payment Form','kt-payment-module');
            $this->method_description = __('KT Payment Module','kt-payment-module');
            $this->icon = plugins_url('/kt-payment-module/assets/img/kuveytturk.svg', dirname(__FILE__));
            $this->has_fields = false;
            $this->supports = array('products', 'refunds');
            $this->rates = get_option('kt_pay_rates');
            $this->installment_count= get_option('kt_pay_installments');
            $this->init_form_fields();
            $this->init_settings();
            
            $this->title='Kredi Kartı ile Öde';
            $this->description='Kuveyttürk ödeme altyapısıyla öde';
            $this->order_button_text = 'Öde';
            $this->enabled = $this->settings['enabled'];
            $this->td_mode= $this->settings['td_mode'];
            $this->td_overamount= $this->settings['td_overamount'];
            $this->merchant_id= $this->settings['merchant_id'];
            $this->customer_id= $this->settings['customer_id'];
            $this->api_username= $this->settings['api_username'];
            $this->api_password= $this->settings['api_password'];
            $this->environment= $this->settings['environment'];
            $this->installment_mode= $this->settings['installment_mode'];

            add_action('init', array($this,'check_3d_response'));
            add_action('woocommerce_api_wc_ktpay_gateway', array($this,'check_3d_response'));
            add_action('admin_notices', array($this, 'check_ktpay_enabled'));
            if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            } else {
                add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
            }
            add_action('woocommerce_receipt_ktpay', array($this, 'receipt_page'));
            add_action('woocommerce_api_checkInstallmentDefinition' . $this->id, array($this, 'checkInstallmentDefinition'));
        }

        function check_ktpay_enabled()
        {
            global $woocommerce;

            if ($this->enabled == 'no') {
                return;
            }
        }

        function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Aktif/Pasif', 'kt-payment-module'),
                    'label' => __('KTPay Modülü aktifleştirme', 'kt-payment-module'),
                    'type' => 'checkbox',
                    'default' => 'no',
                ),
                'environment' => array(
                    'title' => __('İşlem Ortamı', 'kt-payment-module'),
                    'type' => 'select',
                    'default' => 'off',
                    'options' => array(
                        'TEST' => __('TEST', 'kt-payment-module'),
                        'PROD' => __('PROD', 'kt-payment-module'),
                    ),
                ),
                'merchant_id' => array(
                    'title' => __('Üye İşyeri Numarası', 'kt-payment-module'),
                    'type' => 'text',
                    'desc_tip' => __('Kuveyttürk tarafından verilen Üye İşyeri Numarası', 'kt-payment-module'),
                ),
                'customer_id' => array(
                    'title' => __('Müşteri Numarası', 'kt-payment-module'),
                    'type' => 'text',
                    'desc_tip' => __('Küveyttürk tarafından verilen Müşteri Nuamrası', 'kt-payment-module'),
                ),
                'api_username' => array(
                    'title' => __('Kullanıcı Adı', 'kt-payment-module'),
                    'type' => 'text',
                    'desc_tip' => __('Kuveyttürk tarafından verilen kullanıcı adı', 'kt-payment-module'),
                ),
                'api_password' => array(
                    'title' => __('Şifre', 'kt-payment-module'),
                    'type' => 'text',
                    'desc_tip' => __('Kuveyttürk tarafından verilen kullanıcı şifresi', 'kt-payment-module'),
                ),
                'td_mode' => array(
                    'title' => __('3D Onayı Gereksin mi?', 'kt-payment-module'),
                    'type' => 'select',
                    'default' => 'off',
                    'options' => array(
                        'off' => __('KAPALI', 'kt-payment-module'),
                        'on' => __('AÇIK', 'kt-payment-module'),
                    ),
                ),
                'td_overamount' => array(
                    'title' => __('3d Secure Geçilecek Tutar', 'kt-payment-module'),
                    'type' => 'text',
                    'description' => __('Belirli bir tutardan sonra 3D onayına düşsün isteniyorsa tutar girin', 'kt-payment-module'),

                ),
                'installment_mode' => array(
                    'title' => __('Taksit Seçenekleri', 'kt-payment-module'),
                    'type' => 'select',
                    'default' => 'off',
                    'description' => __('Sadece Onus kartlar için taksit yapılabilmektedir','kt-payment-module'),
                    'options' => array(
                        'off' => __('KAPALI', 'kt-payment-module'),
                        'on' => __('AÇIK', 'kt-payment-module'),
                    ),
                ),
            );
        }

        public function admin_options()
        {
            if($this->rates==null && $this->environment!=null && strlen(trim($this->environment)) != 0  && $this->merchant_id!=null && strlen(trim($this->merchant_id)) != 0)
            {
                $ktpay=new KTPay();
                $ktpay->check_installment_definition($this->environment, $this->merchant_id);
                $this->installment_count=get_option('kt_pay_installments');
                $this->rates=get_option('kt_pay_rates');            
            }
            
            if (isset($_POST['kt_pay_rates'])) {
                KTPayConfig::register_all_installments($this->installment_count);
                $this->installment_count=get_option('kt_pay_installments');
                $this->rates=get_option('kt_pay_rates'); 
            }
            $ktpay_file_url = plugins_url('/',__FILE__ );
            echo '<img src="' . esc_url($ktpay_file_url) . 'assets/img/kuveytturk.svg" width="150px" style="margin-bottom:10px" />';
            echo '<h1>KTPay Sanal Pos Ayarları</h1>
            <hr />';
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
            
            $_SESSION['merchant_id'] = $this->merchant_id;
            $_SESSION['environment'] = $this->environment;
            wp_enqueue_style( 'ktpay-style1',$ktpay_file_url . 'assets/css/core.min.css', false, '1.0', 'all' );
            include dirname(__FILE__) . '/includes/kt-installment-options.php';
            
            if($this->rates!=null)
            {
                echo KTPayConfig::create_rates_update_form($this->rates,$this->installment_count);
            }
        }

        function receipt_page($orderid)
        {
            global $woocommerce;
            $error_message = false;
            $order = new WC_Order($orderid);
            $installment_mode = $this->installment_mode;
            $has_installment =  'off';

            if($this->rates!=null && $this->installment_count!=null && count($this->installment_count)>1) {
                $rates = KTPayConfig::calculate_price_with_installments($order->get_total(),$this->rates, $this->installment_count);
                $has_installment =  'on';
                $installment_count=$this->installment_count;
            }
            $status = $order->get_status();
            $showtotal = $order->get_total();
            $currency = $order->get_currency();
                       
            $ktpay=new KTPay();
            $check_onus_card_url = $ktpay->check_onus_card_test_url;
            
            if($this->environment == 'PROD') {
                $check_onus_card_url = $ktpay->check_onus_card_prod_url;
            }
            

            $orderIdPost= isset($_POST['order-id']) ? wc_clean($_POST['order-id']) : null;
            if ($orderIdPost!=null && $orderIdPost == $orderid) {
                $this->pay($orderid);
            }

            if ($status != 'pending') {
                $order->get_status();
            }
            $ktpay_file_url = plugins_url('/',__FILE__ );
            wp_enqueue_style( 'ktpay-style',$ktpay_file_url . 'assets/css/core.css', false, '1.0', 'all' );
            wp_enqueue_style( 'ktpay-style1',$ktpay_file_url . 'assets/css/core.min.css', false, '1.0', 'all' );
            wp_enqueue_style( 'ktpay-style2',$ktpay_file_url . 'assets/css/font-awesome.min.css', false, '1.0', 'all' );
            wp_enqueue_style( 'ktpay-style3',$ktpay_file_url . 'assets/css/keyboard.css', false, '1.0', 'all' );

            include dirname(__FILE__) .'/ktpayform.php';
        }
        
        function process_payment($order_id)
        {
            $order = new WC_Order($order_id);

            if (version_compare(WOOCOMMERCE_VERSION, '2.1.0', '>=')) {
                $checkout_payment_url = $order->get_checkout_payment_url(true);
            } else {
                $checkout_payment_url = get_permalink(get_option('woocommerce_pay_page_id'));
            }

            return array(
                'result' => 'success',
                'redirect' => $checkout_payment_url,
            );
        }

        function check_3d_response()
        {
            global $woocommerce;
            $ktpay=new KTPay();
            $postParams = $_POST;
            
            $order_id=isset($postParams['Result_MerchantOrderId']) ? wc_clean($postParams['Result_MerchantOrderId']) : "";
            $orderid=substr($order_id,3,strlen($order_id));
            $order = new WC_Order($orderid);

            $bodyParams=array(
                'environment' => $this->settings['environment'],
                'md'=>(isset($postParams['Result_MD'])) ? $postParams['Result_MD'] : "",
                'merchant_id'=>$this->settings['merchant_id'],
                'customer_id' =>$this->settings['customer_id'],
                'amount' =>$order->get_total(),
                'order_id' =>isset( $postParams['Result_OrderId']) ? $postParams['Result_OrderId'] : "",
                'merchant_order_id' =>isset( $postParams['Result_MerchantOrderId']) ? $postParams['Result_MerchantOrderId'] : "",
                'api_user_name' =>$this->settings['api_username'],
                'api_user_password' =>$this->settings['api_password'],
                'response_message' => isset( $postParams['ResponseMessage']) ? $postParams['ResponseMessage'] : "",
            );

            $action = sanitize_text_field(isset($_GET['action']) ? $_GET['action'] : "fail");
            $response=$ktpay->callback($action, $bodyParams);

            try {
                if($response['status']=="success")
                {  
                    $orderMessage = 'Payment ID: ' . $postParams['Result_MerchantOrderId'];
                    $order->add_order_note($orderMessage, 0, true);
                    $order->payment_complete();
                    WC()->cart->empty_cart();
                    $woocommerce->cart->empty_cart();
                    $checkoutOrderUrl = $order->get_checkout_order_received_url();
                    return wp_redirect($checkoutOrderUrl);
                }
                else
                {
                    $message = (string)$response['message'];
                    $order->add_order_note($message, 0, true);
                    wc_add_notice(__($message, 'kt-payment'), 'error');
                    $redirectUrl = $woocommerce->cart->get_cart_url();
                    return wp_redirect($redirectUrl);
                }
            } catch (\Throwable $th) {
                $message = (string)$th->getMessage();
                $message = !empty($message) ? $message : 'İşlem sırasında beklenmedik bir hata oluştu';
                $order->update_status('failed', sprintf(__('Ödeme Gerçekleşemedi', 'kt-payment'),$message));
                $order->add_order_note($message, 0, true);             
                wc_add_notice(__($message, 'kt-payment'), 'error');
                $redirectUrl = $woocommerce->cart->get_cart_url();
                return wp_redirect($redirectUrl);
            }

            die();
        }

        function pay($order_id)
        {
            $order = new WC_Order($order_id);
            global $woocommerce;
            if (version_compare(get_bloginfo('version'), '4.5', '>=')) {
                wp_get_current_user();
            } else {
                get_currentuserinfo();
            }

            $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
            $user_meta = get_user_meta(get_current_user_id());
            $card_holder_name = wc_clean($_POST['card-holder']);
            $card_number = $this->replaceSpace(wc_clean($_POST['card-number']));

            $card_expire_date=explode("/",wc_clean($_POST['card-expire-date']));
            $card_expire_month = $card_expire_date[0];
            $card_expire_year = $card_expire_date[1];
            $card_cvv = wc_clean($_POST['card-cvv']);
            $installment = isset($_POST['installment']) ? wc_clean($_POST['installment']) : 1;
            $orderId = 'WC-'.$order_id;

            $total = KTPayConfig::calculate_total_price($order->get_total(), $this->rates,  $installment);
            $order->set_total($total);
            $currency = KTPayConfig::get_currency_code($order->get_currency());
            $is3dTransaction=($this->td_mode == 'on') || ($this->td_overamount!=null && $this->td_mode =='off' && $total>$this->td_overamount);

            $ktpay = new KTPay();
            $params = array(               
                'merchant_id' => $this->merchant_id,
                'customer_id' => $this->customer_id,
                'api_user_name' => $this->api_username,
                'api_user_password' => $this->api_password,
                'success_url' => $is3dTransaction ? add_query_arg('wc-api', 'WC_KTPay_Gateway', $order->get_checkout_order_received_url()) . "&action=success" : '',
                'fail_url' => $is3dTransaction ? add_query_arg('wc-api', 'WC_KTPay_Gateway', $order->get_checkout_order_received_url()) . "&action=fail" : '',
                'merchant_order_id' =>$orderId,
                'amount' => $total,
                'installment_count' => $installment,
                'currency_code' => $currency,
                'customer_ip' => $ip,
                'customer_mail' => $order->get_billing_email(),
                'phone_number' => $order->get_billing_phone(),
                'card_holder_name' => $card_holder_name,
                'card_number' => $card_number,
                'card_expire_month' => $card_expire_month,
                'card_expire_year' => $card_expire_year,
                'card_cvv' => $card_cvv
            );
            $ktpay->set_payment_params($params);
            
            if($is3dTransaction){
                //3d akışı
                $parameters = array(
                    'data' => $ktpay->init_3d_request_body(),
                    'url' => $this->environment == 'TEST' ? $ktpay->td_payment_test_url : $ktpay->td_payment_prod_url,
                    'isJson' => $is3dTransaction,
                    'returnTypeJsn' => false,
                );
                $response=$ktpay->send_request($parameters);
                try {
                    if($response['success'])
                    {
                        if(str_starts_with($response['data'],'{'))
                        {                           
                            $jsonResponse=json_decode($response['data']);
                            echo var_dump($jsonResponse);
                            if(isset($jsonResponse->ResponseCode) && $jsonResponse->Success==false)
                            {
                                $message = (string)$jsonResponse->ResponseMessage;
                                $order->add_order_note($message, 0, true);
                                wc_add_notice(__($message, 'kt-payment'), 'error');
                                $redirectUrl = $woocommerce->cart->get_cart_url();
                                return wp_redirect($redirectUrl);
                            }
                        }
                        else
                        {
                            echo $response['data'];
                        }
                    }
                    else
                    {
                        $message = (string)$response['message'];
                        $order->add_order_note($message, 0, true);
                        wc_add_notice(__($message, 'kt-payment'), 'error');
                        $redirectUrl = $woocommerce->cart->get_cart_url();
                        return wp_redirect($redirectUrl);
                    }
                } catch (\Throwable $th) {
                    $message = (string)$th->getMessage();
                    $order->add_order_note($message, 0, true);
                    wc_add_notice(__($message, 'kt-payment'), 'error');
                    $redirectUrl = $woocommerce->cart->get_cart_url();
                    return wp_redirect($redirectUrl);
                }               
            }
            else
            {
                //non3d akışı     
                $parameters = array(
                    'data' => $ktpay->init_non3d_request_body(),
                    'url' => $this->environment == 'TEST' ? $ktpay->ntd_payment_test_url : $ktpay->ntd_payment_prod_url,
                    'isJson' => $is3dTransaction,
                    'returnTypeJsn' => $is3dTransaction,
                );
                $response = $ktpay->send_request($parameters);
                if($response['success'])
                {
                    $resultXML = simplexml_load_string($response['data']);
                    $VPosTransactionResponseContract=new SimpleXMLElement($resultXML->asXML());
    
                    if($VPosTransactionResponseContract->ResponseCode=="00")
                    {
                        if ($installment > 1) {
                            $installment_fee = $total;
                            $order_fee = new stdClass();
                            $order_fee->id = 'Total';
                            $order_fee->name = __('Total', 'kt-payment');
                            $order_fee->amount = $installment_fee;
                            $order_fee->taxable = false;
                            $order_fee->tax = 0;
                            $order_fee->tax_data = array();
                            $order_fee->tax_class = '';
                            $fee_id = $order->add_fee($order_fee);
                            $order->calculate_totals(true);
                            update_post_meta($orderId, 'kt_installment_number', esc_sql($installment));
                            update_post_meta($orderId, 'kt_installment_fee', $installment_fee);
                        }
                        
                        $orderMessage = 'Payment ID: ' . $orderId;
                        $order->add_order_note($orderMessage, 0, true);
                        $order->payment_complete();
                        WC()->cart->empty_cart();
                        $woocommerce->cart->empty_cart();
                        $checkoutOrderUrl = $order->get_checkout_order_received_url();
                        return wp_redirect($checkoutOrderUrl);
                    }
                    else
                    {
                        $message = (string)$VPosTransactionResponseContract->ResponseMessage;
                        $order->add_order_note($message, 0, true);
                        wc_add_notice(__($message, 'kt-payment'), 'error');
                        $redirectUrl = $woocommerce->cart->get_cart_url();
                        return wp_redirect($redirectUrl);
                    }
                }
                else
                {
                    $message = (string)$response['message'];
                    $order->add_order_note($message, 0, true);
                    wc_add_notice(__($message, 'kt-payment'), 'error');
                    $redirectUrl = $woocommerce->cart->get_cart_url();
                    return wp_redirect($redirectUrl);
                }
            }

        }

        private function replaceSpace($veri)
        {
            $veri = str_replace("/s+/", "", $veri);
            $veri = str_replace(" ", "", $veri);
            $veri = str_replace(" ", "", $veri);
            $veri = str_replace("/s/g", "", $veri);
            $veri = str_replace("/s+/g", "", $veri);
            $veri = trim($veri);
            return $veri;
        }
        #
    }
}

add_filter('woocommerce_payment_gateways', 'woocommerce_kt_checkout_form');

function woocommerce_kt_checkout_form($methods)
{
    $methods[]="WC_KTPay_Gateway";
    return $methods;
}
