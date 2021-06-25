<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Context;
use \Bitrix\Main\FileTable;
use \Bitrix\Main\Config\Option;
use \Bitrix\Highloadblock\HighloadBlockTable;
use \Bitrix\Sale;
use \Bitrix\Catalog;
use \Salesbeat\Sale\System;
use \Salesbeat\Sale\Api;
use \Salesbeat\Sale\Tools;

class SbSaleOrderAjax extends CBitrixComponent
{
    protected $fUserId = null;
    protected $user = [];
    protected $siteId = null;

    protected $order = null;
    protected $basket = null;
    protected $basketItems = [];

    protected $moduleId = '';
    protected $catalogId = 0;
    protected $offersId = 0;
    protected $shopUrl = '';
    protected $sbCartId = '';

    public function executeComponent()
    {
        if (!Loader::includeModule('salesbeat.sale')) return;
        if (!Loader::IncludeModule('catalog')) return;
        if (!Loader::IncludeModule('sale')) return;

        $request = Main\Application::getInstance()->getContext()->getRequest();
        $orderId = (int)$request->get('ORDER_ID');

        if ($orderId) {
            $this->order = Sale\Order::load($orderId);
            $this->confirm();
        } else {
            $this->order = [];
            $this->checkout();
        }

        $this->includeComponentTemplate();
    }

    protected function checkout() {
        $this->moduleId = System::getModuleId();
        $this->shopUrl = Tools::getShopUrl();
        $this->catalogId = Option::get($this->moduleId, 'order_catalog');
        $this->offersId = Option::get($this->moduleId, 'order_offers');

        if ($this->checkSbCart()) {
            $this->sbCartId = $_SESSION['sb_cart_id'];
        } else {
            $sbCart = $this->creatSbCart();
            $this->sbCartId = $sbCart['cart_info']['cart_id'];

            $_SESSION['sb_cart_id'] = $this->sbCartId;
        }

        $this->basketItems = $this->getBasketItems();
        if (empty($this->basketItems)) LocalRedirect('/');

        $fields = $this->fillItemsWithFields();
        if (!empty($fields)) $this->basketItems = array_replace_recursive($this->basketItems, $fields);
        $fields = null;

        $properties = $this->fillItemsWithProperties();
        if (!empty($properties)) $this->basketItems = array_replace_recursive($this->basketItems, $properties);
        $properties = null;

        $measureRatio = $this->fillItemsWithMeasureRatio();
        if (!empty($measureRatio)) $this->basketItems = array_replace_recursive($this->basketItems, $measureRatio);
        $measureRatio = null;

        $this->arResult = [
            'cart_id' => $this->getFuserId(),
            'sb_cart_id' => $this->sbCartId,
            'items' => $this->basketItems,
            'count' => $this->getBasket()->count(),
            'price' => $this->getBasket()->getBasePrice(),
        ];
    }

    protected function getFUserId()
    {
        if ($this->fUserId === null)
            $this->fUserId = Sale\Fuser::getId();

        return $this->fUserId;
    }

    protected function getUser(): array
    {
        global $USER;

        if (empty($this->user) && !empty($USER->GetParam('USER_ID'))) {
            $userTable = new Main\UserTable;
            $user = $userTable->getById($USER->GetParam('USER_ID'))->Fetch();

            $this->user = [
                'shop_client_id' => $user['ID'],
                'first_name' => $user['NAME'],
                'last_name' => $user['LAST_NAME'],
                'middle_name' => $user['SECOND_NAME'],
                'phone' => $user['PERSONAL_PHONE'],
                'email' => $user['EMAIL']
            ];
        }

        return $this->user;
    }

    public function getSiteId()
    {
        if ($this->siteId === null)
            $this->siteId = Context::getCurrent()->getSite();

        return $this->siteId;
    }

    protected function getBasket()
    {
        if ($this->basket === null)
            $this->basket = Sale\Basket::loadItemsForFUser($this->getFuserId(), $this->getSiteId());

        return $this->basket;
    }

    protected function getBasketItems(): array
    {
        $defaultWidth = Option::get($this->moduleId, 'default_width');
        $defaultHeight = Option::get($this->moduleId, 'default_height');
        $defaultLength = Option::get($this->moduleId, 'default_length');
        $defaultWeight = Option::get($this->moduleId, 'default_weight');

        $basketItems = $this->getBasket()->getBasketItems();

        $result = [];
        foreach ($basketItems as $basketItem) {
            $productId = $basketItem->getProductId();
            $weight = $basketItem->getWeight();

            $result[$productId] = [
                'id' => $productId,
                'name' => $basketItem->getField('NAME'),
                'quantity' => $basketItem->getQuantity(),
                'price' => $basketItem->getBasePrice(),
                'lot' => 1,
                'min_quantity' => 1,
                'max_quantity' => 9999,
                'dimensions' => [
                    'x' => $defaultWidth,
                    'y' => $defaultHeight,
                    'z' => $defaultLength,
                ],
                'features' => [],
                'weight' => $weight > 0 ? ceil($weight) : $defaultWeight,
                'image_url' => '',
            ];
        }

        return $result;
    }

