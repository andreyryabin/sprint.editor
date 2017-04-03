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

    protected function includeBlock($block)
    {
        $path = $this->findBlockPath($block['name']);
        if ($path) {
            /** @noinspection PhpIncludeInspection */
            include($path);
            return true;
        }
        return false;
    }

    protected function includePartial($partialName, &$blocks, $arParams)
    {
        $path = $this->findBlockPath($partialName);
        if ($path) {
            /** @noinspection PhpIncludeInspection */
            include($path);
            return true;
        }
        return false;
    }

    protected function findBlockPath($blockName)
    {
        $templateName = $this->arParams['TEMPLATE_NAME'];
        $root = \Sprint\Editor\Module::getDocRoot();

        $paths = array(

            $root . SITE_TEMPLATE_PATH . '/components/sprint.editor/blocks/' . $templateName . '/' . $blockName . '.php',
            $root . SITE_TEMPLATE_PATH . '/components/sprint.editor/blocks/.default/' . $blockName . '.php',

            $root . '/local/templates/.default/components/sprint.editor/blocks/' . $templateName . '/' . $blockName . '.php',
            $root . '/bitrix/templates/.default/components/sprint.editor/blocks/' . $templateName . '/' . $blockName . '.php',

            $root . '/local/templates/.default/components/sprint.editor/blocks/.default/' . $blockName . '.php',
            $root . '/bitrix/templates/.default/components/sprint.editor/blocks/.default/' . $blockName . '.php',

            $root . '/local/components/sprint.editor/blocks/templates/' . $templateName . '/' . $blockName . '.php',
            $root . '/bitrix/components/sprint.editor/blocks/templates/' . $templateName . '/' . $blockName . '.php',

            $root . '/local/components/sprint.editor/blocks/templates/.default/' . $blockName . '.php',
            $root . '/bitrix/components/sprint.editor/blocks/templates/.default/' . $blockName . '.php',

            $root . '/local/components/sprint.editor/blocks/templates/.default/dump.php',
            $root . '/bitrix/components/sprint.editor/blocks/templates/.default/dump.php',
        );

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return false;
    }

}