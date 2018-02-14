<?php

use Bitrix\Main\Page\Asset;

class SprintEditorBlocksComponent extends CBitrixComponent
{
    protected $preparedBlocks = array();
    protected $includedBlocks = 0;
    protected $layoutIndex = 0;
    protected $resourcesCache = array();

    public function onPrepareComponentParams($arParams) {
        $arParams['USE_JQUERY'] = (!empty($arParams['USE_JQUERY']) && $arParams['USE_JQUERY'] == 'Y') ? 'Y' : 'N';
        $arParams['USE_FANCYBOX'] = (!empty($arParams['USE_FANCYBOX']) && $arParams['USE_FANCYBOX'] == 'Y') ? 'Y' : 'N';
        return $arParams;
    }

    public function executeComponent() {
        if (!\CModule::IncludeModule('sprint.editor')) {
            return 0;
        }

        $this->arParams['TEMPLATE_NAME'] = $this->getTemplateName();
        if (empty($this->arParams['TEMPLATE_NAME'])) {
            $this->arParams['TEMPLATE_NAME'] = '.default';
        }

        if (!empty($this->arParams['JSON'])) {
            $this->outJson($this->arParams['~JSON']);

        } elseif (!empty($this->arParams['IBLOCK_ID']) && !empty($this->arParams['ELEMENT_ID'])) {
            $this->outIblockElement();

        } elseif (!empty($this->arParams['IBLOCK_ID']) && !empty($this->arParams['SECTION_ID'])) {
            $this->outIblockSection();
        }

        return $this->includedBlocks;
    }


    protected function outIblockElement() {
        \CModule::IncludeModule("iblock");

        $aPropertyCodes = array();
        if (empty($this->arParams['PROPERTY_CODE'])) {
            $dbRes = \CIBlockProperty::GetList(array('SORT' => 'ASC'), array(
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                'CHECK_PERMISSIONS' => 'N',
                'USER_TYPE' => 'sprint_editor',
            ));
            while ($aProp = $dbRes->Fetch()) {
                $aPropertyCodes[] = 'PROPERTY_' . $aProp['CODE'];
            }

        } else {
            $aPropertyCodes[] = 'PROPERTY_' . $this->arParams['PROPERTY_CODE'];
        }

        if (empty($aPropertyCodes)) {
            return false;
        }

        $aSelect = array_merge(array(
            'ID',
            'IBLOCK_ID',
            'NAME',
            'CODE',
            'SORT',
        ), $aPropertyCodes);

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $aItem = \CIBlockElement::GetList(array('SORT' => 'ASC'), array(
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ID' => $this->arParams['ELEMENT_ID'],
        ), false, array('nTopCount' => 1), $aSelect)->Fetch();


        foreach ($aPropertyCodes as $propertyCode) {
            if (!empty($aItem[$propertyCode . '_VALUE'])) {
                $this->outJson($aItem[$propertyCode . '_VALUE']);
            }
        }

        return true;
    }

    protected function outIblockSection() {
        \CModule::IncludeModule("iblock");

        $aPropertyCodes = array();
        if (empty($this->arParams['PROPERTY_CODE'])) {
            //todo: получить все пользовательские поля с редактором если явно не указано
            return false;
        } else {
            $aPropertyCodes[] = $this->arParams['PROPERTY_CODE'];
        }

        if (empty($aPropertyCodes)) {
            return false;
        }

        $aSelect = array_merge(array(
            'ID',
            'IBLOCK_ID',
            'NAME',
            'CODE',
            'SORT',
        ), $aPropertyCodes);

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $aItem = \CIBlockSection::GetList(array('SORT' => 'ASC'), array(
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ID' => $this->arParams['SECTION_ID'],
        ), false, $aSelect, array('nTopCount' => 1))->Fetch();

        foreach ($aPropertyCodes as $propertyCode) {
            if (!empty($aItem[$propertyCode])) {
                $this->outJson($aItem[$propertyCode]);
            }
        }

        return true;
    }

    protected function prepareValue($value) {
        $value = str_replace("\xe2\x80\xa8", '\\u2028', $value);
        $value = str_replace("\xe2\x80\xa9", '\\u2029', $value);

        $value = json_decode(Sprint\Editor\Locale::convertToUtf8IfNeed($value), true);
        $value = Sprint\Editor\Locale::convertToWin1251IfNeed($value);
        $value = (json_last_error() == JSON_ERROR_NONE) ? $value : array();
        return Sprint\Editor\AdminEditor::prepareValueArray($value);
    }

