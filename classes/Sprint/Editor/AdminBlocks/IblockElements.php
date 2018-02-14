<?php

namespace Sprint\Editor\AdminBlocks;
use Sprint\Editor\Locale;

class IblockElements
{

    protected $params = array();
    
    public function __construct() {
        \CModule::IncludeModule('iblock');

        $name = !empty($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
        $id1 = !empty($_REQUEST['id1']) ? intval($_REQUEST['id1']) : '';
        $id2 = !empty($_REQUEST['id2']) ? trim($_REQUEST['id2']) : '';

        $ids = !empty($_REQUEST['element_ids']) ? $_REQUEST['element_ids'] : array();
        $ids = array_map(function ($val) {
            return intval($val);
        }, $ids);

        $ids = array_unique($ids);

        $ibid = !empty($_REQUEST['iblock_id']) ? intval($_REQUEST['iblock_id']) : 0;
        $page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $page = ($page >= 1) ? $page : 1;
        
        $this->params = array(
            'page' => $page,
            'limit' => 10,
            'iblock_id' => $ibid,
            'element_ids' => $ids,
            'name' => $name,
            'id1' => $id1,
            'id2' => $id2,
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

        $pageNum = 1;
        $pageCnt = 1;
        
        $source = array();
        if ($this->params['iblock_id'] > 0){

            $filter = array(
                'IBLOCK_ID' => $this->params['iblock_id'],
                'ACTIVE' => 'Y',
            );

            if (!empty($this->params['element_ids'])){
                //$filter['!ID'] = $this->params['element_ids'];
            }

            if (!empty($this->params['name'])){
                $filter['NAME'] = '%'.$this->params['name'].'%';
            }

            if (!empty($this->params['id1'])){
                $filter['>=ID'] = $this->params['id1'];
            }

            if (!empty($this->params['id2'])){
                $filter['<=ID'] = $this->params['id2'];
            }

            $dbRes = \CIBlockElement::GetList(array(
                'ID' => 'ASC'
            ), $filter, false, array(
                'nPageSize' => $this->params['limit'],
                'iNumPage' => $this->params['page']
            ), array(
                'ID', 
                'IBLOCK_ID', 
                'NAME', 
                'ACTIVE', 
                'SORT'
            ));

            $pageNum = $dbRes->NavPageNomer;
            $pageCnt = $dbRes->NavPageCount;
           
            while ($aItem = $dbRes->Fetch()) {
                $source[] = array(
                    'title' => Locale::truncateText($aItem['NAME']),
                    'id' => $aItem['ID'],
                );
            }
        }


        $elements = array();
        if ($this->params['iblock_id'] > 0 && !empty($this->params['element_ids'])){
            $dbRes = \CIBlockElement::GetList(array(
                'ID' => 'DESC'
            ), array(
                'IBLOCK_ID' => $this->params['iblock_id'],
                'ACTIVE' => 'Y',
                'ID' => $this->params['element_ids']
            ), false, false, array(
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

            foreach ($this->params['element_ids'] as $id){
                if (isset($unsorted[$id])){
                    $elements[] = $unsorted[$id];
                }
            }
        }

        header('Content-type: application/json; charset=utf-8');
        echo json_encode(Locale::convertToUtf8IfNeed(array(
            'iblocks' => $iblocks,
            'elements' => $elements,
            'source' => $source,
            'iblock_id' => $this->params['iblock_id'],
            'element_ids' => $this->params['element_ids'],
            'page_num' => $pageNum,
            'page_cnt' => $pageCnt,
            'name' => $this->params['name'],
            'id1' => $this->params['id1'],
            'id2' => $this->params['id2'],
        )));
    }
    

}