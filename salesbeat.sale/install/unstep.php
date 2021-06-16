<?php

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDatabase $DB
 * @global string $mid
 **/

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

CAdminMessage::ShowMessage([
    'MESSAGE' => Loc::getMessage('SB_UNINSTALL_STEP1_MESSAGE'),
    'DETAILS'=> Loc::getMessage('SB_UNINSTALL_STEP1_DETAILS', ['#MODULE_NAME#' => Loc::getMessage('SB_MODULE_NAME')]),
    'HTML' => true,
]);

echo BeginNote(), Loc::getMessage('SB_UNINSTALL_STEP1_NOTE'), EndNote();

$moduleId = 'salesbeat.sale';

$tabs = [
    [
        'DIV' => 'step',
        'TAB' => Loc::getMessage('SB_UNINSTALL_STEP1_TAB'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SB_UNINSTALL_STEP1_TITLE')
    ]
];

$tabControl = new CAdminForm('tabControl', $tabs, true, true);
$tabControl->SetShowSettings(false);
unset($tabs);
?>
<form action="<?= $APPLICATION->GetCurPage(); ?>">
    <?= bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="id" value="<?= $moduleId ?>">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">

    <?php
    $tabControl->Begin();

    // Tab с настройками модуля
    $tabControl->BeginNextFormTab();

    $tabControl->BeginCustomField('uninstall', '', true);
    ?>
    <tr>
        <td>
            <input type="checkbox" name="delete_table" id="delete-table" value="Y">
            <label for="delete-table"><?= Loc::getMessage('SB_UNINSTALL_STEP1_DELETE_TABLE'); ?></label>
        </td>
    </tr>
    <tr>
        <td>
            <input type="checkbox" name="delete_settings" id="delete-settings" value="Y">
            <label for="delete-settings"><?= Loc::getMessage('SB_UNINSTALL_STEP1_DELETE_CONFIGS'); ?></label>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('uninstall', '');

    // Кнопки
    $MESS['admin_lib_edit_save'] = Loc::getMessage('SB_UNISTALL_SUBMIT_STEP1_SAVE', ['#MODULE_NAME#' => Loc::getMessage('SB_MODULE_NAME')]);
    $tabControl->Buttons([
        'back_url' => $APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID . '&mid=' . $mid,
        'btnApply' => false, // не показывать кнопку применить
    ]);

    $tabControl->Show();
    $tabControl->ShowWarnings('post_form', null);
    ?>
</form>