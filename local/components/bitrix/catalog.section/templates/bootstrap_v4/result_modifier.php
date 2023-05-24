<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$this->__component->SetResultCacheKeys(array('NAME'));

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

