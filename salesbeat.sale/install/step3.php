<?php

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDatabase $DB
 * @global string $mid
 **/

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if ($errorException = $APPLICATION->GetException()) { // Отображение ошибок
    CAdminMessage::ShowMessage([
        'MESSAGE' => Loc::getMessage('SB_INSTALL_ERROR_MESSAGE'),
        'DETAILS'=> $errorException->GetString(),
        'HTML' => true,
    ]);
} else { // Уведомление
    CAdminMessage::ShowNote(Loc::getMessage('SB_INSTALL_STEP3_NOTE', ['#MODULE_NAME#' => Loc::getMessage('SB_MODULE_NAME')]));
}

$tabs = [
    [
        'DIV' => 'step',
        'TAB' => Loc::getMessage('SB_INSTALL_STEP3_TAB'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SB_INSTALL_STEP3_TITLE')
    ]
];

$tabControl = new CAdminForm('tabControl', $tabs, true, true);
$tabControl->SetShowSettings(false);
unset($tabs);
?>
<form action="<?= $APPLICATION->GetCurPage(); ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">

    <?php
    $tabControl->Begin();

    // Tab с настройками модуля
    $tabControl->BeginNextFormTab();

    if (!$errorException = $APPLICATION->GetException()) {
        $tabControl->BeginCustomField('error', '', true);
        ?>
        <tr>
            <td><?= Loc::getMessage('SB_INSTALL_TEXT_OK_STEP3') ?></td>
        </tr>
        <?php
        $tabControl->EndCustomField('error', '');

        // Кнопки
        $MESS['admin_lib_edit_save'] = Loc::getMessage('SB_SUBMIT_BACK');
        $tabControl->Buttons([
            'btnApply' => false, // не показывать кнопку применить
            'btnCancel' => false, // не показывать кнопку отменить
        ]);
    } else {
        $tabControl->BeginCustomField('error', '', true);
        ?>
        <tr>
            <td><?= Loc::getMessage('SB_INSTALL_TEXT_ERROR_STEP3') ?></td>
        </tr>
        <?php
        $tabControl->EndCustomField('error', '');

        // Кнопки
        $MESS['admin_lib_edit_cancel'] = Loc::getMessage('SB_SUBMIT_BACK');
        $tabControl->Buttons([
            'back_url' => $APPLICATION->GetCurPage(),
            'btnSave' => false, // не показывать кнопку сохранить
            'btnApply' => false, // не показывать кнопку применить
        ]);
    }

    $tabControl->Show();
    $tabControl->ShowWarnings('post_form', null);
    ?>
</form>