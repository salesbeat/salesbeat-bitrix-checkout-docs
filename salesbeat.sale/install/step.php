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

if ($errorException = $APPLICATION->GetException()) { // Отображение ошибок
    CAdminMessage::ShowMessage([
        'MESSAGE' => Loc::getMessage('SB_INSTALL_ERROR_MESSAGE'),
        'DETAILS'=> $errorException->GetString(),
        'HTML' => true,
    ]);
} else { // Уведомление
    echo BeginNote(), Loc::getMessage('SB_INSTALL_STEP1_NOTE'), EndNote();
}

$moduleId = 'salesbeat.sale';

$tabs = [
    [
        'DIV' => 'step',
        'TAB' => Loc::getMessage('SB_INSTALL_STEP1_TAB'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SB_INSTALL_STEP1_TITLE')
    ]
];

$tabControl = new CAdminForm('tabControl', $tabs, true, true);
$tabControl->SetShowSettings(false);
unset($tabs);

$arFields = [
    'api_token' => Option::get($moduleId, 'api_token'),
    'secret_token' => Option::get($moduleId, 'secret_token')
];
?>
<form action="<?= $APPLICATION->GetCurPage(); ?>">
    <?= bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="id" value="<?= $moduleId ?>">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">

    <?php
    $tabControl->Begin();

    // Tab с настройками модуля
    $tabControl->BeginNextFormTab();

    $tabControl->BeginCustomField('api_token', Loc::getMessage('SB_INSTALL_STEP1_API_TOKEN'), true);
    ?>
    <tr>
        <td><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td>
            <input type="text" name="api_token" value="<?= $arFields['api_token'] ?>" size="80">
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('api_token', '');
    $tabControl->BeginCustomField('secret_token', Loc::getMessage('SB_INSTALL_STEP1_SECRET_TOKEN'), true);
    ?>
    <tr>
        <td><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td>
            <input type="text" name="secret_token" value="<?= $arFields['secret_token'] ?>" size="80">
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('secret_token', '');

    // Кнопки
    $MESS['admin_lib_edit_save'] = Loc::getMessage('SB_INSTALL_SUBMIT_STEP1_SAVE');
    $MESS['admin_lib_edit_cancel'] = Loc::getMessage('SB_INSTALL_SUBMIT_STEP1_CANCEL');

    $tabControl->Buttons([
        'back_url' => $APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID . '&mid=' . $mid,
        'btnApply' => false, // не показывать кнопку применить
    ]);
    $tabControl->Show();
    $tabControl->ShowWarnings('post_form', null);
    ?>
</form>