<?php

namespace Sprint\Editor\AdminBlocks;

use CIblock;
use CIBlockElement;
use CModule;
use Sprint\Editor\Locale;

class IblockElements
{
    private $enabledIblocks;
    private $selectedElements;
    private $iblockId;

    public function __construct()
    {
        CModule::IncludeModule('iblock');

        $this->enabledIblocks = !empty($_REQUEST['enabled_iblocks']) ? $_REQUEST['enabled_iblocks'] : [];
        $this->enabledIblocks = is_array($this->enabledIblocks) ? $this->enabledIblocks : [];

        $this->selectedElements = !empty($_REQUEST['element_ids']) ? $_REQUEST['element_ids'] : [];
        $this->selectedElements = is_array($this->selectedElements) ? $this->selectedElements : [];
        $this->selectedElements = array_map('intval', $this->selectedElements);
        $this->selectedElements = array_unique($this->selectedElements);

        $this->iblockId = !empty($_REQUEST['iblock_id']) ? intval($_REQUEST['iblock_id']) : 0;
    }

    public function execute()
    {
        $iblocksFilter = ['ACTIVE' => 'Y'];
        if (!empty($this->enabledIblocks)) {
            $iblocksFilter['=ID'] = $this->enabledIblocks;
        }

        $dbResult = CIblock::GetList(
            [
                'SORT' => 'ASC',
            ],
            $iblocksFilter
        );

        $iblocks = [];
        while ($aItem = $dbResult->Fetch()) {
            $iblocks[] = [
                'title' => Locale::truncateText($aItem['NAME']),
                'id'    => $aItem['ID'],
            ];
        }

        $elements = [];
        if ($this->iblockId > 0 && !empty($this->selectedElements)) {
            $dbRes = CIBlockElement::GetList(
                [
                    'ID' => 'DESC',
                ],
                [
                    'IBLOCK_ID' => $this->iblockId,
                    'ACTIVE'    => 'Y',
                    'ID'        => $this->selectedElements,
                ],
                false,
                false,
                [
                    'ID',
                    'IBLOCK_ID',
                    'NAME',
                    'ACTIVE',
                    'SORT',
                ]
            );

            $unsorted = [];
            while ($aItem = $dbRes->Fetch()) {
                $unsorted[$aItem['ID']] = [
                    'title' => Locale::truncateText($aItem['NAME']),
                    'id'    => $aItem['ID'],
                ];
            }

            foreach ($this->selectedElements as $id) {
                if (isset($unsorted[$id])) {
                    $elements[] = $unsorted[$id];
                }
            }
        }

        header('Content-type: application/json; charset=utf-8');
        echo json_encode(
            Locale::convertToUtf8IfNeed(
                [
                    'iblocks'     => $iblocks,
                    'elements'    => $elements,
                    'iblock_id'   => $this->iblockId,
                    'element_ids' => $this->selectedElements,
                ]
            )
        );
    }
}
