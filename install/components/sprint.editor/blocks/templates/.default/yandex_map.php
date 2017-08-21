<? /** @var $block array **/ /**@var $APPLICATION \CMain*/ ?>
<div class="sp-yandex-map">
    <? global $APPLICATION;$APPLICATION->IncludeComponent(
        "bitrix:map.yandex.view",
        "",
        Array(
            "MAP_DATA" => Sprint\Editor\Blocks\YandexMap::getMapData($block),
            "CONTROLS" => array("ZOOM", "MINIMAP", "TYPECONTROL", "SCALELINE"),
            "INIT_MAP_TYPE" => "MAP",
            "MAP_HEIGHT" => "250",
            "MAP_ID" => "",
            "MAP_WIDTH" => "600",
            "OPTIONS" => array("ENABLE_SCROLL_ZOOM", "ENABLE_DBLCLICK_ZOOM", "ENABLE_DRAGGING")
        )
    ); ?>
</div>