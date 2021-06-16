<?php

use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\IO\Directory;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Salesbeat\Sale\Internals;
use \Salesbeat\Sale\Api;

class salesbeat_sale extends CModule
{
	public $MODULE_ID = 'salesbeat.sale';
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = 'N';
    public $PARTNER_NAME = 'Salesbeat';
    public $PARTNER_URI = '//salesbeat.pro';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_CSS;
    public $NEED_MAIN_VERSION = '16.5.11';
    public $NEED_PHP_VERSION = '7.1';
    public $NEED_MODULES = ['main', 'sale'];
    public $ERRORS;

    /**
     * @var false
     */
    public function __construct()
    {
        if (file_exists(__DIR__ . '/version.php')) {
            $this->MODULE_NAME = Loc::getMessage('SB_MODULE_NAME');
            $this->MODULE_DESCRIPTION = Loc::getMessage('SB_MODULE_DESCRIPTION');

            $moduleVersion = [];
            include __DIR__ . '/version.php';

            if (is_array($moduleVersion) && isset($moduleVersion['VERSION'])) {
                $this->MODULE_VERSION = $moduleVersion['VERSION'];
                $this->MODULE_VERSION_DATE = $moduleVersion['VERSION_DATE'];
            }

            $this->ERRORS = false;
        }
    }

    /**
     * Выполняем запрос в БД
     * @param string $fileName
     */
    public function runSql(string $fileName)
    {
        global $DB, $APPLICATION;

        $this->ERRORS = $DB->RunSQLBatch(__DIR__ . '/db/' . mb_strtolower($DB->type) . '/' . $fileName . '.sql');
        if (!empty($this->ERRORS)) {
            $strError = '';
            foreach ($this->ERRORS as $error) $strError .= $error . '<br>';
            $APPLICATION->ThrowException($strError);
        }
    }

    /**
     * Устанавливаем файлы модуля в Bitrix
     */
    public function installFiles()
    {
        CopyDirFiles(
            __DIR__ . '/admin/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/',
            true, true
        );
        CopyDirFiles(
            __DIR__ . '/components/',
            $_SERVER['DOCUMENT_ROOT'] . '/local/components/',
            true, true
        );
        CopyDirFiles(
            __DIR__ . '/js/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/',
            true, true
        );
        CopyDirFiles(
            __DIR__ . '/php_interface/',
            $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/',
            true, true
        );
        CopyDirFiles(
            __DIR__ . '/services/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/services/',
            true, true
        );
        CopyDirFiles(
            __DIR__ . '/themes/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/',
            true, true
        );
    }

    /**
     * Удаляем файлы модуля из Bitrix
     */
    public function unInstallFiles()
    {
        // Удаляем страницы
        DeleteDirFiles(
            __DIR__ . '/admin/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/'
        );

        // Удаляем содержимое компонентов
        $componentList = [
            'sale.basket.small',
            'sale.catalog.element.delivery',
            'sale.delivery.widget',
            'sale.delivery.widget.admin',
            'sale.location.selector',
            'sale.order.ajax'
        ];

        foreach ($componentList as $component) {
            DeleteDirFiles(
                __DIR__ . '/components/salesbeat/' . $component,
                $_SERVER['DOCUMENT_ROOT'] . '/local/components/salesbeat/' . $component
            );
        }

        // Удаляем JS
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/salesbeat.sale');

        // Удаляем обработчики собственной доставки
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/sale_delivery/salesbeat');
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/sale_delivery/salesbeat2');

        // Удаляем сервисы
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/services/salesbeat.sale');

        // Удаляем тему
        DeleteDirFiles(
            __DIR__ . '/themes/.default/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/'
        );
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/icons/salesbeat.sale');
    }

