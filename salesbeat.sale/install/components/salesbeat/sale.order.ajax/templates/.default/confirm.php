<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @var string $templateFolder
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 */

$this->addExternalCss($templateFolder . '/stale.css');
$this->addExternalJS($templateFolder . '/script.js');

if ($arParams['SET_TITLE'] == 'Y')
    $APPLICATION->SetTitle(Loc::getMessage("SOA_ORDER_COMPLETE"));

if (!empty($arResult['ORDER'])):
    ?>
    <table class="sale_order_full_table">
        <tr>
            <td>
                <?= Loc::getMessage('SB_SOA_SUCCESS_ORDER', [
                    '#ORDER_DATE#' => $arResult['ORDER']['DATE_INSERT']->toUserTime()->format('d.m.Y H:i'),
                    '#ORDER_ID#' => $arResult['ORDER']['ACCOUNT_NUMBER']
                ]); ?>

                <?php
                if (!empty($arResult['ORDER']['PAYMENT_ID'])):
                    echo Loc::getMessage('SB_SOA_SUCCESS_PAYMENT', [
                        '#PAYMENT_ID#' => $arResult['PAYMENT'][$arResult['ORDER']['PAYMENT_ID']]['ACCOUNT_NUMBER']
                    ]);
                endif;
                ?>
            </td>
        </tr>
    </table>

    <?php
    if ($arResult['ORDER']['IS_ALLOW_PAY'] !== 'Y'):
        ?>
        <strong><?= $arParams['MESS_PAY_SYSTEM_PAYABLE_ERROR']; ?></strong>
        <?php
        return;
    endif;

    if (empty($arResult['PAYMENT'])) return;

    foreach ($arResult['PAYMENT'] as $payment):
        if ($payment['PAID'] === 'Y') continue;

        if (empty($arResult['PAY_SYSTEM_LIST']) || !array_key_exists($payment['PAY_SYSTEM_ID'], $arResult['PAY_SYSTEM_LIST'])):
            echo '<span style="color:red;">' . Loc::getMessage('SB_SOA_ORDER_PS_ERROR') . '</span>';
            continue;
        endif;

        $paySystem = $arResult['PAY_SYSTEM_LIST_BY_PAYMENT_ID'][$payment['ID']];
        if (!empty($paySystem['ERROR'])):
            echo '<span style="color:red;">' . Loc::getMessage('SB_SOA_ORDER_PS_ERROR') . '</span>';
            continue;
        endif;
        ?>

        <br>
        <table class="sale_order_full_table">
            <tr>
                <td class="ps_logo">
                    <div class="pay_name"><?= Loc::getMessage('SB_SOA_PAY') ?></div>
                    <?= CFile::ShowImage($paySystem['LOGOTIP'], 100, 100, "border=0\" style=\"width:100px\"", '', false) ?>
                    <div class="paysystem_name"><?= $paySystem['NAME'] ?></div>
                </td>
            </tr>
            <tr>
                <td>
                    <?php
                    if ($paySystem['ACTION_FILE'] <> '' && $paySystem['NEW_WINDOW'] === 'Y' && $paySystem['IS_CASH'] !== 'Y'):
                        $orderAccountNumber = urlencode(urlencode($arResult['ORDER']['ACCOUNT_NUMBER']));
                        $paymentAccountNumber = $payment['ACCOUNT_NUMBER'];
                        ?>
                        <script>
                            // window.open('<?= $arParams['PATH_TO_PAYMENT'] . '?ORDER_ID=' . $orderAccountNumber . '&PAYMENT_ID=' . $paymentAccountNumber ?>');
                        </script>
                        <br>
                        <?= Loc::getMessage('SB_SOA_PAY_LINK', [
                            "#LINK#" => $arParams['PATH_TO_PAYMENT'] . '?ORDER_ID=' . $orderAccountNumber . '&PAYMENT_ID=' . $paymentAccountNumber
                        ]); ?>

                        <?php if (CSalePdf::isPdfAvailable() && $paySystem['IS_AFFORD_PDF']): ?>
                            <br>
                            <?= Loc::getMessage('SB_SOA_PAY_PDF', ['#LINK#' => $arParams['PATH_TO_PAYMENT'] . '?ORDER_ID=' . $orderAccountNumber . '&pdf=1&DOWNLOAD=Y']); ?>
                        <?php
                        endif;
                    else:
                        ?>
                        <div data-pay-system>
                            <?= $paySystem['BUFFERED_OUTPUT'] ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <?php
    endforeach;
    ?>
<? else: ?>
    <table class="sale_order_full_table">
        <tr>
            <td>
                <?= Loc::getMessage('SB_SOA_ERROR_ORDER_TITLE'); ?>
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage('SB_SOA_ERROR_ORDER_DESC'); ?></td>
        </tr>
    </table>
<? endif ?>
<script>
    if (typeof window.frameCacheVars !== 'undefined') {
        BX.addCustomEvent('onFrameDataReceived', BX.Salesbeat.SaleOrderAjax.init({
            'type': 'confirm'
        }));
    } else {
        BX.ready(BX.Salesbeat.SaleOrderAjax.init({
            'type': 'confirm'
        }));
    }
</script>