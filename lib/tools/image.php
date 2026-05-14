<?php

namespace Sprint\Editor\Tools;

use CFile;

class Image
{
    static public function resizeImage2($image, $resizeParams = [])
    {
        $resizeParams = array_merge(
            [
                'width'       => 1024,
                'height'      => 768,
                'exact'       => 0,
                'init_sizes'  => false,
                'filters'     => false,
                'immediate'   => false,
                'jpg_quality' => false,
            ], $resizeParams
        );

        if (is_numeric($image)) {
            $image = CFile::GetFileArray($image);
        }

        if (empty($image["ID"])) {
            return [];
        }

        if (empty($image['SRC'])) {
            $image['SRC'] = CFile::GetFileSRC($image);
        }

        if ($resizeParams['width'] > 0 && $resizeParams['height'] > 0) {
            if ($resizeParams['exact']) {
                $resizeType = BX_RESIZE_IMAGE_EXACT;
            } else {
                $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
            }

            $size = [
                "width"  => $resizeParams['width'],
                "height" => $resizeParams['height'],
            ];

            $resized = CFile::ResizeImageGet(
                $image,
                $size,
                $resizeType,
                $resizeParams['init_sizes'],
                $resizeParams['filters'],
                $resizeParams['immediate'],
                $resizeParams['jpg_quality']
            );
            if (empty($resized['width'])) {
                $resized['width'] = $resizeParams['width'];
            }

            if (empty($resized['height'])) {
                $resized['height'] = $resizeParams['height'];
            }

            $image["ORIGIN_SRC"] = $image['SRC'];
            $image["WIDTH"] = $resized["width"];
            $image["HEIGHT"] = $resized["height"];
            $image["SRC"] = $resized["src"];
        }

        if (isset($image["SRC"])) {
            $image["SRC"] = self::urlencodePath($image["SRC"]);
        }

        if (isset($image["ORIGIN_SRC"])) {
            $image["ORIGIN_SRC"] = self::urlencodePath($image['ORIGIN_SRC']);
        }

        if (isset($image['DESCRIPTION'])) {
            $image['DESCRIPTION'] = htmlspecialcharsbx($image['DESCRIPTION']);
        }

        return $image;
    }

    /** @deprecated */
    static public function resizeImageById($iImageId, $width = 0, $height = 0, $exact = 0)
    {
        if ($aImage = CFile::GetFileArray($iImageId)) {
            $aImage = self::resizeImage($aImage, $width, $height, $exact);
        }
        return $aImage;
    }

    /** @deprecated */
    static public function resizeImage($aImage, $width = 0, $height = 0, $exact = 0)
    {
        if ($width > 0 && $height > 0) {
            $resizeType = ($exact) ? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL;

            $aImage['SRC'] = !empty($aImage['SRC']) ? $aImage['SRC'] : CFile::GetFileSRC($aImage);
            $resized = CFile::ResizeImageGet(
                $aImage, ["width" => $width, "height" => $height], $resizeType,
                true
            );

            if (isset($aImage['COLLECTION_ID'])) {
                $aImage = [
                    "ID"            => $aImage["ID"],
                    "COLLECTION_ID" => $aImage["COLLECTION_ID"],
                    "WIDTH"         => $resized["width"],
                    "HEIGHT"        => $resized["height"],
                    "SRC"           => self::urlencodePath($resized["src"]),
                    "ORIGIN_SRC"    => self::urlencodePath($aImage['SRC']),
                    "NAME"          => $aImage['NAME'],
                    "DESCRIPTION"   => htmlspecialcharsbx($aImage['DESCRIPTION']),
                ];
            } else {
                $aImage = [
                    "ID"         => $aImage["ID"],
                    "WIDTH"      => $resized["width"],
                    "HEIGHT"     => $resized["height"],
                    "SRC"        => self::urlencodePath($resized["src"]),
                    "ORIGIN_SRC" => self::urlencodePath($aImage['SRC']),
                ];
            }
        }
        return $aImage;
    }

    static protected function urlencodePath($url)
    {
        $path = parse_url($url, PHP_URL_PATH);

        $parts = explode("/", $path);

        foreach ($parts as &$part) {
            $part = rawurlencode(urldecode($part));
        }

        return str_replace($path, implode("/", $parts), $url);
    }

    static protected function unparse_url($parts): string
    {
        return (isset($parts['scheme']) ? "{$parts['scheme']}://" : '')
               . ($parts['user'] ?? '')
               . (isset($parts['pass']) ? ":{$parts['pass']}" : '')
               . (($parts['user'] ?? $parts['pass'] ?? '') ? '@' : '')
               . ($parts['host'] ?? '')
               . (isset($parts['port']) ? ":{$parts['port']}" : '')
               . ($parts['path'] ?? '')
               . (isset($parts['query']) ? "?{$parts['query']}" : '')
               . (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
    }
}
