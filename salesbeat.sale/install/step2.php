<?php

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDatabase $DB
 * @global string $mid
 **/

include __DIR__ . '/../lib/system.php';
include __DIR__ . '/../lib/internals.php';

use \Bitrix\Main\Localization\Loc;
use \Salesbeat\Sale\Internals;

Loc::loadMessages(__FILE__);

if ($errorException = $APPLICATION->GetException()) { // Отображение ошибок
    CAdminMessage::ShowMessage([
        'MESSAGE' => Loc::getMessage('SB_INSTALL_ERROR_MESSAGE'),
        'DETAILS'=> $errorException->GetString(),
        'HTML' => true,
    ]);
} else { // Уведомление
    echo BeginNote(), Loc::getMessage('SB_INSTALL_STEP2_NOTE'), EndNote();
}

$moduleId = 'salesbeat.sale';

$tabs = [
    [
        'DIV' => 'step',
        'TAB' => Loc::getMessage('SB_INSTALL_STEP2_TAB'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SB_INSTALL_STEP2_TITLE')
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
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="3">

    <?php
    $tabControl->Begin();

    // Tab с настройками модуля
    $tabControl->BeginNextFormTab();

    if (empty($APPLICATION->GetException())) {
        $personTypeList = Internals::getPersonTypeList();
        foreach ($personTypeList as $personType) {
            $propertyList = Internals::getPropertyList([
                'order' => ['ID' => 'ASC'],
                'filter' => [
                    'PERSON_TYPE_ID' => $personType['ID'],
                    'ACTIVE' => 'Y',
                    '!CODE' => array_column(Internals::getSbPropertyList(), 'CODE')
                ]
            ]);

            $tabControl->BeginCustomField('person_type_' . $personType['ID'], $personType['NAME'], true);
            ?>
            <tr>
                <td width="10%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
                <td>
                    <select name="props[]" multiple size="5">
                        <option value=""><?= Loc::getMessage('SB_SELECT_PROPS') ?></option>
                        <?php
                        foreach ($propertyList as $property)
                            echo '<option value="' . $property['ID'] . '">[' . $property['CODE'] . '] ' . $property['NAME'] . '</option>';
                        unset($propertyList, $property);
                        ?>
                    </select>
                </td>
            </tr>
            <?php
            $tabControl->EndCustomField('person_type_' . $personType['ID'], '');
        }

        // Кнопки
        $MESS['admin_lib_edit_save'] = Loc::getMessage('SB_INSTALL_SUBMIT_STEP2_SAVE');
        $MESS['admin_lib_edit_cancel'] = Loc::getMessage('SB_INSTALL_SUBMIT_STEP2_CANCEL');
        $tabControl->Buttons([
            'back_url' => $APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID . '&mid=' . $mid,
            'btnApply' => false, // не показывать кнопку сохранить
        ]);
    } else {
        $tabControl->BeginCustomField('error', '', true);
        ?>
        <tr>
            <td><?= Loc::getMessage('SB_INSTALL_TEXT_ERROR_STEP2') ?></td>
        </tr>
        <?php
        $tabControl->EndCustomField('error', '');

        // Кнопки
        $MESS['admin_lib_edit_cancel'] = Loc::getMessage('SB_INSTALL_SUBMIT_STEP2_CANCEL_ERROR');
        $tabControl->Buttons([
            'back_url' => $_SERVER['HTTP_REFERER'],
            'btnSave' => false, // не показывать кнопку сохранить
            'btnApply' => false, // не показывать кнопку применить
        ]);
    }

    $tabControl->Show();
    $tabControl->ShowWarnings('post_form', null);
    ?>
</form>