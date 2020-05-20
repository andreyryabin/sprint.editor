<?php

namespace Sprint\Editor\AdminBlocks;

use Sprint\Editor\Locale;
use Sprint\Editor\Tools\Medialib;

class MedialibElements
{

    protected $params = array();

    public function __construct() {
        $ids = !empty($_REQUEST['element_ids']) ? $_REQUEST['element_ids'] : array();
        $ids = array_map(function ($val) {
            return intval($val);
        }, $ids);

        $ids = array_unique($ids);

        $ibid = !empty($_REQUEST['collection_id']) ? intval($_REQUEST['collection_id']) : 0;
        $page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        $page = ($page >= 1) ? $page : 1;

        $this->params = array(
            'page' => $page,
            'limit' => 10,
            'collection_id' => $ibid,
            'element_ids' => $ids
        );
    }

    public function execute() {
        $collections = array();

        $dbresult = Medialib::GetCollections(array(
            'type' => 'image'
        ));

        foreach ($dbresult as $aItem) {
            $collections[] = array(
                'title' => Locale::truncateText($aItem['NAME']),
                'id' => $aItem['ID'],
            );
        }

        $pageNum = 1;
        $pageCnt = 1;

        $source = array();
        if ($this->params['collection_id'] > 0) {

            $dbresult = Medialib::GetElements(array(
                'collection_id' => $this->params['collection_id'],
            ),array(
                'page_size' => $this->params['limit'],
                'page_num' => $this->params['page']
            ));


            $pageCnt = $dbresult['page_count'];
            $pageNum = $dbresult['page_num'];

            foreach ($dbresult['items'] as $aItem) {
                $source[] = array(
                    'src' => $aItem['SRC'],
                    'id' => $aItem['ID'],
                );
            }
        }


        $elements = array();
        if ($this->params['collection_id'] > 0 && !empty($this->params['element_ids'])){

            $dbresult = Medialib::GetElements(array(
                'collection_id' => $this->params['collection_id'],
                'id' => $this->params['element_ids'],
            ));

            $unsorted = array();
            foreach ($dbresult['items'] as $aItem) {
                $unsorted[ $aItem['ID'] ] = array(
                    'src' => $aItem['SRC'],
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
            'collections' => $collections,
            'elements' => $elements,
            'source' => $source,
            'collection_id' => $this->params['collection_id'],
            'element_ids' => $this->params['element_ids'],
            'page_num' => $pageNum,
            'page_cnt' => $pageCnt
        )));
    }


}