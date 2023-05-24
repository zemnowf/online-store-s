<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $templateData */
/** @var @global CMain $APPLICATION */

CJSCore::Init(array('fx', 'popup'));

use Lib\FilterStorage;

echo "help<pre>";
var_dump($arResult["CUR_ITEMS"]);
echo "</pre>";
FilterStorage::set("items", $arResult["ITEMS"]);
FilterStorage::set("checked_items", $arResult["CUR_ITEMS"]);
FilterStorage::set("max_price", $arResult["MAX_PRICE"]);
FilterStorage::set("min_price", $arResult["MIN_PRICE"]);
FilterStorage::set("price", $arResult["PRICE"]);