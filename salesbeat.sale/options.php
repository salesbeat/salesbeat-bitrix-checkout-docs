<?php

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDatabase $DB
 * @global string $mid
 **/

use \Bitrix\Main\Application;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\IO;
use \Bitrix\Iblock;
use \Bitrix\Sale\PaySystem;
use \Salesbeat\Sale\System;
use \Salesbeat\Sale\Internals;
use \Salesbeat\Sale\Api;

Loc::loadMessages(__FILE__);

Loader::includeModule('sale');
Loader::includeModule('salesbeat.sale');

$moduleId = System::getModuleId();

$request = Application::getInstance()->getContext()->getRequest();

if ($request->getRequestMethod() == 'POST' && check_bitrix_sessid()) {
    $apiToken = $request->getPost('api_token');
    $secretToken = $request->getPost('secret_token');

    $result = Api::postCheckTokens($apiToken, $secretToken);
    if (isset($result['data']['valid']) && $result['data']['valid']) {
        Option::set($moduleId, 'api_token', $apiToken);
        Option::set($moduleId, 'secret_token', $secretToken);
    } else {
        CAdminMessage::ShowMessage([
            'MESSAGE' => Loc::getMessage('SB_OPTION_ERROR_TOKENS_MESSAGE'),
            'DETAILS'=> Loc::getMessage('SB_OPTION_ERROR_TOKENS_DETAILS'),
            'HTML' => true,
        ]);
    }

    Option::set($moduleId, 'default_width', $request->getPost('default_width') ?: 0);
    Option::set($moduleId, 'default_height', $request->getPost('default_height') ?: 0);
    Option::set($moduleId, 'default_length', $request->getPost('default_length') ?: 0);
    Option::set($moduleId, 'default_weight', $request->getPost('default_weight') ?: 0);

    Option::set($moduleId, 'pay_systems_cash', serialize($request->getPost('pay_systems_cash')));
    Option::set($moduleId, 'pay_systems_card', serialize($request->getPost('pay_systems_card')));
    Option::set($moduleId, 'pay_systems_online', serialize($request->getPost('pay_systems_online')));

    Option::set($moduleId, 'recipient_extend', $request->getPost('recipient_extend') ?: 'N');
    Option::set($moduleId, 'recipient_full_name', $request->getPost('recipient_full_name'));
    Option::set($moduleId, 'recipient_last_name', $request->getPost('recipient_last_name'));
    Option::set($moduleId, 'recipient_first_name', $request->getPost('recipient_first_name'));
    Option::set($moduleId, 'recipient_middle_name', $request->getPost('recipient_middle_name'));
    Option::set($moduleId, 'recipient_phone', $request->getPost('recipient_phone'));
    Option::set($moduleId, 'recipient_email', $request->getPost('recipient_email'));

    Option::set($moduleId, 'order_ab_test', $request->getPost('order_ab_test') ? (int)$request->getPost('order_ab_test') : 100);
    Option::set($moduleId, 'order_catalog', $request->getPost('order_catalog'));
    Option::set($moduleId, 'order_offers', $request->getPost('order_offers'));
    Option::set($moduleId, 'order_properties', serialize($request->getPost('order_properties')));

    Option::set($moduleId, 'delivery_template', $request->getPost('delivery_template'));
}
unset($request);