    protected function getProductsId(): array
    {
        if (empty($this->basketItems)) return [];
        return array_column($this->basketItems, 'id');
    }

    protected function fillItemsWithFields(): array
    {
        $elements = Catalog\ProductTable::getList([
            'filter' => ['=ID' => $this->getProductsId()],
            'select' => ['ID', 'QUANTITY', 'WIDTH', 'LENGTH', 'HEIGHT']
        ]);

        $result = [];
        while ($element = $elements->fetch()) {
            if (!empty($element['QUANTITY'])) $result[$element['ID']]['max_quantity'] = $element['QUANTITY'];

            $xyz = [$element['WIDTH'], $element['LENGTH'], $element['HEIGHT']];
            rsort($xyz);

            if (!empty($xyz[0])) $result[$element['ID']]['dimensions']['x'] = $xyz[0];
            if (!empty($xyz[1])) $result[$element['ID']]['dimensions']['y'] = $xyz[1];
            if (!empty($xyz[2])) $result[$element['ID']]['dimensions']['z'] = $xyz[2];
        }

        return $result;
    }

    protected function fillItemsWithProperties(): array
    {
        $elementsSelect = ['IBLOCK_ID', 'ID', 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'PROPERTY_CML2_LINK'];

        $propertyList = unserialize(Option::get($this->moduleId, 'order_properties'));
        if (!empty($propertyList)) {
            foreach ($propertyList as $value) $elementsSelect[] = 'PROPERTY_' . $value;
        }

        $rsElements = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => [$this->catalogId, $this->offersId], '=ID' => $this->getProductsId()],
            false,
            false,
            $elementsSelect
        );

        $cml2LinkList = [];

        $result = [];
        while ($element = $rsElements->GetNextElement()) {
            $fields = $element->GetFields();
            $properties = $element->GetProperties();

            $picture = CFile::GetFileArray($fields['PREVIEW_PICTURE']);
            if (empty($picture)) $picture = CFile::GetFileArray($fields['DETAIL_PICTURE']);

            if (!empty($picture['SRC'])) {
                $result[$fields['ID']]['image_url'] = $this->shopUrl . $picture['SRC'];
            } else if (!empty($fields['PROPERTY_CML2_LINK_VALUE'])) {
                $cml2LinkList[$properties['CML2_LINK']['VALUE']][] = $fields['ID'];
            }

            foreach ($properties as $property) {
                if (!in_array($property['CODE'], $propertyList)) continue;
                if (empty($property['NAME']) || empty($property['VALUE'])) continue;

                if ($property['USER_TYPE'] === 'directory')
                    $property['VALUE'] = $this->searchHlPropertyValue($property);

                $result[$fields['ID']]['features'][] = [
                    'name' => $property['NAME'],
                    'value' => $property['VALUE']
                ];
            }
        }

        if (!empty($cml2LinkList)) {
            $rsElements = CIBlockElement::GetList(
                [],
                ['IBLOCK_ID' => $this->catalogId, '=ID' => array_keys($cml2LinkList)],
                false,
                false,
                ['IBLOCK_ID', 'ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE']
            );

            while ($element = $rsElements->GetNextElement()) {
                $fields = $element->GetFields();

                if (empty($cml2LinkList[$fields['ID']])) continue;

                $picture = CFile::GetFileArray($fields['PREVIEW_PICTURE']);
                if (empty($picture)) $picture = CFile::GetFileArray($fields['DETAIL_PICTURE']);

                if (empty($picture['SRC'])) continue;

                foreach ($cml2LinkList[$fields['ID']] as $id)
                    $result[$id]['image_url'] = $this->shopUrl . $picture['SRC'];
            }
        }

        return $result;
    }

    /**
     * @param array $property
     * @return string
     */
    protected function searchHlPropertyValue(array $property): string
    {
        CModule::IncludeModule('highloadblock');

        $rsData = HighloadBlockTable::getList([
            'filter' => ['TABLE_NAME' => $property['USER_TYPE_SETTINGS']['TABLE_NAME']]
        ]);

        if ($data = $rsData->fetch()) {
            HighloadBlockTable::compileEntity($data);
            $hlDataClass = $data['NAME'] . 'Table';

            $record = $hlDataClass::getList([
                    'filter' => ['UF_XML_ID' => $property['VALUE']],
                    'order' => [
                        'UF_NAME' => 'asc'
                    ],
                ]
            );

            return $record->fetch()['UF_NAME'];
        }

        return $property['VALUE'];
    }

    protected function fillItemsWithMeasureRatio(): array
    {
        $measures = Catalog\ProductTable::getCurrentRatioWithMeasure($this->getProductsId());

        $result = [];
        foreach ($measures as $productId => $measure) {
            $result[$productId] = [
                'lot' => $measure['RATIO'],
                'min_quantity' => $measure['RATIO']
            ];
        }
        return $result;
    }

    protected function checkSbCart(): bool
    {
        return !empty($_SESSION['sb_cart_id']);
    }

    protected function creatSbCart(): array
    {
        $fields = [
            'cart_info' => [
                'shop_cart_id' => $this->fUserId,
                'products' => array_values($this->basketItems)
            ],
            'customer_info' => $this->getUser()
        ];

        return Api::createCart(
            Option::get($this->moduleId, 'secret_token'),
            $fields
        );
    }

    protected function updateSbCart(): array
    {
        $fields = [
            'cart_info' => [
                'shop_cart_id' => $this->fUserId,
                'products' => array_values($this->basketItems)
            ],
            'customer_info' => $this->getUser()
        ];

        return Api::updateCart(
            Option::get($this->moduleId, 'secret_token'),
            $this->sbCartId,
            $fields
        );
    }

    protected function confirm() {
        if (!empty($this->order)) {
            $arOrder = $this->order->getFieldValues();
            $arResult = [
                'ORDER_ID' => $arOrder['ID'],
                'ACCOUNT_NUMBER' => $arOrder['ACCOUNT_NUMBER']
            ];

            foreach (GetModuleEvents('sale', 'OnSaleComponentOrderOneStepFinal', true) as $arEvent)
                ExecuteModuleEventEx($arEvent, [$arResult["ORDER_ID"], &$arOrder, &$this->arParams]);

            $this->arResult['ORDER']['ORDER_ID'] = $this->order->getField('ID');
            $this->arResult['ORDER']['ACCOUNT_NUMBER'] = $this->order->getField('ACCOUNT_NUMBER');
            $this->arResult['ORDER']['DATE_INSERT'] = $this->order->getField('DATE_INSERT');
            $this->arResult['ORDER']['IS_ALLOW_PAY'] = $this->order->isAllowPay() ? 'Y' : 'N';
            $this->arResult['PAYMENT'] = [];

            if ($this->order->isAllowPay()) {
                $paymentCollection = $this->order->getPaymentCollection();

                /** @var Payment $payment */
                foreach ($paymentCollection as $payment) {
                    if (!(int)$payment->getPaymentSystemId() || $payment->isPaid())
                        continue;

                    $this->arResult['PAYMENT'][$payment->getId()] = $payment->getFieldValues();

                    $paySystemService = Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
                    if (!empty($paySystemService)) {
                        $paySysAction = $paySystemService->getFieldsValues();

                        if (
                            $paySystemService->getField('NEW_WINDOW') === 'N' ||
                            $paySystemService->getField('ID') == Sale\PaySystem\Manager::getInnerPaySystemId()
                        ) {
                            /** @var Sale\PaySystem\ServiceResult $initResult */
                            $initResult = $paySystemService->initiatePay($payment, null, Sale\PaySystem\BaseServiceHandler::STRING);
                            if ($initResult->isSuccess()) {
                                $paySysAction['BUFFERED_OUTPUT'] = $initResult->getTemplate();
                            } else {
                                $paySysAction['ERROR'] = $initResult->getErrorMessages();
                            }
                        }

                        $this->arResult['ORDER']['PAYMENT_ID'] = $payment->getId();
                        $this->arResult['ORDER']['PAY_SYSTEM_ID'] = $payment->getPaymentSystemId();

                        $paySysAction['NAME'] = htmlspecialcharsEx($paySysAction['NAME']);
                        $paySysAction['IS_AFFORD_PDF'] = $paySystemService->isAffordPdf();
                        $paySysAction['LOGOTIP'] = CFile::GetFileArray($paySysAction['LOGOTIP']);

                        $this->arResult['PAYMENT'][$payment->getId()]['PAID'] = $payment->getField('PAID');
                        $this->arResult['PAY_SYSTEM_LIST'][$payment->getPaymentSystemId()] = $paySysAction;
                        $this->arResult['PAY_SYSTEM_LIST_BY_PAYMENT_ID'][$payment->getId()] = $paySysAction;
                    } else {
                        $this->arResult['PAY_SYSTEM_LIST'][$payment->getPaymentSystemId()] = [
                            'ERROR' => true
                        ];
                    }
                }
            }
        } else {
            $this->arResult = [];
        }
    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams['token'] = Option::get(System::getModuleId(), 'api_token');
        return $arParams;
    }
}
