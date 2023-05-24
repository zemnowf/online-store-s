<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($arParams["TEMPLATE_THEME"]) && !empty($arParams["TEMPLATE_THEME"]))
{
	$arAvailableThemes = array();
	$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
	if (is_dir($dir) && $directory = opendir($dir))
	{
		while (($file = readdir($directory)) !== false)
		{
			if ($file != "." && $file != ".." && is_dir($dir.$file))
				$arAvailableThemes[] = $file;
		}
		closedir($directory);
	}

	if ($arParams["TEMPLATE_THEME"] == "site")
	{
		$solution = COption::GetOptionString("main", "wizard_solution", "", SITE_ID);
		if ($solution == "eshop")
		{
			$templateId = COption::GetOptionString("main", "wizard_template_id", "eshop_bootstrap", SITE_ID);
			$templateId = (preg_match("/^eshop_adapt/", $templateId)) ? "eshop_adapt" : $templateId;
			$theme = COption::GetOptionString("main", "wizard_".$templateId."_theme_id", "blue", SITE_ID);
			$arParams["TEMPLATE_THEME"] = (in_array($theme, $arAvailableThemes)) ? $theme : "blue";
		}
	}
	else
	{
		$arParams["TEMPLATE_THEME"] = (in_array($arParams["TEMPLATE_THEME"], $arAvailableThemes)) ? $arParams["TEMPLATE_THEME"] : "blue";
	}
}
else
{
	$arParams["TEMPLATE_THEME"] = "blue";
}

$arParams["FILTER_VIEW_MODE"] = (isset($arParams["FILTER_VIEW_MODE"]) && toUpper($arParams["FILTER_VIEW_MODE"]) == "HORIZONTAL") ? "HORIZONTAL" : "VERTICAL";
$arParams["POPUP_POSITION"] = (isset($arParams["POPUP_POSITION"]) && in_array($arParams["POPUP_POSITION"], array("left", "right"))) ? $arParams["POPUP_POSITION"] : "left";


foreach ($arResult["ITEMS"] as $item) {
    $valueChecked = false;
    foreach ($item["VALUES"] as $value) {
        if ($value["CHECKED"]) {
            $valueChecked = true;
        }
    }
    if ($item["NAME"] == "Розничная цена") {

        $arResult["PRICE"] = $item;

        if ($item["VALUES"]["MIN"]["HTML_VALUE"]) {
            $arResult["MIN_PRICE"] = $item["VALUES"]["MIN"]["HTML_VALUE"];
        } else {
            $arResult["MIN_PRICE"] = $item["VALUES"]["MIN"]["VALUE"];
        }

        if ($item["VALUES"]["MAX"]["HTML_VALUE"]) {
            $arResult["MAX_PRICE"] = $item["VALUES"]["MAX"]["HTML_VALUE"];
        } else {
            $arResult["MAX_PRICE"] = $item["VALUES"]["MAX"]["VALUE"];
        }

    }
    if ($valueChecked) {
        $checkedValues = array();
        foreach ($item["VALUES"] as $value) {
            if ($value["CHECKED"]) {
                $checkedValues[] = $value["VALUE"];
            }
        }
        $newItem = array(
            "NAME" => $item["NAME"],
            "CHECKED_VALUES" => $checkedValues
        );
        $arResult["CUR_ITEMS"][] = $newItem;
    }
}

echo "price<pre>";
var_dump($arResult["CUR_ITEMS"]);
echo "</pre>";
$this->__component->setResultCacheKeys(array("ITEMS", "CUR_ITEMS", "MAX_PRICE", "MIN_PRICE", "PRICE"));



