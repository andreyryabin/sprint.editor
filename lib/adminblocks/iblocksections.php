<?php

namespace Sprint\Editor\AdminBlocks;

use CIblock;
use CIBlockSection;
use CModule;
use Sprint\Editor\Locale;

class IblockSections
{
    protected $params = [];

    public function __construct()
    {
        CModule::IncludeModule('iblock');

        $ids = !empty($_REQUEST['section_ids']) ? $_REQUEST['section_ids'] : [];
        $ids = array_map(
            function ($val) {
                return intval($val);
            }, $ids
        );

        $ids = array_unique($ids);

        $ibid = !empty($_REQUEST['iblock_id']) ? intval($_REQUEST['iblock_id']) : 0;

        $this->params = [
            'iblock_id'   => $ibid,
            'section_ids' => $ids,
        ];
    }

    public function execute()
    {
        $iblocks = [];
        $dbResult = CIblock::GetList(['SORT' => 'ASC'], ['ACTIVE' => 'Y']);
        while ($aItem = $dbResult->Fetch()) {
            $iblocks[] = [
                'title' => Locale::truncateText($aItem['NAME']),
                'id'    => $aItem['ID'],
            ];
        }

        $elements = [];
        if ($this->params['iblock_id'] > 0 && !empty($this->params['section_ids'])) {
            $dbRes = CIBlockSection::GetList(
                [
                    'ID' => 'DESC',
                ],
                [
                    'IBLOCK_ID' => $this->params['iblock_id'],
                    'ACTIVE'    => 'Y',
                    'ID'        => $this->params['section_ids'],
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

            foreach ($this->params['section_ids'] as $id) {
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
                    'iblock_id'   => $this->params['iblock_id'],
                    'section_ids' => $this->params['section_ids'],
                ]
            )
        );
    }
}
