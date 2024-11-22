<?php
if (!defined('ABSPATH')) {
    exit;
}

?>

<hr/>
    <h1>
      
        <input id="checkInstallmentDefinition" type="submit" value="<?php echo __('Taksit Tanımını Kontrol Et', 'kt-payment-module'); ?>" name="checkInstallmentDefinition" class="grow px-16 font-small text-center text-white bg-teal-600 rounded-xl shadow-lg ">
        
        </input>
    </h1>
<?php

if(isset($_POST["checkInstallmentDefinition"])){
    check_installment_definition();
}

function check_installment_definition(){

    $environment=$_SESSION['environment'];
    $merchant_id=$_SESSION['merchant_id'];

    if($merchant_id==null || strlen(trim($merchant_id)) === 0){
        echo "<script type='text/javascript'>alert('Üye işyeri numarası girilmelidir');</script>";
        return;
    }

    $kt_pay=new KTPay();
    $kt_pay->check_installment_definition($environment, $merchant_id);

}

?>