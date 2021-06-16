<?php
// Уведомления
$MESS['SB_NOTE_CURRENT_VERSION'] = 'Текущая версия модуля: #MODULE_VERSION#';
$MESS['SB_NOTE_LAST_VERSION'] = '<hr>Используется актуальная версия';
$MESS['SB_NOTE_NOT_LAST_VERSION'] = '<hr>Используется не актуальная версия<hr>';
$MESS['SB_NOTE_LINK_UPDATE'] = '<a href="/bitrix/admin/update_system_partner.php?tabControl_active_tab=tab2&addmodule=#MODULE_ID#" 
target="_blank" style="color:red;">Скачайте новое обновление</a>';

$MESS['SB_NOTE_HINT'] = 'Получить API-токен и Secret-токен вы сможете перейдя по этой ссылке: 
<a href="https://app.salesbeat.pro/#/shop/info" target="_blank">https://app.salesbeat.pro/#/shop/info</a>.<br><br>
Не забудьте сопоставить и синхронизировать платежные системы.<br>
Не забудьте сопоставить свойства заказа о получателе, это необходимо для корректной выгрузки заказов.<br><br>
Модуль служб доставки работает только в рамках компонента: sale.order.ajax.<br>';

// Заголовки табов
$MESS['SB_TABS_DOCUMENTATION_TAB'] = 'Документация';
$MESS['SB_TABS_DOCUMENTATION_TITLE'] = 'Документация модуля';

$MESS['SB_TABS_SETTING_TAB'] = 'Общие настройки';
$MESS['SB_TABS_SETTING_TITLE'] = 'Настройка параметров модуля';

$MESS['SB_TABS_PAY_SYSTEMS_TAB'] = 'Платежные системы';
$MESS['SB_TABS_PAY_SYSTEMS_TITLE'] = 'Настройка платежных систем';

$MESS['SB_TABS_RECIPIENT_TAB'] = 'О получателе';
$MESS['SB_TABS_RECIPIENT_TITLE'] = 'Настройка пользовательских свойств';

$MESS['SB_TABS_ORDER_TAB'] = 'Модуль оформления заказов';
$MESS['SB_TABS_ORDER_TITLE'] = 'Настройка модуля оформления заказов';

$MESS['SB_TABS_DELIVERY_TAB'] = 'Модуль служб доставки';
$MESS['SB_TABS_DELIVERY_TITLE'] = 'Настройка модуля служб доставки';

// Списки
$MESS['SB_SELECT_PAY_SYSTEMS'] = '- Выберите платежные системы - ';
$MESS['SB_SELECT_PROP'] = '- Выберите свойство - ';
$MESS['SB_SELECT_PROPS'] = '- Выберите свойства - ';
$MESS['SB_SELECT_IBLOCK'] = '- Выберите инфоблок - ';

// Tab с документацией модуля
$MESS['SB_SECTION_DOCUMENTATION_ABOUT'] = 'О нас';
$MESS['DOCUMENTATION_ABOUT_ITEM1_TITLE'] = '- Что такое Salesbeat';
$MESS['DOCUMENTATION_ABOUT_ITEM1_DESC'] = 'Salesbeat — сервис, позволяющий подключить расчёт доставки всеми популярными службами, выгружать заказы в личные<br>
кабинеты служб доставки, а также гибко настраивать тарифы и прозрачно добавлять свои способы доставки.<br>
Средняя скорость расчёта меньше 0.2с.';
$MESS['DOCUMENTATION_ABOUT_ITEM2_TITLE'] = '- Для чего нужен модуль';
$MESS['DOCUMENTATION_ABOUT_ITEM2_DESC'] = 'Модуль обеспечивает интеграцию вашего интернет-магазина с нашим сервисом Salesbeat, содержащим интеграции со службами доставки.<br><br>
Вместе с модулем в Битрикс становятся доступны два новых вида служб доставки: "Salesbeat с виджетом" (информация о доставке на<br>
странице оформления заказа будет отображаться в нашем удобном виджете) и "Salesbeat без виджета" (информация о доставке на странице<br>
оформления заказа будет отображаться в стандартных интерфейсах вашей страницы оформления).<br><br>
Новые способы доставки позволяют вашим покупателям выбрать удобный для них способ доставки.';

