<?php /**
 * @var $block array
 * @var $this  SprintEditorBlocksComponent
 */
global $APPLICATION;

if ($comp = Sprint\Editor\Blocks\Component::initialize($block)) {
    $APPLICATION->IncludeComponent(
        $comp['component_name'],
        $comp['component_template'],
        $comp['component_params'],
        $this->getParent(),
        [
            'HIDE_ICONS' => 'Y',
        ]
    );
}
