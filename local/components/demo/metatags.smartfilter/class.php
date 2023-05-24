<?php

use Bitrix\Main\Config\Configuration;
use Bitrix\Landing\Source\Seo;

class MetaSmartFilter extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        if ($this->StartResultCache()) {

            $this->arResult["SECTION_FIELDS"]["NAME"] = $arParams["SECTION_FIELDS"]["NAME"];
            $this->arResult["TITLE"] = $arParams["TITLE"];
            $this->arResult["CHECKED_ITEMS"] = $arParams["CHECKED_ITEMS"];
            $this->arResult["MAX_PRICE"] = $arParams["MAX_PRICE"];
            $this->arResult["MIN_PRICE"] = $arParams["MIN_PRICE"];

            $this->IncludeComponentTemplate();
        }
    }

    public function getFilter()
    {

        $filterResult = Configuration::getValue('smart_filter_template');

        if (!empty($this->arResult["CHECKED_ITEMS"])) {
            $properties = '';
            foreach ($this->arResult["CHECKED_ITEMS"] as $prop) {
                $properties .= ' ' . $prop['NAME'] . ' - ';
                foreach ($prop["CHECKED_VALUES"] as $value) {
                    $properties .= " " . $value;
                }
                $properties .= '; ';
            }
            $filterResult = str_replace('{smartfilter.params.name} - {smartfilter.params.value}',
                $properties, $filterResult);
        } else {
            $filterResult = str_replace('{smartfilter.params.name} - {smartfilter.params.value}',
                '', $filterResult);
        }

        if (!empty($this->arResult["MIN_PRICE"] && $this->arResult["MAX_PRICE"])) {
            $priceValue = 'от ' . $this->arResult["MIN_PRICE"] .
                ' до ' . $this->arResult["MAX_PRICE"];
            $filterResult = str_replace('{smartfilter.price}', $priceValue, $filterResult);
        } else {
            $filterResult = str_replace('Цена - {smartfilter.price} ', '', $filterResult);
        }

        if(!empty($this->arResult["SECTION_FIELDS"]["NAME"])) {
            $filterResult = str_replace('{section.name}', $this->arResult["SECTION_FIELDS"]["NAME"], $filterResult);
        } else {
            $filterResult = str_replace('{section.name}', '', $filterResult);
        }

        if(!empty($this->arResult["TITLE"])) {
            $filterResult = str_replace('{h1}', $this->arResult["TITLE"], $filterResult);
        } else {
            $filterResult = str_replace('{h1}', '', $filterResult);
        }

        return $filterResult;
    }

}