<?php
$filePath = '/modules/salesbeat.sale/services/update_basket.php';
$filePathLocal = $_SERVER['DOCUMENT_ROOT'] . '/local' . $filePath;
$filePathBitrix = $_SERVER['DOCUMENT_ROOT'] . '/bitrix' . $filePath;

if (file_exists($filePathLocal)) {
    require_once($filePathLocal);
} elseif (file_exists($filePathBitrix)) {
    require_once($filePathBitrix);
}