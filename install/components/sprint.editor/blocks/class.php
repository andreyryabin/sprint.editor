<?php

class SprintEditorBlocksComponent extends CBitrixComponent
{

    public function executeComponent() {
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

        return 0;
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

    protected function outJson($blocks) {
        $blocks = json_decode(Sprint\Editor\Locale::convertToUtf8IfNeed($blocks), true);
        $blocks = Sprint\Editor\Locale::convertToWin1251IfNeed($blocks);
        $blocks = (json_last_error() == JSON_ERROR_NONE) ? $blocks : array();

        $cntblocks = 0;
        foreach ($blocks as $block) {
            if ($this->includeBlock($block, $this->arParams['TEMPLATE_NAME'])) {
                $cntblocks++;
            }
        }

        return $cntblocks;

    }

    protected function includeBlock($block, $templateName) {
        $path = $this->findBlockPath($block['name'], $templateName);
        if ($path) {
            /** @noinspection PhpIncludeInspection */
            include($path);
            return true;
        }
        return false;
    }

    protected function findBlockPath($blockName, $templateName) {
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