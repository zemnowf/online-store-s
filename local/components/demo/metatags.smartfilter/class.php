<?php

use Bitrix\Main\Config\Configuration;
use Bitrix\Landing\Source\Seo;

class MetaSmartFilter extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        if ($this->StartResultCache()) {

            $this->arResult["UF_SECTION_NAME"] = $arParams["UF_SECTION_NAME"];
            $this->arResult["UF_TITLE"] = $arParams["UF_TITLE"];

            foreach ($arParams["ITEMS"] as $item) {
                $valueChecked = false;
                foreach ($item["VALUES"] as $value) {
                    if ($value["CHECKED"]) {
                        $valueChecked = true;
                    }
                }
                if ($item["NAME"] == "Розничная цена") {

                    $this->arResult["PRICE"] = $item;

                    if ($item["VALUES"]["MIN"]["HTML_VALUE"]) {
                        $this->arResult["MIN"] = $item["VALUES"]["MIN"]["HTML_VALUE"];
                    } else {
                        $this->arResult["MIN"] = $item["VALUES"]["MIN"]["VALUE"];
                    }

                    if ($item["VALUES"]["MAX"]["HTML_VALUE"]) {
                        $this->arResult["MAX"] = $item["VALUES"]["MAX"]["HTML_VALUE"];
                    } else {
                        $this->arResult["MAX"] = $item["VALUES"]["MAX"]["VALUE"];
                    }

                }
                if ($valueChecked) {
                    $this->arResult["ITEMS"][] = $item;
                }
            }

            $this->IncludeComponentTemplate();
        }
    }

    public function getFilter()
    {

        $filterResult = Configuration::getValue('smart_filter_template');

        if (!empty($this->arResult["ITEMS"])) {
            $properties = '';
            foreach ($this->arResult["ITEMS"] as $prop) {
                $properties .= ' ' . $prop['NAME'] . ' - ';
                foreach ($prop["VALUES"] as $value) {
                    $properties .= " " . $value["VALUE"];
                }
                $properties .= '; ';
            }
            $filterResult = str_replace('{smartfilter.params.name} - {smartfilter.params.value}',
                $properties, $filterResult);
        } else {
            $filterResult = str_replace('{smartfilter.params.name} - {smartfilter.params.value}',
                '', $filterResult);
        }

        if (!empty($this->arResult["PRICE"])) {
            $priceValue = 'от ' . $this->arResult["MIN"] .
                ' до ' . $this->arResult["MAX"];
            $filterResult = str_replace('{smartfilter.price}', $priceValue, $filterResult);
        } else {
            $filterResult = str_replace('Цена - {smartfilter.price} ', '', $filterResult);
        }

        if(!empty($this->arResult["UF_SECTION_NAME"])) {
            $filterResult = str_replace('{section.name}', $this->arResult["UF_SECTION_NAME"], $filterResult);
        } else {
            $filterResult = str_replace('{section.name}', '', $filterResult);
        }

        if(!empty($this->arResult["UF_TITLE"])) {
            $filterResult = str_replace('{h1}', $this->arResult["UF_TITLE"], $filterResult);
        } else {
            $filterResult = str_replace('{h1}', '', $filterResult);
        }

        return $filterResult;
    }

}