    protected function prepareBlocks($blocks) {
        $this->preparedBlocks = array();

        foreach ($blocks as $block) {
            $pos = $block['layout'];

            if (!isset($this->preparedBlocks[$pos])) {
                $this->preparedBlocks[$pos] = array();
            }

            $this->preparedBlocks[$pos][] = $block;
        }
    }

    protected function outJson($value) {

        $value = $this->prepareValue($value);

        $events = GetModuleEvents("sprint.editor", "OnBeforeShowComponentBlocks", true);
        foreach ($events as $aEvent) {
            ExecuteModuleEventEx($aEvent, array(&$value['blocks']));
        }

        $this->includeHeader($value['blocks'], $this->arParams);
        $this->prepareBlocks($value['blocks']);

        $this->layoutIndex = 0;
        foreach ($value['layouts'] as $columns) {
            $this->includeLayout($columns);
        }

        $this->includeFooter($this->arParams);

    }

    protected function includeLayoutBlocks($columnIndex) {
        $pos = $this->layoutIndex . ',' . $columnIndex;
        if (isset($this->preparedBlocks[$pos])) {
            foreach ($this->preparedBlocks[$pos] as $block) {
                $this->includeBlock($block);
            }
        }
    }

    protected function registerJs($path) {
        if (empty($path)) {
            return false;
        }

        Asset::getInstance()->addJs($path);
        if ($this->getParent()) {
            $this->getParent()->addChildJS($path);
        }
    }

    protected function registerCss($path) {
        if (empty($path)) {
            return false;
        }

        Asset::getInstance()->addCss($path);
        if ($this->getParent()) {
            $this->getParent()->addChildCSS($path);
        }
    }

    protected function includeBlock($block) {
        $root = \Sprint\Editor\Module::getDocRoot();

        $path = $this->findResource($block['name'] . '.php');

        if (!$path) {
            $path = $this->findResource('dump.php');
        }

        if (!$path) {
            return false;
        }

        /** @noinspection PhpIncludeInspection */
        include($root . $path);

        $this->includedBlocks++;

        return true;

    }

    protected function includeLayout($columns) {
        $root = \Sprint\Editor\Module::getDocRoot();
        $path = $this->findResource('_layout.php');
        if (!$path) {
            return false;
        }
        /** @noinspection PhpIncludeInspection */
        include($root . $path);

        $this->layoutIndex++;

        return true;
    }

    protected function includeHeader(&$blocks, $arParams) {
        $root = \Sprint\Editor\Module::getDocRoot();
        $path = $this->findResource('_header.php');
        if (!$path) {
            return false;
        }

        /** @noinspection PhpIncludeInspection */
        include($root . $path);
        return true;
    }

    protected function includeFooter($arParams) {
        $root = \Sprint\Editor\Module::getDocRoot();
        $path = $this->findResource('_footer.php');
        if (!$path) {
            return false;
        }

        /** @noinspection PhpIncludeInspection */
        include($root . $path);
        return true;
    }

    protected function findResource($resName) {
        $templateName = $this->arParams['TEMPLATE_NAME'];
        $root = \Sprint\Editor\Module::getDocRoot();

        $uniq = $templateName . $resName;

        if (isset($this->resourcesCache[$uniq])) {
            return $this->resourcesCache[$uniq];
        }

        $paths = array(

            SITE_TEMPLATE_PATH . '/components/sprint.editor/blocks/' . $templateName . '/' . $resName,
            SITE_TEMPLATE_PATH . '/components/sprint.editor/blocks/.default/' . $resName,

            '/local/templates/.default/components/sprint.editor/blocks/' . $templateName . '/' . $resName,
            '/bitrix/templates/.default/components/sprint.editor/blocks/' . $templateName . '/' . $resName,

            '/local/templates/.default/components/sprint.editor/blocks/.default/' . $resName,
            '/bitrix/templates/.default/components/sprint.editor/blocks/.default/' . $resName,

            '/local/components/sprint.editor/blocks/templates/' . $templateName . '/' . $resName,
            '/bitrix/components/sprint.editor/blocks/templates/' . $templateName . '/' . $resName,

            '/local/components/sprint.editor/blocks/templates/.default/' . $resName,
            '/bitrix/components/sprint.editor/blocks/templates/.default/' . $resName,
        );

        foreach ($paths as $path) {
            if (is_file($root . $path)) {
                $this->resourcesCache[$uniq] = $path;
                return $path;
            }
        }

        return false;
    }

}