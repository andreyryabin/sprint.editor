<?php

class SprintEditorBlocksComponent extends CBitrixComponent
{

    protected $layouts = array();
    protected $layoutIndex = 0;
    protected $blocksIncluded = 0;

    public function executeComponent()
    {
        if (!\CModule::IncludeModule('sprint.editor')) {
            return 0;
        }

        $this->arParams['TEMPLATE_NAME'] = $this->getTemplateName();
        if (empty($this->arParams['TEMPLATE_NAME'])) {
            $this->arParams['TEMPLATE_NAME'] = '.default';
        }

        if (!empty($this->arParams['JSON'])) {
            $this->outJson($this->arParams['~JSON']);

        }elseif (!empty($this->arParams['IBLOCK_ID']) && !empty($this->arParams['ELEMENT_ID'])) {
            $this->outIblockElement();

        }elseif (!empty($this->arParams['IBLOCK_ID']) && !empty($this->arParams['SECTION_ID'])) {
            $this->outIblockSection();
        }

        return $this->blocksIncluded;
    }


    protected function outIblockElement()
    {
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

    protected function outIblockSection()
    {
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

    protected function outJson($blocks)
    {
        $blocks = json_decode(Sprint\Editor\Locale::convertToUtf8IfNeed($blocks), true);
        $blocks = Sprint\Editor\Locale::convertToWin1251IfNeed($blocks);
        $blocks = (json_last_error() == JSON_ERROR_NONE) ? $blocks : array();

        $events = GetModuleEvents("sprint.editor", "OnBeforeShowComponentBlocks", true);
        foreach ($events as $aEvent) {
            ExecuteModuleEventEx($aEvent, array(&$blocks));
        }

        $this->includeAssets();
        $this->includePartial('_header', $blocks, $this->arParams);

        

        $this->layouts = array();
        $this->layoutIndex = 0;
        $lindex = -1;
        $lname = '';

        foreach ($blocks as $block) {

            $layout = !empty($block['layout']) ? $block['layout'] : array();

            if (empty($layout['name'])){
                $layout['name'] = 'A0B0';
            }

            if (empty($layout['type'])){
                $layout['type'] = 1;
            }

            if (empty($layout['index'])){
                $layout['index'] = 1;
            }

            if ($lname != $layout['name']){
                $this->layouts[] = array();
                $lindex++;
                $lname = $layout['name'];
            }
        
            $colName = 'col'.$layout['index'];

            if (empty($this->layouts[$lindex][$colName])){
                $this->layouts[$lindex][$colName] = array();
            }

            $this->layouts[$lindex][$colName][] = $block;
        }

        foreach ($this->layouts as $columns){
            $layoutName = 'layout'.count($columns);
            if ($this->includeLayout($layoutName)){
                $this->layoutIndex++;
            }
        }

        $this->includePartial('_footer', $blocks, $this->arParams);

    }

    public function includeLayoutBlocks($type){
        $blocks = array();

        if (isset($this->layouts[$this->layoutIndex])){
            if (isset($this->layouts[$this->layoutIndex][$type])){
                $blocks = $this->layouts[$this->layoutIndex][$type];
            }
        }

        foreach ($blocks as $block) {
            if ($this->includeBlock($block)) {
                $this->blocksIncluded++;
            }
        }

    }

    protected function includeAssets()
    {
        global $APPLICATION;

        $path = $this->findResource('_style.css');
        if ($path) {
            $APPLICATION->SetAdditionalCSS($path);
        }

        $path = $this->findResource('_script.js');
        if ($path) {
            $APPLICATION->AddHeadScript($path);
        }

    }

    protected function includeBlock($block)
    {
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
        return true;

    }

    protected function includeLayout($layoutName)
    {
        $component = $this;

        $root = \Sprint\Editor\Module::getDocRoot();
        $path = $this->findResource($layoutName . '.php');
        if (!$path) {
            return false;
        }

        /** @noinspection PhpIncludeInspection */
        include($root . $path);
        return true;
    }

    protected function includePartial($partialName, &$blocks, $arParams)
    {
        $root = \Sprint\Editor\Module::getDocRoot();
        $path = $this->findResource($partialName . '.php');
        if (!$path) {
            return false;
        }

        /** @noinspection PhpIncludeInspection */
        include($root . $path);
        return true;
    }

    protected function findResource($resName)
    {
        $templateName = $this->arParams['TEMPLATE_NAME'];
        $root = \Sprint\Editor\Module::getDocRoot();

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
                return $path;
            }
        }

        return false;
    }

}