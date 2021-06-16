<?php
// Информация о модуле
$MESS['SB_MODULE_NAME'] = 'Salesbeat';
$MESS['SB_MODULE_DESCRIPTION'] = 'Оформление заказа в интернет-магазине с максимальной конверсией';

// Установка модуля
$MESS['SB_INSTALL_TITLE'] = 'Установка модуля #MODULE_NAME#';

$MESS['SB_INSTALL_STEP1_TAB'] = 'Шаг 1 из 3';
$MESS['SB_INSTALL_STEP1_TITLE'] = 'Проверка корректности токенов';
$MESS['SB_INSTALL_STEP1_NOTE'] = 'Получить API-токен и Secret-токен вы сможете перейдя по этой ссылке: 
<a href="https://app.salesbeat.pro/#/shop/info" target="_blank">https://app.salesbeat.pro/#/shop/info</a>.';
$MESS['SB_INSTALL_STEP1_API_TOKEN'] = 'Api-token';
$MESS['SB_INSTALL_STEP1_SECRET_TOKEN'] = 'Secret-token';

$MESS['SB_INSTALL_STEP2_TAB'] = 'Шаг 2 из 3';
$MESS['SB_INSTALL_STEP2_TITLE'] = 'Деактивация свойств';
$MESS['SB_INSTALL_STEP2_NOTE'] = 'Отметьте все свойства которые отвечают за: 
<ol><li>Индекс</li><li>Город</li><li>Местоположение</li><li>Адрес доставки</li></ol>
<span class="required">Отмеченные свойства будут деактивированы</span>.';

$MESS['SB_INSTALL_STEP3_TAB'] = 'Шаг 3 из 3';
$MESS['SB_INSTALL_STEP3_TITLE'] = 'Финал';
$MESS['SB_INSTALL_STEP3_NOTE'] = 'Модуль #MODULE_NAME# установлен';

// Удаление модуля
$MESS['SB_UNINSTALL'] = 'Модуль #MODULE_NAME# удален';

$MESS['SB_UNINSTALL_TITLE'] = 'Удаление модуля #MODULE_NAME#';

$MESS['SB_UNINSTALL_STEP1_TAB'] = 'Шаг 1 из 2';
$MESS['SB_UNINSTALL_STEP1_TITLE'] = 'Удаление блока';
$MESS['SB_UNINSTALL_STEP1_MESSAGE'] = 'Внимание!';
$MESS['SB_UNINSTALL_STEP1_DETAILS'] = 'Модуль #MODULE_NAME# будет удален из системы.';
$MESS['SB_UNINSTALL_STEP1_NOTE'] = 'Вы можете удалить данные модуля из базы данных, чего делать мы не рекомендуем, вдруг захотите вернуться :)';
$MESS['SB_UNINSTALL_STEP1_DELETE_TABLE'] = 'Удалить наши таблицы из базы данных';
$MESS['SB_UNINSTALL_STEP1_DELETE_CONFIGS'] = 'Удалить наши настройки модуля';

$MESS['SB_UNINSTALL_STEP2_TAB'] = 'Шаг 2 из 2';
$MESS['SB_UNINSTALL_STEP2_TITLE'] = 'Финал';
$MESS['SB_UNINSTALL_STEP2_NOTE'] = 'Модуль #MODULE_NAME# удален';

// Ошибки
$MESS['SB_INSTALL_ERROR_TITLE'] = 'Ошибка! Наш модуль использует технологию ядра D7 и новый пользовательский интервейс.';
$MESS['SB_INSTALL_ERROR_VERSION_MODULE'] = '<br>Версия установленного главного модуля: #VERSION_MODULE#. Пожалуйста, обновите свою систему минимум до версии 16.5.11.';
$MESS['SB_INSTALL_ERROR_VERSION_PHP'] = '<br>Версия используемого php интерпретатора: #VERSION_PHP#. Пожалуйста, обновите PHP интерпретатора минимум до версии 7.1.';
$MESS['SB_INSTALL_ERROR_SUPPORT'] = '<br><br>Если у вас возникли вопросы по любому из указанных пунктов, пожалуйста, напишите на нашу почту <a href="mail:#EMAIL#">#EMAIL#</a> &mdash; мы с радостью вам поможем!';
$MESS['SB_INSTALL_ERROR_MESSAGE'] = 'Ошибка!';
$MESS['SB_INSTALL_ERROR_TOKENS'] = 'Один из токенов введен не верно.';

$MESS['SB_UNINSTALL_ERROR_MESSAGE'] = 'Ошибка!';

