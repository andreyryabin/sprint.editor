<?php

namespace Sprint\Editor\Blocks;

use Sprint\Editor\Module;

class FlickrPhotoset
{
    public static function getInfo($photosetId)
    {
        if (empty($photosetId)) {
            return [];
        }

        return self::sendRequest([
            'method'      => 'flickr.photosets.getInfo',
            'photoset_id' => $photosetId,
        ]);
    }

    public static function getInfoWithPreviews($photosetId, $previewsLimit = 5)
    {
        $info = self::getInfo($photosetId);

        $cntPhotos = (int)($info['photoset']['count_photos'] ?? 0);

        $info['previews'] = [];
        $info['count_other_previews'] = 0;

        if ($cntPhotos > 0) {
            $info['previews'] = self::getPhotos($photosetId, [
                'page'         => 1,
                'per_page'     => $previewsLimit,
                'preview_size' => 's',
            ]);
            $info['count_other_previews'] = $cntPhotos - count($info['previews']);
        }

        return $info;
    }

    public static function getPhotosets($page, $limit = 10)
    {
        return self::sendRequest([
            'method'   => 'flickr.photosets.getList',
            'page'     => $page,
            'per_page' => $limit,
        ]);
    }

    public static function getPhotos($photosetId, $query = [])
    {
        if (empty($photosetId)) {
            return [];
        }

        //sizes = https://www.flickr.com/services/api/misc.urls.html;
        $previewSize = 'm';
        if (isset($query['preview_size'])) {
            $previewSize = $query['preview_size'];
            unset($query['preview_size']);
        }

        $detailSize = 'b';
        if (isset($query['detail_size'])) {
            $detailSize = $query['detail_size'];
            unset($query['detail_size']);
        }

        $response = self::sendRequest(array_merge($query, [
            'method'      => 'flickr.photosets.getPhotos',
            'photoset_id' => $photosetId,
        ]));

        $items = (array)$response['photoset']['photo'] ?? [];

        $images = [];
        foreach ($items as $item) {
            $images[] = [
                'DETAIL_SRC'  => self::getImageSrc($item, $detailSize),
                'SRC'         => self::getImageSrc($item, $previewSize),
                'DESCRIPTION' => $item['title'],
            ];
        }

        return $images;
    }

    private static function sendRequest(array $params)
    {
        $params = array_merge($params, [
            'api_key'        => Module::getDbOption('flickr_api_key'),
            'user_id'        => Module::getDbOption('flickr_user_id'),
            'format'         => 'json',
            'nojsoncallback' => 1,
        ]);

        $endpoint = 'https://www.flickr.com/services/rest/?' . http_build_query($params);

        $ctx = stream_context_create(['http' => ['timeout' => 5,]]);
        $response = file_get_contents($endpoint, false, $ctx);
        return json_decode($response, true);
    }

    private static function getImageSrc(array $image, $size)
    {
        return str_replace(
            [
                '{farm}',
                '{server}',
                '{id}',
                '{secret}',
                '{size}',
            ],
            [
                $image['farm'],
                $image['server'],
                $image['id'],
                $image['secret'],
                $size,
            ],
            "http://farm{farm}.staticflickr.com/{server}/{id}_{secret}_{size}.jpg"
        );
    }
}
