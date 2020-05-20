<?php

namespace Sprint\Editor\Blocks;

class YandexMap
{
    static public function getMapData($block, $params = [])
    {
        if (empty($block['placemarks'])) {
            return '';
        }

        $placemarks = [];
        foreach ($block['placemarks'] as $item) {
            $placemarks[] = [
                'LAT'  => $item['coords'][0],
                'LON'  => $item['coords'][1],
                'TEXT' => $item['text'],
            ];
        }

        return serialize(
            [
                'yandex_lat'   => $block['center'][0],
                'yandex_lon'   => $block['center'][1],
                'yandex_scale' => $block['zoom'],
                'PLACEMARKS'   => $placemarks,
            ]
        );
    }
}
