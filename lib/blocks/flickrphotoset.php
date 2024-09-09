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

    public static function getPhotos($photosetId)
    {
        if (empty($photosetId)) {
            return [];
        }

        $response = self::sendRequest([
            'method'      => 'flickr.photosets.getPhotos',
            'photoset_id' => $photosetId,
        ]);

        $items = (array)$response['photoset']['photo'] ?? [];

        $images = [];
        foreach ($items as $item) {
            $images[] = [
                'DETAIL_SRC'  => self::getImageSrc($item, 'b'),
                'SRC'         => self::getImageSrc($item, 'm'),
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
