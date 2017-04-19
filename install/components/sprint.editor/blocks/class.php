<?php

class SprintEditorBlocksComponent extends CBitrixComponent
{

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
            return $this->outJson($this->arParams['~JSON']);
        }

        if (!empty($this->arParams['IBLOCK_ID']) && !empty($this->arParams['ELEMENT_ID'])) {
            return $this->outIblockElement();
        }

        if (!empty($this->arParams['IBLOCK_ID']) && !empty($this->arParams['SECTION_ID'])) {
            return $this->outIblockSection();
        }

        return 0;
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
            return 0;
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

        $cntblocks = 0;
        foreach ($aPropertyCodes as $propertyCode) {
            if (!empty($aItem[$propertyCode . '_VALUE'])) {
                $cntblocks += $this->outJson($aItem[$propertyCode . '_VALUE']);
            }
        }

        return $cntblocks;
    }

    protected function outIblockSection()
    {
        \CModule::IncludeModule("iblock");

        $aPropertyCodes = array();
        if (empty($this->arParams['PROPERTY_CODE'])) {
            //todo: получить все пользовательские поля с редактором если явно не указано
            return 0;
        } else {
            $aPropertyCodes[] = $this->arParams['PROPERTY_CODE'];
        }

        if (empty($aPropertyCodes)) {
            return 0;
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

        $cntblocks = 0;
        foreach ($aPropertyCodes as $propertyCode) {
            if (!empty($aItem[$propertyCode])) {
                $cntblocks += $this->outJson($aItem[$propertyCode]);
            }
        }

        return $cntblocks;
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

        $cntblocks = 0;
        foreach ($blocks as $block) {
            if ($this->includeBlock($block)) {
                $cntblocks++;
            }
        }

        $this->includePartial('_footer', $blocks, $this->arParams);
        return $cntblocks;

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