// Тексты
$MESS['SB_INSTALL_TEXT_OK_STEP3'] = 'Спасибо, что выбрали наш модуль! Все указанные ниже ссылки будут открываться в новой вкладке :)

<h2>Настройка модуля</h2>
<ol>
<li>Для настройки модуля перейдите по этой ссылке: <a href="/bitrix/admin/settings.php?lang=ru&mid=salesbeat.sale" target="_blank">настройка модуля</a>.</li>
<li>Во вкладке «Общие настройки» заполняем поля в секции «Габариты по умолчанию».</li>
<li>Во вкладке «Платежные системы» сопоставляем платежные системы вашего магазина с типами оплаты (наличными, картой при получении или онлайн).</li>
<li>Во вкладке «О получателе» сопоставьте свойства заказа со всеми полями.</li>
<li>Нажмите на кнопку «Сохранить».</li>
</ol>

<h2>Использование модуля как оформление заказов</h2>
<ol>
<li>Для настройки модуля перейдите по этой ссылке: <a href="/bitrix/admin/settings.php?lang=ru&mid=salesbeat.sale" target="_blank">настройка модуля</a>.</li>
<li>Перейдите во вкладку «Модуль оформления заказов».</li>
<li>В секции «Тестирование» в поле АБ-тест укажите процент пользователей который сможет воспользоваться оформлением заказа через наш модуль.<br>
Если вы хотите использовать только наш модуль, напишите значение 100.</li>
<li>В секции «Каталог и свойства» выберите инфоблоки каталога и торговых предложений, а также выделите свойства товаров которые будут отображаться<br>
при оформлении заказов.</li>
<li>Нажмите на кнопку «Сохранить».</li>
</ol>

<h2>Использование модуля как расчет доставки</h2>
<ol>
<li>Для создания службы доставки перейдите по этой ссылке: <a href="/bitrix/admin/sale_delivery_service_list.php" target="_blank">создание службы доставки</a>.</li>
<li>Нажмите на кнопку «Добавить» и выберите один из способов доставки «Salesbeat с виджетом» или «Salesbeat без виджета».</li>
<li>В случае добавления способа доставки «Salesbeat без виджета», то не забудьте в настройках службы добавить профили доставки.<br>
Список профилей автоматически подгружается, исходя из выбранных служб доставки в вашем кабинете Salesbeat, управлять ими можно там же &mdash; <a href="https://app.salesbeat.pro/#/delivery_services" target="_blank">настройка тарифов</a></li>
<li>Перейдите в настройки модуля по этой ссылке: <a href="/bitrix/admin/settings.php?lang=ru&mid=salesbeat.sale" target="_blank">настройка модуля</a>.</li>
<li>Перейдите во вкладку «Модуль служб доставки».</li>
<li>В секции «Шаблоны» выберите шаблон отображения доставок исходя из созданного вами способа доставки.</li>
<li>Нажмите на кнопку «Сохранить».</li>
</ol>
<br>
<a href="https://salesbeat.pro/integrations/bitrix" target="_blank">Полная документация по установке и настройке модуля Salesbeat</a>';

$MESS['SB_INSTALL_TEXT_ERROR_STEP2'] = 'Дальнейшая установка не возможна. Вернитесь на предыдущий шаг.';
$MESS['SB_INSTALL_TEXT_ERROR_STEP3'] = 'Упс, кажется что-то пошло не так, пожалуйста пройдите установку модуля повторно.';

$MESS['SB_UNINSTALL_TEXT_OK_STEP2'] = 'Модуль успешно удален.';

// Списки
$MESS['SB_SELECT_PROPS'] = '- Отметьте свойства -';

// Кнопки
$MESS['SB_INSTALL_SUBMIT_STEP1_SAVE'] = 'Проверить правильность токенов и продолжить установку';
$MESS['SB_INSTALL_SUBMIT_STEP1_CANCEL'] = 'Отменить установку';
$MESS['SB_INSTALL_SUBMIT_STEP2_SAVE'] = 'Деактивировать отмеченные свойства и продолжить установку';
$MESS['SB_INSTALL_SUBMIT_STEP2_CANCEL'] = 'Отменить установку';
$MESS['SB_INSTALL_SUBMIT_STEP2_CANCEL_ERROR'] = 'Отменить установку';
$MESS['SB_UNISTALL_SUBMIT_STEP1_SAVE'] = 'Я, находясь в здравом уме и трезвом состоянии, подтверждаю удаление модуля #MODULE_NAME#';

$MESS['SB_SUBMIT_BACK'] = 'Вернуться в список';
?>