$arTabs = [
    [
        'DIV' => 'documentation',
        'TAB' => Loc::getMessage('SB_TABS_DOCUMENTATION_TAB'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SB_TABS_DOCUMENTATION_TITLE')
    ],
    [
        'DIV' => 'setting',
        'TAB' => Loc::getMessage('SB_TABS_SETTING_TAB'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SB_TABS_SETTING_TITLE')
    ],
    [
        'DIV' => 'pay_systems',
        'TAB' => Loc::getMessage('SB_TABS_PAY_SYSTEMS_TAB'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SB_TABS_PAY_SYSTEMS_TITLE')
    ],
    [
        'DIV' => 'recipient',
        'TAB' => Loc::getMessage('SB_TABS_RECIPIENT_TAB'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SB_TABS_RECIPIENT_TITLE')
    ],
    [
        'DIV' => 'order',
        'TAB' => Loc::getMessage('SB_TABS_ORDER_TAB'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SB_TABS_ORDER_TITLE')
    ],
    [
        'DIV' => 'delivery',
        'TAB' => Loc::getMessage('SB_TABS_DELIVERY_TAB'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SB_TABS_DELIVERY_TITLE')
    ]
];

$tabControl = new CAdminForm('tabControl', $arTabs, true, true);
$tabControl->SetShowSettings(false);
unset($arTabs);

$arFields = [
    'api_token' => Option::get($moduleId, 'api_token'),
    'secret_token' => Option::get($moduleId, 'secret_token'),

    'default_width' => Option::get($moduleId, 'default_width'),
    'default_height' => Option::get($moduleId, 'default_height'),
    'default_length' => Option::get($moduleId, 'default_length'),
    'default_weight' => Option::get($moduleId, 'default_weight'),

    'pay_systems_cash' => unserialize(Option::get($moduleId, 'pay_systems_cash')),
    'pay_systems_card' => unserialize(Option::get($moduleId, 'pay_systems_card')),
    'pay_systems_online' => unserialize(Option::get($moduleId, 'pay_systems_online')),
    'pay_systems_last_sync' => Option::get($moduleId, 'pay_systems_last_sync'),

    'recipient_extend' => Option::get($moduleId, 'recipient_extend'),
    'recipient_full_name' => Option::get($moduleId, 'recipient_full_name'),
    'recipient_last_name' => Option::get($moduleId, 'recipient_last_name'),
    'recipient_first_name' => Option::get($moduleId, 'recipient_first_name'),
    'recipient_middle_name' => Option::get($moduleId, 'recipient_middle_name'),
    'recipient_phone' => Option::get($moduleId, 'recipient_phone'),
    'recipient_email' => Option::get($moduleId, 'recipient_email'),

    'order_ab_test' => Option::get($moduleId, 'order_ab_test'),
    'order_catalog' => Option::get($moduleId, 'order_catalog'),
    'order_offers' => Option::get($moduleId, 'order_offers'),
    'order_properties' => unserialize(Option::get($moduleId, 'order_properties')),

    'delivery_template' => Option::get($moduleId, 'delivery_template'),
];

if (System::checkUpdateModule()) {
    echo BeginNote(),
        Loc::getMessage('SB_NOTE_CURRENT_VERSION', ['#MODULE_VERSION#' => System::getModuleVersion()]),
        Loc::getMessage('SB_NOTE_NOT_LAST_VERSION'),
        Loc::getMessage('SB_NOTE_LINK_UPDATE', ['#MODULE_ID#' => System::getModuleId()]),
        EndNote();
} else {
    echo BeginNote(),
        Loc::getMessage('SB_NOTE_CURRENT_VERSION', ['#MODULE_VERSION#' => System::getModuleVersion()]),
        Loc::getMessage('SB_NOTE_LAST_VERSION'),
        EndNote();
}

echo BeginNote(), Loc::getMessage('SB_NOTE_HINT'), EndNote();
?>
<style>
    .documentation__item {padding: 0 10px 10px}
    .documentation__title {font-size: 16px;color: #2E569C;cursor: pointer;}
    .documentation__title:hover {text-decoration: underline}
    .documentation__desc {margin: 10px;}
    .documentation__item .documentation__desc {display: none}
    .documentation__item.active .documentation__desc {display: block}
    .recipient__field {display: none}
    .recipient__field.active {display: table-row}
</style>
<form method="POST" action="<?= $APPLICATION->GetCurPage() ?>?lang=<?= LANGUAGE_ID; ?>&mid=<?= $mid ?>" name="post_form">
    <?= bitrix_sessid_post(); ?>
    <input type="hidden" name="recipient_extend" value="<?= $arFields['recipient_extend']; ?>">

    <?php
    $tabControl->Begin();

    // Tab с документацией модуля
    $tabControl->BeginNextFormTab();

    $tabControl->AddSection('documentation', Loc::getMessage('SB_SECTION_DOCUMENTATION_ABOUT'));
    $tabControl->BeginCustomField('documentation_about_text', '', true);
    ?>
    <tr><td>
        <div class="documentation__list">
            <div class="documentation__item">
                <div class="documentation__title"><?= Loc::getMessage('DOCUMENTATION_ABOUT_ITEM1_TITLE'); ?></div>
                <div class="documentation__desc"><?= Loc::getMessage('DOCUMENTATION_ABOUT_ITEM1_DESC') ?></div>
            </div>
            <div class="documentation__item">
                <div class="documentation__title"><?= Loc::getMessage('DOCUMENTATION_ABOUT_ITEM2_TITLE'); ?></div>
                <div class="documentation__desc"><?= Loc::getMessage('DOCUMENTATION_ABOUT_ITEM2_DESC'); ?></div>
            </div>
        </div>
    </td></tr>
    <?php
    $tabControl->EndCustomField('documentation_about_text', '');

    $tabControl->AddSection('documentation_faq', Loc::getMessage('SB_SECTION_DOCUMENTATION_FAQ'));
    $tabControl->BeginCustomField('documentation_faq_text', '', true);
    ?>
    <tr><td>
        <div class="documentation__list">
            <div class="documentation__item">
                <div class="documentation__title"><?= Loc::getMessage('DOCUMENTATION_FAQ_ITEM1_TITLE'); ?></div>
                <div class="documentation__desc"><?= Loc::getMessage('DOCUMENTATION_FAQ_ITEM1_DESC') ?></div>
            </div>
            <div class="documentation__item">
                <div class="documentation__title"><?= Loc::getMessage('DOCUMENTATION_FAQ_ITEM2_TITLE'); ?></div>
                <div class="documentation__desc"><?= Loc::getMessage('DOCUMENTATION_FAQ_ITEM2_DESC'); ?></div>
            </div>
            <div class="documentation__item">
                <div class="documentation__title"><?= Loc::getMessage('DOCUMENTATION_FAQ_ITEM3_TITLE'); ?></div>
                <div class="documentation__desc"><?= Loc::getMessage('DOCUMENTATION_FAQ_ITEM3_DESC'); ?></div>
            </div>
            <div class="documentation__item">
                <div class="documentation__title"><?= Loc::getMessage('DOCUMENTATION_FAQ_ITEM4_TITLE'); ?></div>
                <div class="documentation__desc"><?= Loc::getMessage('DOCUMENTATION_FAQ_ITEM4_DESC'); ?></div>
            </div>
            <div class="documentation__item">
                <div class="documentation__title"><?= Loc::getMessage('DOCUMENTATION_FAQ_ITEM5_TITLE'); ?></div>
                <div class="documentation__desc"><?= Loc::getMessage('DOCUMENTATION_FAQ_ITEM5_DESC'); ?></div>
            </div>
        </div>
    </td></tr>
    <?php
    $tabControl->EndCustomField('documentation_faq_text', '');

    $tabControl->AddSection('documentation_info', Loc::getMessage('SB_SECTION_DOCUMENTATION_INFO'));
    $tabControl->BeginCustomField('documentation_info_text', '', true);
    ?>
    <tr><td>
        <div class="documentation__list">
            <div class="documentation__item">
                <div class="documentation__title"><?= Loc::getMessage('DOCUMENTATION_INFO_ITEM1_TITLE'); ?></div>
                <div class="documentation__desc"><?= Loc::getMessage('DOCUMENTATION_INFO_ITEM1_DESC') ?></div>
            </div>
            <div class="documentation__item">
                <div class="documentation__title"><?= Loc::getMessage('DOCUMENTATION_INFO_ITEM2_TITLE'); ?></div>
                <div class="documentation__desc"><?= Loc::getMessage('DOCUMENTATION_INFO_ITEM2_DESC'); ?></div>
            </div>
            <div class="documentation__item">
                <div class="documentation__title"><?= Loc::getMessage('DOCUMENTATION_INFO_ITEM3_TITLE'); ?></div>
                <div class="documentation__desc"><?= Loc::getMessage('DOCUMENTATION_INFO_ITEM3_DESC'); ?></div>
            </div>
            <div class="documentation__item">
                <div class="documentation__title"><?= Loc::getMessage('DOCUMENTATION_INFO_ITEM4_TITLE'); ?></div>
                <div class="documentation__desc"><?= Loc::getMessage('DOCUMENTATION_INFO_ITEM4_DESC'); ?></div>
            </div>
        </div>
    </td></tr>
    <?php
    $tabControl->EndCustomField('documentation_info_text', '');

    // Tab с настройками модуля
    $tabControl->BeginNextFormTab();

    // Формируем блок
    $tabControl->AddSection('main', Loc::getMessage('SB_SECTION_MAIN'));
    $tabControl->BeginCustomField('api_token', Loc::getMessage('SB_FIELD_API_TOKEN'), true);
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <input type="text" name="api_token" value="<?= $arFields['api_token'] ?>" size="80">
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('api_token', '');
    $tabControl->BeginCustomField('secret_token', Loc::getMessage('SB_FIELD_SECRET_TOKEN'), true);
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <input type="text" name="secret_token" value="<?= $arFields['secret_token'] ?>" size="80">
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('secret_token', '');

    $tabControl->AddSection('default_dimensions', Loc::getMessage('SB_SECTION_DEFAULT_DIMENSIONS'));
    $tabControl->BeginCustomField('default_width', Loc::getMessage('SB_DEFAULT_WIDTH'));
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <input type="text" name="default_width" value="<?= $arFields['default_width'] ?>" size="80">
            <?= Loc::getMessage('SB_DEFAULT_DIMENSIONS_UNIT'); ?>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('default_width', '');
    $tabControl->BeginCustomField('default_height', Loc::getMessage('SB_DEFAULT_HEIGHT'));
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <input type="text" name="default_height" value="<?= $arFields['default_height'] ?>" size="80">
            <?= Loc::getMessage('SB_DEFAULT_DIMENSIONS_UNIT'); ?>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('default_height', '');
    $tabControl->BeginCustomField('default_length', Loc::getMessage('SB_DEFAULT_LENGTH'));
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <input type="text" name="default_length" value="<?= $arFields['default_length'] ?>" size="80">
            <?= Loc::getMessage('SB_DEFAULT_DIMENSIONS_UNIT'); ?>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('default_length', '');
    $tabControl->BeginCustomField('default_weight', Loc::getMessage('SB_DEFAULT_WEIGHT'));
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <input type="text" name="default_weight" value="<?= $arFields['default_weight'] ?>" size="80">
            <?= Loc::getMessage('SB_DEFAULT_WEIGHT_UNIT'); ?>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('default_weight', '');

    // Tab c платежными системами
    $tabControl->BeginNextFormTab();

    $tabControl->AddSection('pay_systems', Loc::getMessage('SB_SECTION_PAY_SYSTEMS'));

    $rsPaySystem = PaySystem\Manager::getList([
        'order' => ['ID' => 'ASC', 'NAME' => 'ASC'],
        'filter' => ['ACTIVE' => 'Y'],
    ]);

    $arPaySystems = [];
    while ($arPaySystem = $rsPaySystem->fetch())
        $arPaySystems[$arPaySystem['ID']] = $arPaySystem;
    unset($rsPaySystem, $arPaySystem);

    $tabControl->BeginCustomField('pay_systems_cash', Loc::getMessage('SB_FIELD_PAY_SYSTEMS_CASH'), true);
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="pay_systems_cash[]" multiple size="5">
                <option value=""><?= Loc::getMessage('SB_SELECT_PAY_SYSTEMS') ?></option>
                <?php
                foreach ($arPaySystems as $key => $arPaySystem) {
                    $selected = in_array($key, $arFields['pay_systems_cash']) ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>[' . $key . '] ' . $arPaySystem['NAME'] . '</option>';
                }
                unset($key, $value, $selected);
                ?>
            </select>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('pay_systems_cash', '');
    $tabControl->BeginCustomField('pay_systems_card', Loc::getMessage('SB_FIELD_PAY_SYSTEMS_CARD'), true);
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="pay_systems_card[]" multiple size="5">
                <option value=""><?= Loc::getMessage('SB_SELECT_PAY_SYSTEMS') ?></option>
                <?php
                foreach ($arPaySystems as $key => $arPaySystem) {
                    $selected = in_array($key, $arFields['pay_systems_card']) ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>[' . $key . '] ' . $arPaySystem['NAME'] . '</option>';
                }
                unset($key, $arPaySystem, $selected);
                ?>
            </select>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('pay_systems_card', '');
    $tabControl->BeginCustomField('pay_systems_online', Loc::getMessage('SB_FIELD_PAY_SYSTEMS_ONLINE'), true);
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="pay_systems_online[]" multiple size="5">
                <option value=""><?= Loc::getMessage('SB_SELECT_PAY_SYSTEMS') ?></option>
                <?php
                foreach ($arPaySystems as $key => $arPaySystem) {
                    $selected = in_array($key, $arFields['pay_systems_online']) ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>[' . $key . '] ' . $arPaySystem['NAME'] . '</option>';
                }
                unset($key, $arPaySystem, $selected);
                ?>
            </select>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('pay_systems_online', '');
    unset($arPaySystems);

    $tabControl->AddSection('pay_systems_manual_sync', Loc::getMessage('SB_SECTION_PAY_SYSTEMS_MANUAL_SYNC'));
    $tabControl->BeginCustomField('pay_systems_sync', Loc::getMessage('SB_FIELD_PAY_SYSTEMS_SYNC'));
    ?>
    <tr>
        <td colspan="2" style="text-align:center">
            <input type="button" value="<?= Loc::getMessage('SB_BUTTON_PAY_SYSTEMS_SYNC') ?>" data-action="sync_pay_systems">
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('pay_systems_sync', '');
    $tabControl->BeginCustomField('pay_systems_last_sync', Loc::getMessage('SB_FIELD_PAY_SYSTEMS_LAST_SYNC'));
    ?>
    <tr>
        <td colspan="2" style="text-align:center" data-result="sync_pay_systems">
            <?php
            $lastSync = $arFields['pay_systems_last_sync'] ?
                $tabControl->GetCustomLabelHTML() . ': ' . $arFields['pay_systems_last_sync'] :
                '';
            echo $lastSync;

            unset($lastSync);
            ?>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('pay_systems_last_synchronization', '');

    // Tab о получателе
    $tabControl->BeginNextFormTab();

    $tabControl->AddSection('recipient', Loc::getMessage('SB_SECTION_RECIPIENT'));

    $arOrderProps = [];
    foreach (Internals::getPropertyList() as $property)
        $arOrderProps[$property['CODE']] = $property;

    $tabControl->BeginCustomField('recipient_full_name', Loc::getMessage('SB_FIELD_RECIPIENT_FULL_NAME'), true);
    ?>
    <tr class="recipient__field<?= $arFields['recipient_extend'] != 'Y' ? ' active' : '' ?>">
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="recipient_full_name" size="5">
                <option value=""><?= Loc::getMessage('SB_SELECT_PROP') ?></option>
                <?php
                foreach ($arOrderProps as $key => $arProp) {
                    $selected = $key == $arFields['recipient_full_name'] ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>[' . $key . '] ' . $arProp['NAME'] . '</option>';
                }
                unset($key, $arProp, $selected);
                ?>
            </select>
            <a href="" data-recipient-extend data-value="Y"><?= Loc::getMessage('SB_RECIPIENT_EXTEND_Y') ?></a>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('recipient_full_name', '');

    $tabControl->BeginCustomField('recipient_last_name', Loc::getMessage('SB_FIELD_RECIPIENT_LAST_NAME'), true);
    ?>
    <tr class="recipient__field<?= $arFields['recipient_extend'] == 'Y' ? ' active' : '' ?>">
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="recipient_last_name" size="5">
                <option value=""><?= Loc::getMessage('SB_SELECT_PROP') ?></option>
                <?php
                foreach ($arOrderProps as $key => $arProp) {
                    $selected = $key == $arFields['recipient_last_name'] ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>[' . $key . '] ' . $arProp['NAME'] . '</option>';
                }
                unset($key, $arProp, $selected);
                ?>
            </select>
            <a href="" data-recipient-extend data-value="N"><?= Loc::getMessage('SB_RECIPIENT_EXTEND_N') ?></a>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('recipient_last_name', '');

    $tabControl->BeginCustomField('recipient_first_name', Loc::getMessage('SB_FIELD_RECIPIENT_FIRST_NAME'), true);
    ?>
    <tr class="recipient__field<?= $arFields['recipient_extend'] == 'Y' ? ' active' : '' ?>">
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="recipient_first_name" size="5">
                <option value=""><?= Loc::getMessage('SB_SELECT_PROP') ?></option>
                <?php
                foreach ($arOrderProps as $key => $arProp) {
                    $selected = $key == $arFields['recipient_first_name'] ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>[' . $key . '] ' . $arProp['NAME'] . '</option>';
                }
                unset($key, $arProp, $selected);
                ?>
            </select>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('recipient_first_name', '');

    $tabControl->BeginCustomField('recipient_middle_name', Loc::getMessage('SB_FIELD_RECIPIENT_MIDDLE_NAME'), true);
    ?>
    <tr class="recipient__field<?= $arFields['recipient_extend'] == 'Y' ? ' active' : '' ?>">
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="recipient_middle_name" size="5">
                <option value=""><?= Loc::getMessage('SB_SELECT_PROP') ?></option>
                <?php
                foreach ($arOrderProps as $key => $arProp) {
                    $selected = $key == $arFields['recipient_middle_name'] ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>[' . $key . '] ' . $arProp['NAME'] . '</option>';
                }
                unset($key, $arProp, $selected);
                ?>
            </select>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('recipient_middle_name', '');

    $tabControl->BeginCustomField('recipient_phone', Loc::getMessage('SB_FIELD_RECIPIENT_PHONE'), true);
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="recipient_phone" size="5">
                <option value=""><?= Loc::getMessage('SB_SELECT_PROP') ?></option>
                <?php
                foreach ($arOrderProps as $key => $arProp) {
                    $selected = $key == $arFields['recipient_phone'] ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>[' . $key . '] ' . $arProp['NAME'] . '</option>';
                }
                unset($key, $arProp, $selected);
                ?>
            </select>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('recipient_phone', '');
    $tabControl->BeginCustomField('recipient_email', Loc::getMessage('SB_FIELD_RECIPIENT_EMAIL'), true);
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="recipient_email" size="5">
                <option value=""><?= Loc::getMessage('SB_SELECT_PROP') ?></option>
                <?php
                foreach ($arOrderProps as $key => $arProp) {
                    $selected = $key == $arFields['recipient_email'] ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>[' . $key . '] ' . $arProp['NAME'] . '</option>';
                }
                unset($key, $arProp, $selected);
                ?>
            </select>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('recipient_email', '');
    unset($arOrderProps);

    // Tab модуль оформления заказа
    $tabControl->BeginNextFormTab();

    // Формируем блок
    $tabControl->AddSection('order_testing', Loc::getMessage('SB_SECTION_ORDER_TESTING'));
    $tabControl->BeginCustomField('order_ab_test', Loc::getMessage('SB_FIELD_ORDER_AB_TEST'), true);
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <input type="text" name="order_ab_test" value="<?= $arFields['order_ab_test'] ?>" size="80">
            <?= Loc::getMessage('SB_FIELD_ORDER_AB_TEST_UNIT'); ?>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('order_ab_test', '');

    $tabControl->AddSection('order_cop', Loc::getMessage('SB_SECTION_ORDER_COP'));

    $rsIBlock = Iblock\IblockTable::getList([
        'order' => ['ID' => 'ASC', 'NAME' => 'ASC'],
        'filter' => ['ACTIVE' => 'Y'],
        'select' => ['ID', 'NAME']
    ]);

    $arIBlock = [];
    while ($iBlock = $rsIBlock->fetch())
        $arIBlock[$iBlock['ID']] = $iBlock['NAME'];
    unset($rsIBlock, $iBlock);

    $tabControl->BeginCustomField('order_catalog', Loc::getMessage('SB_FIELD_ORDER_CATALOG'), true);
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="order_catalog">
                <option value="0"><?= Loc::getMessage('SB_SELECT_IBLOCK') ?></option>
                <?php
                foreach ($arIBlock as $key => $value) {
                    $selected = $key == $arFields['order_catalog'] ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
                }
                unset($key, $value, $selected);
                ?>
            </select>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('order_catalog', '');

    $tabControl->BeginCustomField('order_offers', Loc::getMessage('SB_FIELD_ORDER_OFFERS'), true);
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="order_offers">
                <option value="0"><?= Loc::getMessage('SB_SELECT_IBLOCK') ?></option>
                <?php
                foreach ($arIBlock as $key => $value) {
                    $selected = $key == $arFields['order_offers'] ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
                }
                unset($key, $value, $selected);
                ?>
            </select>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('order_offers', '');
    unset($arIBlock);

    $tabControl->BeginCustomField('order_properties', Loc::getMessage('SB_FIELD_ORDER_PROPERTIES'), true);

    $rsProperties = Iblock\PropertyTable::getList([
        'order' => ['ID' => 'ASC', 'NAME' => 'ASC'],
        'filter' => ['IBLOCK_ID' => [$arFields['order_catalog'], $arFields['order_offers']], 'ACTIVE' => 'Y'],
    ]);

    $arProperties = [];
    while ($property = $rsProperties->fetch())
        $arProperties[$property['ID']] = $property;
    unset($rsProperties, $property);
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="order_properties[]" multiple size="5">
                <option value=""><?= Loc::getMessage('SB_SELECT_PROPS') ?></option>
                <?php
                foreach ($arProperties as $key => $value) {
                    if ($value['CODE'] === 'CML2_LINK') continue;

                    $selected = in_array($value['CODE'], $arFields['order_properties']) ? ' selected' : '';
                    echo '<option value="' .  $value['CODE'] . '"' . $selected . '>[' . $key . '] ' . $value['NAME'] . '</option>';
                }
                unset($key, $value, $selected);
                ?>
            </select>
        </td>
    </tr>
    <?
    unset($arProperties);
    $tabControl->EndCustomField('order_properties', '');

    // Tab модуль служб доставки
    $tabControl->BeginNextFormTab();

    $tabControl->AddSection('delivery_templates', Loc::getMessage('SB_SECTION_DELIVERY_TEMPLATES'));
    $tabControl->BeginCustomField('delivery_template', Loc::getMessage('SB_FIELD_DELIVERY_TEMPLATE'));

    // Получаем список шаблонов
    $arTemplates = [];
    $pathComponentWidget = $_SERVER['DOCUMENT_ROOT'] . '/local/components/salesbeat/sale.delivery.widget/templates/';
    $objDirectory = new IO\Directory($pathComponentWidget, $siteId = null);
    if ($objDirectory->isExists()) {
        $objChildren = $objDirectory->getChildren();

        foreach ($objChildren as $objChild) {
            if ($objChild->isDirectory()) {
                $dirName =  $objChild->getName();
                $templateParameterName = $dirName;
                include $pathComponentWidget . $dirName . '/.parameters.php';

                $arTemplates[$dirName] = $templateParameterName;
            }
        }
        ksort($arTemplates);
        unset($objChildren, $objChild);
    }
    unset($pathComponentWidget, $objDirectory);

    if (empty($arFields['delivery_template']) && isset($arTemplates['.default']))
        $arFields['delivery_template'] = '.default';
    ?>
    <tr>
        <td width="40%"><?= $tabControl->GetCustomLabelHTML() ?>:</td>
        <td width="60%">
            <select name="delivery_template">
                <?php
                foreach ($arTemplates as $key => $value) {
                    $selected = $key == $arFields['delivery_template'] ? ' selected' : '';
                    echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
                }
                unset($key, $value, $selected);
                ?>
            </select>
        </td>
    </tr>
    <?php
    unset($arTemplates);
    $tabControl->EndCustomField('delivery_template', '');

    // Кнопки
    $tabControl->Buttons([
        'back_url' => $APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID . '&mid=' . $mid,
        'btnApply' => false, // не показывать кнопку применить
    ]);

    $tabControl->Show();
    $tabControl->ShowWarnings('post_form', null);
    ?>
</form>
<script>
    (function () {
        const documentations = document.querySelectorAll('.documentation__title');
        for (let i in documentations) {
            if (!documentations.hasOwnProperty(i)) continue;

            BX.bind(documentations[i], 'click', function (e) {
                e.preventDefault();

                const parent = this.closest('.documentation__item');
                parent.classList.toggle('active');
            });
        }

        const recipientExtends = document.querySelectorAll('[data-recipient-extend]');
        const recipientExtendValue = document.querySelector('[name="recipient_extend"]');
        const recipientFields = document.querySelectorAll('.recipient__field');
        for (let i in recipientExtends) {
            if (!recipientExtends.hasOwnProperty(i)) continue;

            BX.bind(recipientExtends[i], 'click', function (e) {
                e.preventDefault();

                recipientExtendValue.value = this.getAttribute('data-value');
                for (let n in recipientFields) {
                    if (recipientFields.hasOwnProperty(n))
                        recipientFields[n].classList.toggle('active');
                }
            });
        }

        const button = document.querySelector('[data-action="sync_pay_systems"]');
        const result = document.querySelector('[data-result="sync_pay_systems"]');
        BX.bind(button, 'click', function (e) {
            e.preventDefault();

            result.innerHTML = '<?= Loc::getMessage('SB_JS_SYNC_PAY_SYSTEMS_LOAD') ?>';
            BX.ajax({
                method: 'POST',
                url: '/bitrix/services/salesbeat.sale/sync_pay_systems.php',
                dataType: 'JSON',
                data: {},
                start: true,
                cache: false,
                onsuccess: function (data) {
                    if (data.status === 'success') {
                        alert('<?= Loc::getMessage('SB_JS_SYNC_PAY_SYSTEMS_SUCCESS') ?>');
                        result.innerHTML = '<?= Loc::getMessage('SB_FIELD_PAY_SYSTEMS_LAST_SYNC') ?>: ' + data.message;
                    } else {
                        alert('<?= Loc::getMessage('SB_JS_SYNC_PAY_SYSTEMS_ERROR') ?>');
                        result.innerHTML = data.message;
                    }
                },
                onfailure: function (data) {
                    alert('<?= Loc::getMessage('SB_JS_SYNC_PAY_SYSTEMS_ERROR') ?>');
                    result.innerHTML = data;
                }
            });
        });
    })();
</script>