$MESS['SB_SECTION_DOCUMENTATION_FAQ'] = 'Часто задавемые вопросы';
$MESS['DOCUMENTATION_FAQ_ITEM1_TITLE'] = '- Где? Как? И вообще можно ли выгружать заказы?';
$MESS['DOCUMENTATION_FAQ_ITEM1_DESC'] = 'Да, мы выгружаем заказы в транспортные компании, но на данный момент не во все.<br><br>
Перейти на страницу выгрузки заказов вы можете, нажав по этой ссылке: <a href="/bitrix/admin/sb_delivery_order_list.php" target="_blank">Выгрузка заказов</a>.<br>
Либо можете перейти через древо меню: Магазин -> Salesbeat -> Выгрузка заказов.<br><br>
Осуществить выгрузку заказа вы можете, нажав на три черточки напротив ID заказа, после нажав на зеленую кнопку "Отправить заказ",<br>
либо можете массово выгрузить свои заказы, для этого выделите нужные заказы, после в действиях внизу таблицы выберите "Отправить заказы".';
$MESS['DOCUMENTATION_FAQ_ITEM2_TITLE'] = '- Чем отличаются способы доставки?';
$MESS['DOCUMENTATION_FAQ_ITEM2_DESC'] = 'Ключевая разница между двумя способами это принцип работы. <br><br>
Salesbeat с виджетом &mdash; работает через наш виджет Salesbeat, благодаря этому способу вы сможете отображать доставку во весь блок,<br>
либо сделать отображение в модальном окне.<br><br>
Salesbeat без виджета &mdash; работает через профили доставки, где каждый профиль на странице оформления заказа будет отображаться отдельным<br>
способом доставки, при этом наш виджет не используется.';
$MESS['DOCUMENTATION_FAQ_ITEM3_TITLE'] = '- Для чего нужны разные шаблоны на странице оформления заказа?';
$MESS['DOCUMENTATION_FAQ_ITEM3_DESC'] = 'Многошаблонность реализована специально, для того, чтобы была возможность гибкой модификации нашего модуля под каждое решение.<br>
Благодаря этому функционалу ваши программисты могут интегрировать нас под любое используемое вами решение.<br><br>
Если у вас или у ваших программистов возникают сложности, звоните 8 (495) 118-27-70, мы своих в беде не бросаем :)';
$MESS['DOCUMENTATION_FAQ_ITEM4_TITLE'] = '- Зачем нужна сихронизация платежных систем?';
$MESS['DOCUMENTATION_FAQ_ITEM4_DESC'] = 'Синхранизация платежных систем необходима для расширения механизма правил расчета доставок в Salesbeat. После успешной<br>
синхранизации, все используемые на этом сайте платежные системы появятся в вашем личном кабинете Salesbeat, и они могут быть<br>
использованы в механизме правил Salesbeat. Например, можно настроить скрытие некоторых способов оплаты для какой-то<br>
географии или службы доставки или любых других условий.';
$MESS['DOCUMENTATION_FAQ_ITEM5_TITLE'] = '- У меня не получается настроить модуль. Что мне делать?';
$MESS['DOCUMENTATION_FAQ_ITEM5_DESC'] = 'Свяжитесь, пожалуйста, с нами по телефону 8 (495) 118-27-70, либо<br>
напишите на email <a href="mailto:hi@salesbeat.pro">hi@salesbeat.pro</a> или
в чат на сайте <a href="https://salesbeat.pro" target="_blank">salesbeat.pro</a>.';

$MESS['SB_SECTION_DOCUMENTATION_INFO'] = 'Справочная информация';
$MESS['DOCUMENTATION_INFO_ITEM1_TITLE'] = '- Вся правда о работе с местоположением';
$MESS['DOCUMENTATION_INFO_ITEM1_DESC'] = 'Наш модуль не нуждается в дефолтном местоположении Bitrix.<br><br>
К сожалению, даже расширенная георграфия Bitrix не позволяет считать доставку во все населенные пункты, в результате чего<br>
было принято решение написать свое свойство местоположения, которое работает через наше Api и позволяет считать доставку<br>
по полной географии.';
$MESS['DOCUMENTATION_INFO_ITEM2_TITLE'] = '- Особенности использования правил фильтрации';
$MESS['DOCUMENTATION_INFO_ITEM2_DESC'] = 'Мы настоятельно рекомендуем настраивать фильтрацию по местоположению и платежным системам в вашем личном кабинете Salesbeat,<br>
а не через стандартный функционал Bitrix. В противном случае вы можете потерять гибкость в настройке правил.';
$MESS['DOCUMENTATION_INFO_ITEM3_TITLE'] = '- Создание собственного шаблона модуля';
$MESS['DOCUMENTATION_INFO_ITEM3_DESC'] = 'Наш модуль, как и любой другой, использует компоненты для решения разного рода задач, следовательно новые шаблоны создаются в наших компонентах.<br>
Указанные ниже компоненты расположены в директории: /local/components/salesbeat/ .<br><br>
Список наших компонентов:
<ul>
    <li>sale.basket.small &mdash; компонент мини-корзины для шапки интернет магазина</li>
    <li>sale.catalog.element &mdash; компонент отображения расчетов в товарной карточке</li>
    <li>sale.delivery.widget &mdash; компонент отображения виджета доставки и карты с точками самовывоза на стандартной странице оформления заказа</li>
    <li>sale.delivery.widget.admin &mdash; компонент отображения виджета доставки на странице редактирования заказа в админке</li>
    <li>sale.location.selector &mdash; используется для расширенного определения местоположения на стандартной странице оформления заказа</li>
    <li>sale.order.ajax &mdash; используется для отображения оформления заказа через наш модуль</li>
