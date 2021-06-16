<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) return;

if ($errorException = $APPLICATION->GetException()) {
    CAdminMessage::ShowMessage([
        'MESSAGE' => Loc::getMessage('SB_UNINSTALL_ERROR_MESSAGE'),
        'DETAILS'=> $errorException->GetString(),
        'HTML' => true,
    ]);
} else {
    CAdminMessage::ShowNote(Loc::getMessage('SB_UNINSTALL_STEP2_NOTE', ['#MODULE_NAME#' => Loc::getMessage('SB_MODULE_NAME')]));
}

$tabs = [
    [
        'DIV' => 'step',
        'TAB' => Loc::getMessage('SB_UNINSTALL_STEP2_TAB'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SB_UNINSTALL_STEP2_TITLE')
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
            <td><?= Loc::getMessage('SB_UNINSTALL_TEXT_OK_STEP2') ?></td>
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