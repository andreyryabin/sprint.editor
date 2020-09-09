<?if ($block['url']):?>
<?global $APPLICATION;?>
<?$APPLICATION->IncludeComponent(
	"bitrix:player", 
	".default", 
	array(
		"PLAYER_TYPE" => "auto",
		"USE_PLAYLIST" => "N",
		"PATH" => $block['url'],
		"PLAYLIST_DIALOG" => "",
		"PROVIDER" => "video",
		"STREAMER" => "",
		"WIDTH" => "400",
		"HEIGHT" => "400",
		"PREVIEW" => "/local/templates/do.altspu.ru/main_logo.png",
		"FILE_TITLE" => "Вступление",
		"FILE_DURATION" => "305",
		"FILE_AUTHOR" => "Иван Иванов",
		"FILE_DATE" => "01.08.2010",
		"FILE_DESCRIPTION" => "Презентация продукта",
		"SKIN_PATH" => "/bitrix/components/bitrix/player/mediaplayer/skins",
		"SKIN" => "",
		"CONTROLBAR" => "bottom",
		"WMODE" => "transparent",
		"PLAYLIST" => "right",
		"PLAYLIST_SIZE" => "180",
		"LOGO" => "/logo.png",
		"LOGO_LINK" => "http://do.altspu.ru/",
		"LOGO_POSITION" => "bottom-left",
		"PLUGINS" => array(
			0 => "tweetit-1",
			1 => "fbit-1",
			2 => "",
		),
		"PLUGINS_TWEETIT-1" => "tweetit.link=",
		"PLUGINS_FBIT-1" => "fbit.link=",
		"ADDITIONAL_FLASHVARS" => "",
		"WMODE_WMV" => "window",
		"SHOW_CONTROLS" => "Y",
		"PLAYLIST_TYPE" => "xspf",
		"PLAYLIST_PREVIEW_WIDTH" => "64",
		"PLAYLIST_PREVIEW_HEIGHT" => "48",
		"SHOW_DIGITS" => "Y",
		"CONTROLS_BGCOLOR" => "FFFFFF",
		"CONTROLS_COLOR" => "000000",
		"CONTROLS_OVER_COLOR" => "000000",
		"SCREEN_COLOR" => "000000",
		"AUTOSTART" => "N",
		"REPEAT" => "none",
		"VOLUME" => "90",
		"MUTE" => "N",
		"HIGH_QUALITY" => "Y",
		"SHUFFLE" => "N",
		"START_ITEM" => "1",
		"ADVANCED_MODE_SETTINGS" => "Y",
		"PLAYER_ID" => "",
		"BUFFER_LENGTH" => "10",
		"DOWNLOAD_LINK_TARGET" => "_self",
		"ADDITIONAL_WMVVARS" => "",
		"ALLOW_SWF" => "Y",
		"COMPONENT_TEMPLATE" => ".default",
		"SIZE_TYPE" => "fluid",
		"AUTOSTART_ON_SCROLL" => "N",
		"START_TIME" => "0",
		"PLAYBACK_RATE" => "1",
		"TYPE" => "",
		"PRELOAD" => "N"
	),
	false
);?>
<?endif?>
