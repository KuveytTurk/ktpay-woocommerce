<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<?php if ($error_message) { ?>
    <div class="row">
        <ul class="woocommerce-error" id="errDiv">
            <li>
                <?php echo __('Payment Error.', 'kt-payment-module') ?>
                <b><?php echo esc_html($error_message); ?></b><br />
                <?php echo __('Please check the form and try again.', 'kt-payment-module') ?>
            </li>
        </ul>
    </div>
<?php } ?>

<div class="flex flex-col h-screen">
    <div class="flex flex-col px-5 max-w-full w-[600px]">
        <header class="flex flex-wrap pb-12 w-full z-50  sm:py-10">
            <nav class="max-w-7xl flex basis-full items-center w-full">
                <div>
                    <img src="<?php echo plugins_url('/kt-payment-module/assets/img/kuveytturk.svg', dirname(__FILE__)) ?>"
                        alt="" srcset="">
                </div>
                <div class="hidden sm:block w-full mx-auto justify-end">
                    <div class="grow my-auto font-medium leading-4 text-right text-teal-600">
                        <?php echo __('Ödeme Sayfası', 'kt-payment-module') ?>
                    </div>
                </div>
            </nav>
        </header>

        <div class="flex flex-col max-md:px-5">
            <form method="POST" id="ktform" action="">
                <input value="<?php echo esc_html($orderid) ?>" name="order-id" type="hidden">
                <div id="alert" class="alert" style="display: none;">
                    <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
                    <label id="alertText">Hata!</label>
                </div>
                <div class="mb-3">
                    <label for="card-holder" class="block text-sm text-ellipsis text-zinc-600 mb-2">
                        <?php echo __('Kart Sahibi Ad Soyad', 'garanti-payment-module') ?>
                    </label>
                    <input type="text" id="card-holder" name="card-holder" style="color: black;"
                        onkeypress="restrictAllWithoutAlphabets(event)"
                        onpaste="return restrictPasteAllWithoutAlphabets(event)" onfocus="keyboardClose()"
                        class="flex w-full p-3 mt-1.5 text-neutral-400 bg-white rounded-md border border-solid border-[color:var(--Box-stroke,#D6D6D6)] shadow-lg"
                        placeholder="<?php echo __('Ad Soyad', 'kt-payment-module'); ?>" maxlength="45">
                </div>

                <div class="mb-3">
                    <label for="card-number" class="block text-sm text-ellipsis text-zinc-600 mb-2">
                        <?php echo __('Kart Numarası', 'kt-payment-module'); ?>
                    </label>
                    <div class="flex gap-3 justify-center self-center justify-between mb-3" style="position:relative">
                        <input type="text" id="card-number" name="card-number" style="color: black;"
                            onpaste="return restrictPasteAllWithoutNumsWithSpace(event)"
                            onkeypress="restrictAllWithoutNums(event)" onkeyup="cardNumberControls(event,null)"
                            class="flex w-full p-3 mt-1.5 text-neutral-400 bg-white rounded-md border border-solid border-[color:var(--Box-stroke,#D6D6D6)] shadow-lg"
                            placeholder="1234 1234 1234 1234" minlength="19">
                        <img id="keyboard"
                            src="<?php echo plugins_url('/kt-payment-module/assets/img/keyboard.png', dirname(__FILE__)) ?>"
                            loading="lazy" class="max-w-16 mt-4" style="position: absolute; top: 2px; right: 10px"
                            onclick="keyboardClick()" alt="" />
                        <img id="visaScheme"
                            src="<?php echo plugins_url('/kt-payment-module/assets/img/visa.svg', dirname(__FILE__)) ?>"
                            loading="lazy"
                            style="display: none; width:60px; margin-top:20px; margin-right: 40px; position: absolute; top: 2px; right: 10px"
                            class="max-w-16 mt-4" alt="" />
                        <img id="troyScheme"
                            src="<?php echo plugins_url('/kt-payment-module/assets/img/troy.png', dirname(__FILE__)) ?>"
                            loading="lazy"
                            style="display: none; width: 60px; margin-right: 40px; position: absolute; top: 2px; right: 10px"
                            class="max-w-16 mt-4" alt="" />
                        <img id="masterCardScheme"
                            src="<?php echo plugins_url('/kt-payment-module/assets/img/mastercard.svg', dirname(path: __FILE__)) ?>"
                            loading="lazy"
                            style="display: none; width: 50px; margin-right: 40px; position: absolute; top: 2px; right: 10px"
                            class="max-w-12 mt-4" alt="" />
                    </div>
                    <div id="virtualKeyboard" class="virtual-keyboard" style="display: none;"></div>
                </div>

                <div class="flex gap-4 justify-between mb-3">
                    <div class="flex flex-col flex-1">
                        <label for="card-expire" class="block text-sm text-ellipsis text-zinc-600 mb-2">
                            <?php echo __('Kart Son Kullanma Tarihi', 'kt-payment-module'); ?>
                        </label>
                        <div class="flex gap-4 justify-between mb-3">
                        <div class="flex gap-4 justify-between mb-3">
                            <input type="text" id="card-expire-date" name="card-expire-date" style="color: black;" onkeyup="checkCardExpireDate(event)" onkeypress="restrictAllWithoutNums(event)" onpaste="event.preventDefault();"
                            class="flex w-full p-3 mt-1.5 text-neutral-400 bg-white rounded-md border border-solid border-[color:var(--Box-stroke,#D6D6D6)] shadow-lg"
                            placeholder="AA/YY" maxlength="5" onfocus="keyboardClose()">
                        </div>  
                        </div>
                    </div>
                    <div class="flex flex-col flex-1">
                        <label for="card-cvv" class="block text-sm text-ellipsis text-zinc-600 mb-2">CVC/CVV:</label>
                        <input type="text" id="card-cvv" name="card-cvv" style="color: black;"
                            onkeypress="restrictAllWithoutNums(event)"
                            onpaste="return restrictPasteAllWithoutNums(event)" onfocus="keyboardClose()"
                            class="flex w-full p-3 mt-1.5 text-neutral-400 bg-white rounded-md border border-solid border-[color:var(--Box-stroke,#D6D6D6)] shadow-lg"
                            placeholder="123" maxlength="3">
                    </div>
                </div>
                <div id="installmentDiv" class="flex gap-4 justify-between mb-3" style="display:none;">
                    <div class="flex flex-col flex-1">
                        <label for="installment-number"
                            class="block text-sm text-ellipsis text-zinc-600"><?php echo __('Taksit Adedi', 'kt-payment-module') ?></label>
                        <div class="flex gap-4 p-3 mt-1.5 flex-row flex-1 rounded-md border border-solid border-[color:var(--Box-stroke,#D6D6D6)]"
                            style="flex-wrap:wrap">
                            <table>
                                <?php
                                if (isset($installment_count)) {
                                    for ($i = 1; $i <= count($installment_count); $i++) { ?>
                                        <?php if ($rates[$i]["active"] == 1) { ?>
                                            <tr>
                                                <th>
                                                    <div style="width:200px;">
                                                        <input type="radio" id="<?php echo esc_html($i); ?>" name="installment"
                                                            value="<?php echo esc_html($i); ?>" onclick="clickInstallment()">
                                                        <label
                                                            id="installmentLabel_<?php echo esc_html($i) ?>"><?php echo esc_html($i) ?>
                                                            <?php echo __('Taksit', 'kt-payment-module'); ?></label>
                                                    </div>
                                                </th>
                                                <th>
                                                    <div>
                                                        <label><?php echo esc_html($rates[$i]['total']); ?> /
                                                            <?php echo esc_html($rates[$i]['monthly']); ?></label>
                                                    </div>
                                                </th>
                                            </tr>
                                        <?php } ?>
                                    <?php }
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col py-4 pl-12 mt-4 max-md:pl-5">
                    <div class="flex gap-4 justify-between px-2">
                        <button id="payButton" type="button" onclick="pay()"
                            class="grow px-16 py-3 font-medium text-center text-white bg-teal-600 rounded-xl shadow-lg max-w-[400px]">
                            <i id="spinner" class="fa fa-spinner fa-spin" style="display:none;"></i>
                            <i id="buttonText"><?php echo __('Öde', 'kt-payment-module') ?></i>
                        </button>
                    </div>
                </div>
            </form>

        </div>



    </div>
</div>
<script src="<?php echo plugins_url('/kt-payment-module/assets/js/core.js', dirname(__FILE__)) ?>" defer></script>
<script src="<?php echo plugins_url('/kt-payment-module/assets/js/keyboard-handler.js', dirname(__FILE__)) ?>"
    defer></script>
<script src="<?php echo plugins_url('/kt-payment-module/assets/js/keyboard-initializer.js', dirname(__FILE__)) ?>"
    defer></script>

<script type="text/javascript">
    var installmentMode = "<?php echo esc_html($installment_mode) ?>";
    var hasRightForInstallment = "<?php echo esc_html($has_installment) ?>";
    var checkOnusCardUrl = "<?php echo esc_html($check_onus_card_url) ?>";
    var isCheckOnUsCard = false;
</script>