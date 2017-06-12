<?php

class SprintEditorBlocksComponent extends CBitrixComponent
{


    protected $preparedBlocks= array();
    protected $includedBlocks = 0;

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
        $value = json_decode(Sprint\Editor\Locale::convertToUtf8IfNeed($value), true);
        $value = Sprint\Editor\Locale::convertToWin1251IfNeed($value);
        $value = (json_last_error() == JSON_ERROR_NONE) ? $value : array();

        if (!empty($value) && !isset($value['layouts'])) {
            foreach ($value as $index => $block) {
                $block['layout'] = '0,0';
                $value[$index] = $block;
            }

            $value = array(
                'blocks' => $value,
                'layouts' => array(
                    array(''),
                )
            );
        }

        return $value;
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

    public function getColumnCss($column) {
        $cssClasses = explode(',', $column);

        $cssClasses = array_map(function ($cssClass) {
            $cssClass = trim($cssClass);

            if (!empty($cssClass)) {
                return 'col-' . $cssClass;
            } else {
                return '';
            }

        }, $cssClasses);

        if (!empty($cssClasses)) {
            $cssClasses = implode(' ', $cssClasses);
        } else {
            $cssClasses = '';
        }

        return $cssClasses;
    }

    protected function outJson($value) {

        $value = $this->prepareValue($value);

        $events = GetModuleEvents("sprint.editor", "OnBeforeShowComponentBlocks", true);
        foreach ($events as $aEvent) {
            ExecuteModuleEventEx($aEvent, array(&$value['blocks']));
        }

        $this->includeAssets();
        $this->includeHeader($value['blocks'], $this->arParams);

        $this->prepareBlocks($value['blocks']);

        foreach ($value['layouts'] as $layoutIndex => $columns) {
            $this->includeLayout($layoutIndex, $columns);
        }

        $this->includeFooter($this->arParams);

    }

    public function includeLayoutBlocks($layoutIndex, $columnIndex) {
        $pos = $layoutIndex . ',' . $columnIndex;
        if (isset($this->preparedBlocks[$pos])) {
            foreach ($this->preparedBlocks[$pos] as $block) {
                $this->includeBlock($block);
            }
        }
    }

    protected function includeAssets() {
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

    protected function includeLayout($layoutIndex, $columns) {
        $component = $this;

        $root = \Sprint\Editor\Module::getDocRoot();
        $path = $this->findResource('layout.php');
        if (!$path) {
            return false;
        }

        /** @noinspection PhpIncludeInspection */
        include($root . $path);
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