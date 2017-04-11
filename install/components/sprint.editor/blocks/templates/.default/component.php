<? /** @var $block array */
global $APPLICATION;
if (!empty($block['component_name'])) {

    $block = Sprint\Editor\Blocks\Component::initializeParams($block);

    $APPLICATION->IncludeComponent(
        $block['component_name'],
        $block['component_template'],
        $block['component_params'],
        false,
        array(
            'HIDE_ICONS' => 'Y'
        )
    );
}?>