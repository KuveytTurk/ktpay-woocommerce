<?php

if (!defined('ABSPATH')) {
    exit;
}

class KTPayConfig{

    public static function set_rates_from_post($postedData, $installment_array)
    {
        $installments = array();
        for ($i = 1; $i <= count($installment_array); $i++) {
            $installments[$i]['value'] = isset($postedData[$i]['value']) ? ((float) $postedData[$i]['value']) : 0.0;
            $installments[$i]['active'] = isset($postedData[$i]['active']) ? ((int) $postedData[$i]['active']) : 0;
        }

        return $installments;
    }

    public static function init_rates($installment_array)
    {
        $installments = array();
        for ($i = 1; $i <= count($installment_array); $i++) {
            $installments[$i]['value'] = 0;//(float) (1 + $i + ($i / 5) + 0.1);
            $installments[$i]['active'] = 0;
            if ($i == 1) {
                $installments[$i]['value'] = 0.00;
            }
        }

        return $installments;
    }

    public static function register_all_installments($installment_array)
    {
        if (isset($_POST['kt_pay_rates'])) {
            update_option('kt_pay_rates', self::set_rates_from_post($_POST['kt_pay_rates'],$installment_array));
            update_option('kt_pay_installments', $installment_array);
        }
    }

    public static function update_all_installments($installment_array)
    {
        update_option('kt_pay_rates', self::init_rates($installment_array));
        update_option('kt_pay_installments', $installment_array);
    }

    public static function create_rates_update_form($rates, $installment_array)
    {
        $installment_form = '<table style="text-align: center;">'
            . '<thead><tr>';
        foreach ($installment_array as $i) {
            $installment_form .= '<th>' . $i . ' Taksit</th>';
        }
        $installment_form .= '</tr></thead><tbody><tr>';
        for ($i = 1; $i <= count($installment_array); $i++) {
            $installment_form .= '<td>'
                . ' <input type="checkbox"  name="kt_pay_rates[' . $i . '][active]" '
                . ' value="1" ' . ((int) $rates[$i]['active'] == 1 ? 'checked="checked"' : '') . '/></td>';
        }

        $installment_form.='</tr><tr>';

        for ($i = 1; $i <= count($installment_array); $i++) {
            $installment_form .= '<td>'               
                . '%<input type="number" step="0.01" maxlength="4" size="4" style="width:60px" '
                . ($i == 1 ? ' disabled' : '')
                . ' value="' . ((float) $rates[$i]['value']) . '"'
                . ' name="kt_pay_rates[' . $i . '][value]"/></td>';
        }

        $installment_form .= '</tr></tbody></table>';
        return $installment_form;
    }

    public static function calculate_price_with_installments($price, $rates, $installment_array)
    {
        $installment = array();
        for ($i = 1; $i <= count($installment_array); $i++) {
            $installment[$i] = array(
                'active' => isset($rates[$i]['active']) ? $rates[$i]['active'] : 0,
                'total' => number_format((((100 + (isset($rates[$i]['value']) ? $rates[$i]['value'] : 0)) * $price) / 100), 2, '.', ''),
                'monthly' => number_format((((100 + (isset($rates[$i]['value']) ? $rates[$i]['value'] : 0)) * $price) / 100) / $i, 2, '.', ''),
            );
        }
        return $installment;
    }

    public static function calculate_total_price($price, $rates, $installment)
    {
        return number_format((((100 + (isset($rates[$installment]['value']) ? $rates[$installment]['value'] : 0)) * $price) / 100), 2, '.', '');
    }

    public static function get_currency_code($currency)
    {
        switch ($currency) {
            case 'TRY':
                $code = "0949";
                break;
            case 'USD':
                $code = "0840";
                break;
            case 'EUR':
                $code = "0978";
                break;
            default:
                $code = "0949";
                break;
        }
        return $code;
    }

    public static function get_hash_data($hashValue, $password)
    {
        $hashPassword = base64_encode(hash('sha1', mb_convert_encoding($password, "UTF-8",mb_detect_encoding($password)), true));
        $hashValue .= $hashPassword;
        $hashbytes=mb_convert_encoding($hashValue, "UTF-8",mb_detect_encoding($hashValue));
        $inputbytes = hash_hmac('sha512', $hashbytes, mb_convert_encoding($hashPassword, "UTF-8",mb_detect_encoding($hashPassword)), true);
        return base64_encode($inputbytes);
    }
}
