<?php

namespace Sprint\Editor\AdminBlocks;
use Sprint\Editor\Locale;

class IblockSections
{

    protected $params = array();

    public function __construct() {
        \CModule::IncludeModule('iblock');


        $ids = !empty($_REQUEST['section_ids']) ? $_REQUEST['section_ids'] : array();
        $ids = array_map(function ($val) {
            return intval($val);
        }, $ids);

        $ids = array_unique($ids);

        $ibid = !empty($_REQUEST['iblock_id']) ? intval($_REQUEST['iblock_id']) : 0;

        $this->params = array(
            'iblock_id' => $ibid,
            'section_ids' => $ids,
        );
    }

    public function execute() {
        $iblocks = array();
        $dbResult = \CIblock::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y'));
        while ($aItem = $dbResult->Fetch()) {
            $iblocks[] = array(
                'title' => Locale::truncateText($aItem['NAME']),
                'id' => $aItem['ID'],
            );
        }

        $elements = array();
        if ($this->params['iblock_id'] > 0 && !empty($this->params['section_ids'])){
            $dbRes = \CIBlockSection::GetList(array(
                'ID' => 'DESC'
            ),
            array(
                'IBLOCK_ID' => $this->params['iblock_id'],
                'ACTIVE' => 'Y',
                'ID' => $this->params['section_ids']
            ),
    false,
            array(
                'ID',
                'IBLOCK_ID',
                'NAME',
                'ACTIVE',
                'SORT'
            ));

            $unsorted = array();
            while ($aItem = $dbRes->Fetch()) {
                $unsorted[ $aItem['ID'] ] = array(
                    'title' => Locale::truncateText($aItem['NAME']),
                    'id' => $aItem['ID'],
                );
            }

            foreach ($this->params['section_ids'] as $id){
                if (isset($unsorted[$id])){
                    $elements[] = $unsorted[$id];
                }
            }
        }

        header('Content-type: application/json; charset=utf-8');
        echo json_encode(Locale::convertToUtf8IfNeed(array(
            'iblocks' => $iblocks,
            'elements' => $elements,
            'iblock_id' => $this->params['iblock_id'],
            'section_ids' => $this->params['section_ids'],
        )));
    }


}