    /**
     * Регистрируем события
     */
    public function registerEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'main', 'OnBeforeProlog', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnBeforeProlog',
            '50'
        );
        $eventManager->registerEventHandler(
            'main', 'OnAdminSaleOrderEdit', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnAdminSaleOrderEdit',
            '100'
        );
        $eventManager->registerEventHandler(
            'main', 'OnAdminListDisplay', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnAdminSaleOrderList',
            '100'
        );
        $eventManager->registerEventHandler(
            'sale', 'OnSaleComponentOrderCreated', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleComponentOrderCreated',
            '100'
        );
        $eventManager->registerEventHandler(
            'sale', 'OnSaleComponentOrderShowAjaxAnswer', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleComponentOrderShowAjaxAnswer',
            '100'
        );
        $eventManager->registerEventHandler(
            'sale', 'OnSaleComponentOrderJsData', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleComponentOrderJsData',
            '100'
        );
        $eventManager->registerEventHandler(
            'sale', 'OnSaleComponentOrderProperties', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleComponentOrderProperties',
            '100'
        );
        $eventManager->registerEventHandler(
            'sale', 'OnSaleComponentOrderResultPrepared', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleComponentOrderResultPrepared',
            '100'
        );
        $eventManager->registerEventHandler(
            'sale', 'OnSaleOrderSaved', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleOrderSaved',
            '100'
        );
    }

    /**
     * Удаляем зарегистрированные события
     */
    public function unRegisterEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'main', 'OnBeforeProlog', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnBeforeProlog'
        );
        $eventManager->unRegisterEventHandler(
            'main', 'OnAdminSaleOrderEdit', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnAdminSaleOrderEdit'
        );
        $eventManager->unRegisterEventHandler(
            'main', 'OnAdminListDisplay', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnAdminSaleOrderList'
        );
        $eventManager->unRegisterEventHandler(
            'sale', 'OnSaleComponentOrderCreated', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleComponentOrderCreated'
        );
        $eventManager->unRegisterEventHandler(
            'sale', 'OnSaleComponentOrderShowAjaxAnswer', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleComponentOrderShowAjaxAnswer'
        );
        $eventManager->unRegisterEventHandler(
            'sale', 'OnSaleComponentOrderJsData', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleComponentOrderJsData'
        );
        $eventManager->unRegisterEventHandler(
            'sale', 'OnSaleComponentOrderProperties', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleComponentOrderProperties'
        );
        $eventManager->unRegisterEventHandler(
            'sale', 'OnSaleComponentOrderResultPrepared', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleComponentOrderResultPrepared'
        );
        $eventManager->unRegisterEventHandler(
            'sale', 'OnSaleOrderSaved', $this->MODULE_ID,
            '\Salesbeat\Sale\Handler', 'OnSaleOrderSaved'
        );
    }

    /**
     * Выполняем модуль
     */
    public function doInstall()
    {
        global $APPLICATION, $step;

        Loader::includeModule('sale');

        $step = (int)$step;
        if ($step < 2) {
            $strError = '';
            $versionModule = ModuleManager::getVersion('main');
            if (!CheckVersion($versionModule, $this->NEED_MAIN_VERSION))
                $strError .= Loc::getMessage('SB_INSTALL_ERROR_VERSION_MODULE', ['#VERSION_MODULE#' => $versionModule]);

            $versionPhp = phpversion();
            if (!CheckVersion($versionPhp, $this->NEED_PHP_VERSION))
                $strError .= Loc::getMessage('SB_INSTALL_ERROR_VERSION_PHP', ['#VERSION_PHP#' => $versionPhp]);

            if (mb_strlen($strError) > 0) {
                $strError .= Loc::getMessage('SB_INSTALL_ERROR_SUPPORT', ['#EMAIL#' => 'hi@salesbeat.pro']);
                $APPLICATION->ThrowException($strError);
            } elseif ($this->ERRORS) {
                $APPLICATION->ThrowException($this->ERRORS);
            }

            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('SB_INSTALL_TITLE', ['#MODULE_NAME#' => $this->MODULE_NAME]),
                __DIR__ . '/step.php'
            );
        } elseif ($step == 2) {
            include __DIR__ . '/../lib/http.php';
            include __DIR__ . '/../lib/api.php';

            // Проверяем токены на валидность
            $resultApi = Api::postCheckTokens($_REQUEST['api_token'], $_REQUEST['secret_token']);
            if (isset($resultApi['data']['valid']) && $resultApi['data']['valid']) {
                // Токены
                Option::set($this->MODULE_ID, 'api_token', $_REQUEST['api_token']);
                Option::set($this->MODULE_ID, 'secret_token', $_REQUEST['secret_token']);

                // Значения по умолчанию
                Option::set($this->MODULE_ID, 'default_width', 0);
                Option::set($this->MODULE_ID, 'default_height', 0);
                Option::set($this->MODULE_ID, 'default_length', 0);
                Option::set($this->MODULE_ID, 'default_weight', 0);
            } else {
                $this->ERRORS = Loc::getMessage('SB_INSTALL_ERROR_TOKENS');
                $APPLICATION->ThrowException($this->ERRORS);
            }

            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('SB_INSTALL_TITLE', ['#MODULE_NAME#' => $this->MODULE_NAME]),
                __DIR__ . '/step2.php'
            );
        } elseif ($step == 3) {
            include __DIR__ . '/../lib/system.php';
            include __DIR__ . '/../lib/internals.php';

            // Деактивируем отмеченные свойства
            if (!empty($_REQUEST['props']) && is_array($_REQUEST['props'])) {
                foreach ($_REQUEST['props'] as $property)
                    Internals::updateProperty((int)$property, ['ACTIVE' => 'N']);
            }

            // Выполняем установку
			$this->runSql('install');
            $this->installFiles();
            $this->registerEvents();

            Internals::createProperties(); // Создаем свойства
            Internals::activateLocation(); // Активируем свойства

            if (!empty($this->ERRORS)) {
                $APPLICATION->ThrowException($this->ERRORS);
            } else {
                ModuleManager::registerModule($this->MODULE_ID);
            }

            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('SB_INSTALL_TITLE', ['#MODULE_NAME#' => $this->MODULE_NAME]),
                __DIR__ . '/step3.php'
            );
        }
    }

    /**
     * Удаляем модуль
     */
    function doUninstall()
    {
        global $APPLICATION, $step;

        Loader::includeModule('sale');

        $step = (int)$step;
        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('SB_UNINSTALL_TITLE', ['#MODULE_NAME#' => $this->MODULE_NAME]),
                __DIR__ . '/unstep.php'
            );
        } elseif ($step == 2) {
            include __DIR__ . '/../lib/system.php';
            include __DIR__ . '/../lib/internals.php';

            if ($_REQUEST['delete_tables'] == 'Y') $this->runSql('uninstall');
            $this->unInstallFiles();
            $this->unRegisterEvents();
            if ($_REQUEST['delete_settings'] == 'Y') Option::delete($this->MODULE_ID);

            // Деактивируем свойства
            Internals::deActivateLocation();

            if (!empty($this->ERRORS)) {
                $APPLICATION->ThrowException($this->ERRORS);
            } else {
                ModuleManager::unRegisterModule($this->MODULE_ID);
            }

            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('SB_UNINSTALL_TITLE', ['#MODULE_NAME#' => $this->MODULE_NAME]),
                __DIR__ . '/unstep2.php'
            );
        }
    }
}