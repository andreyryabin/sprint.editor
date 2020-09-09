<?
if (!$block[mp3])return false;
global $APPLICATION;
$APPLICATION->IncludeComponent(
	"bbutterfly:playermp3",
	"",
	Array(
		"FILE" => $block[mp3],
		"JQUERY" => "N",
		"JQUERYUIDRAGGABLE" => "Y"
	),
false
);?> 