<?php

namespace Sprint\Editor\AdminBlocks;

use CIblock;
use CIBlockSection;
use CModule;
use Sprint\Editor\Locale;

class IblockSections
{
    private $enabledIblocks;
    private $selectedSections;
    private $iblockId;

    public function __construct()
    {
        CModule::IncludeModule('iblock');

        $this->enabledIblocks = !empty($_REQUEST['enabled_iblocks']) ? $_REQUEST['enabled_iblocks'] : [];
        $this->enabledIblocks = is_array($this->enabledIblocks) ? $this->enabledIblocks : [];

        $this->selectedSections = !empty($_REQUEST['section_ids']) ? $_REQUEST['section_ids'] : [];
        $this->selectedSections = is_array($this->selectedSections) ? $this->selectedSections : [];
        $this->selectedSections = array_map('intval', $this->selectedSections);
        $this->selectedSections = array_unique($this->selectedSections);

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
        if ($this->iblockId > 0 && !empty($this->selectedSections)) {
            $dbRes = CIBlockSection::GetList(
                [
                    'ID' => 'DESC',
                ],
                [
                    'IBLOCK_ID' => $this->iblockId,
                    'ACTIVE'    => 'Y',
                    'ID'        => $this->selectedSections,
                ],
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

            foreach ($this->selectedSections as $id) {
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
                    'section_ids' => $this->selectedSections,
                ]
            )
        );
    }
}