</ul>
Собственные шаблоны будет необходимо создавать здесь: /local/components/salesbeat/Название_компонента/templates .<br>
После того, как создадите собственный шаблон, не забудьте его выбрать в настройках нашего модуля.';
$MESS['DOCUMENTATION_INFO_ITEM4_TITLE'] = '- Контактные данные';
$MESS['DOCUMENTATION_INFO_ITEM4_DESC'] = 'С нами всегда можно можно связаться:
<ul>
    <li>По телефону: 8 (495) 118-27-70</li>
    <li>Через email: <a href="mailto:hi@salesbeat.pro">hi@salesbeat.pro</a></li>
    <li>Через чат на сайте: <a href="https://salesbeat.pro" target="_blank">salesbeat.pro</a>.</li>
</ul>';

// Tab с настройками модуля
$MESS['SB_SECTION_MAIN'] = 'Системные настройки';
$MESS['SB_FIELD_API_TOKEN'] = 'API-токен';
$MESS['SB_FIELD_SECRET_TOKEN'] = 'Secret-токен';
$MESS['SB_SECTION_DEFAULT_DIMENSIONS'] = 'Габариты по умолчанию';
$MESS['SB_DEFAULT_WIDTH'] = 'Ширина';
$MESS['SB_DEFAULT_HEIGHT'] = 'Высота';
$MESS['SB_DEFAULT_LENGTH'] = 'Длина';
$MESS['SB_DEFAULT_WEIGHT'] = 'Вес';
$MESS['SB_DEFAULT_DIMENSIONS_UNIT'] = 'мм.';
$MESS['SB_DEFAULT_WEIGHT_UNIT'] = 'гр.';

// Tab c платежными системами
$MESS['SB_SECTION_PAY_SYSTEMS'] = 'Платежные системы';
$MESS['SB_FIELD_PAY_SYSTEMS_CASH'] = 'Оплата наличными';
$MESS['SB_FIELD_PAY_SYSTEMS_CARD'] = 'Оплата картой';
$MESS['SB_FIELD_PAY_SYSTEMS_ONLINE'] = 'Оплата онлайн';
$MESS['SB_SECTION_PAY_SYSTEMS_MANUAL_SYNC'] = 'Ручная синхронизация';
$MESS['SB_FIELD_PAY_SYSTEMS_SYNC'] = 'Синхронизация';
$MESS['SB_BUTTON_PAY_SYSTEMS_SYNC'] = 'Синхронизировать платежные системы';
$MESS['SB_FIELD_PAY_SYSTEMS_LAST_SYNC'] = 'Последняя синхронизация';

// Tab о получателе
$MESS['SB_SECTION_RECIPIENT'] = 'Свойства заказа о получателе';
$MESS['SB_FIELD_RECIPIENT_FULL_NAME'] = 'ФИO получателя';
$MESS['SB_FIELD_RECIPIENT_LAST_NAME'] = 'Фамилия получателя';
$MESS['SB_FIELD_RECIPIENT_FIRST_NAME'] = 'Имя получателя';
$MESS['SB_FIELD_RECIPIENT_MIDDLE_NAME'] = 'Отчество получателя';
$MESS['SB_FIELD_RECIPIENT_PHONE'] = 'Телефон';
$MESS['SB_FIELD_RECIPIENT_EMAIL'] = 'E-mail';

// Tab модуль оформления заказов
$MESS['SB_SECTION_ORDER_TESTING'] = 'Тестирование';
$MESS['SB_FIELD_ORDER_AB_TEST'] = 'АБ-тест';
$MESS['SB_FIELD_ORDER_AB_TEST_UNIT'] = '%<br>Процент пользователей который сможет воспользоваться оформлением заказа через наш модуль';
$MESS['SB_SECTION_ORDER_COP'] = 'Каталог и свойства';
$MESS['SB_FIELD_ORDER_CATALOG'] = 'Инфоблок с каталогом';
$MESS['SB_FIELD_ORDER_OFFERS'] = 'Инфоблок с торговыми предложениями';
$MESS['SB_FIELD_ORDER_PROPERTIES'] = 'Отображаемые свойства';

// Tab модуль служб доставки
$MESS['SB_SECTION_DELIVERY_TEMPLATES'] = 'Шаблоны';
$MESS['SB_FIELD_DELIVERY_TEMPLATE'] = 'Шаблон виджета доставки';

// Js
$MESS['SB_JS_SYNC_PAY_SYSTEMS_LOAD'] = 'Выполняется синхронизация';
$MESS['SB_JS_SYNC_PAY_SYSTEMS_SUCCESS'] = 'Платежные системы успешно синхронизированы';
$MESS['SB_JS_SYNC_PAY_SYSTEMS_ERROR'] = 'Ошибка синхронизации платежных систем';
$MESS['SB_RECIPIENT_EXTEND_Y'] = 'Раздельные свойства';
$MESS['SB_RECIPIENT_EXTEND_N'] = 'Одним свойством';

// Ошибки
$MESS['SB_OPTION_ERROR_TOKENS_MESSAGE'] = 'Ошибка!';
$MESS['SB_OPTION_ERROR_TOKENS_DETAILS'] = 'Один из токенов введен не верно. Данные в полях с токенами не изменены.';
?>