<?php
/**
 * @var $blockName
 * @var $areas
 */
?>
sprint_editor.registerBlock('<?=$blockName?>', function ($, $el, data) {
    var areas = <?=json_encode($areas, JSON_PRETTY_PRINT)?>;

    this.getData = function () {
        return data;
    };

    this.collectData = function () {
        return data;
    };

    this.getAreas = function () {
        return areas;
    };
});
