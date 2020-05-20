<?php

namespace Sprint\Editor\Tools;

use CMedialib;
use CMedialibCollection;
use CModule;
use COption;

class Medialib
{
    private static $once = 0;

    private static function initialize()
    {
        if (!self::$once) {
            CModule::IncludeModule('fileman');
            CMedialib::Init();
            self::$once = 1;
        }
    }

    public static function GetCollections($filter = [])
    {
        self::initialize();
        $mltypes = CMedialib::GetTypes();

        $filter['type'] = isset($filter['type']) ? $filter['type'] : 'image';

        $typeid = 0;
        foreach ($mltypes as $mltype) {
            if ($mltype['code'] == $filter['type']) {
                $typeid = $mltype['id'];
                break;
            }
        }

        if ($typeid > 0) {
            return CMedialibCollection::GetList(
                [
                    'arFilter' => [
                        'TYPES' => [$typeid],
                    ],
                ]
            );
        } else {
            return [];
        }
    }

    public static function GetElements($filter, $navParams = [], $resizePreview = [], $resizeDetail = [])
    {
        self::initialize();

        global $DB;

        $arResult = [
            'items'      => [],
            'page_count' => 1,
            'page_num'   => 1,
        ];

        $whereQuery = [];
        if (!empty($filter['collection_id'])) {
            if (is_array($filter['collection_id'])) {
                $filter['collection_id'] = array_map(
                    function ($val) {
                        return intval($val);
                    }, $filter['collection_id']
                );

                $whereQuery[] = 'MCI.COLLECTION_ID in (' . implode(',', $filter['collection_id']) . ')';
            } elseif (intval($filter['collection_id']) > 0) {
                $whereQuery[] = 'MCI.COLLECTION_ID=' . intval($filter['collection_id']);
            }
        }

        if (!empty($filter['id'])) {
            if (is_array($filter['id'])) {
                $filter['id'] = array_map(
                    function ($val) {
                        return intval($val);
                    }, $filter['id']
                );

                $whereQuery[] = 'MI.ID in (' . implode(',', $filter['id']) . ')';
            } elseif (intval($filter['id']) > 0) {
                $whereQuery[] = 'MI.ID=' . intval($filter['id']);
            }
        }

        if (empty($whereQuery)) {
            return $arResult;
        }

        $limitQuery = '';
        $whereQuery = implode(' AND ', $whereQuery);

        if (isset($navParams['page_size'])) {
            $q = "SELECT COUNT(*) cnt
                FROM 
                    b_medialib_collection_item MCI
                INNER JOIN 
                    b_medialib_item MI ON (MI.ID=MCI.ITEM_ID)
                INNER JOIN 
                    b_file F ON (F.ID=MI.SOURCE_ID) 
                WHERE " . $whereQuery . ";";

            $allcount = $DB->Query($q)->Fetch();
            $allcount = ($allcount && $allcount['cnt']) ? $allcount['cnt'] : 0;

            $pagesize = intval($navParams['page_size']);
            $pagesize = $pagesize >= 1 ? $pagesize : 10;

            $pagenum = intval($navParams['page_num']);
            $pagenum = ($pagenum >= 1) ? $pagenum : 1;

            $arResult['page_count'] = ceil($allcount / $pagesize);
            $arResult['page_num'] = $pagenum;

            $navoffsset = ($pagenum - 1) * $pagesize;
            $limitQuery = 'LIMIT ' . $navoffsset . ',' . $pagesize;
        }

        $resizePreview = array_merge(
            [
                'width'  => COption::GetOptionInt('fileman', "ml_thumb_width", 140),
                'height' => COption::GetOptionInt('fileman', "ml_thumb_height", 105),
                'exact'  => 0,
            ], $resizePreview
        );

        $q = "SELECT MI.*,MCI.COLLECTION_ID, F.HEIGHT, F.WIDTH, F.FILE_SIZE, F.CONTENT_TYPE, F.SUBDIR, F.FILE_NAME, F.HANDLER_ID
            FROM 
                b_medialib_collection_item MCI
            INNER JOIN 
                b_medialib_item MI ON (MI.ID=MCI.ITEM_ID)
            INNER JOIN 
                b_file F ON (F.ID=MI.SOURCE_ID) 
            WHERE " . $whereQuery . " " . $limitQuery . ";";

        $dbResult = $DB->Query($q);

        while ($aImage = $dbResult->Fetch()) {
            $aItem = Image::resizeImage2($aImage, $resizePreview);
            $aItem['DETAIL_SRC'] = $aItem['SRC'];

            if (!empty($resizeDetail)) {
                $aDetail = Image::resizeImage2($aImage, $resizeDetail);
                $aItem['DETAIL_SRC'] = $aDetail['SRC'];
            }

            $arResult['items'][] = $aItem;
        }

        return $arResult;
    }
}
