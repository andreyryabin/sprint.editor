<?php

namespace Sprint\Editor\Tools;

class Image
{

    static public function resizeImage2($image, $resizeParams = array()) {
        $resizeParams = array_merge(array(
            'width' => 0,
            'height' => 0,
            'exact' => 0,
            'init_sizes' => false,
            'filters' => false,
            'immediate' => false,
            'jpg_quality' => false,
        ), $resizeParams);

        if (is_numeric($image)) {
            $image = \CFile::GetFileArray($image);
        }

        if ($resizeParams['exact']) {
            $resizeType = BX_RESIZE_IMAGE_EXACT;
        } else {
            $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
        }

        if ($image && empty($image['SRC'])) {
            $image['SRC'] = \CFile::GetFileSRC($image);
        }

        if ($resizeParams['width'] > 0 && $resizeParams['height'] > 0) {

            $size = array(
                "width" => $resizeParams['width'],
                "height" => $resizeParams['height']
            );

            $resized = \CFile::ResizeImageGet(
                $image,
                $size,
                $resizeType,
                $resizeParams['init_sizes'],
                $resizeParams['filters'],
                $resizeParams['immediate'],
                $resizeParams['jpg_quality']
            );

            if (isset($image['COLLECTION_ID'])) {
                $image = array(
                    "ID" => $image["ID"],
                    "COLLECTION_ID" => $image["COLLECTION_ID"],
                    "WIDTH" => $resized["width"],
                    "HEIGHT" => $resized["height"],
                    "SRC" => self::urlencodePath($resized["src"]),
                    "ORIGIN_SRC" => self::urlencodePath($image['SRC']),
                    "NAME" => $image['NAME'],
                    "DESCRIPTION" => htmlspecialchars($image['DESCRIPTION']),
                );
            } else {
                $image = array(
                    "ID" => $image["ID"],
                    "WIDTH" => $resized["width"],
                    "HEIGHT" => $resized["height"],
                    "SRC" => self::urlencodePath($resized["src"]),
                    "ORIGIN_SRC" => self::urlencodePath($image['SRC']),
                );
            }
        }
        return $image;
    }


    /** @deprecated */
    static public function resizeImageById($iImageId, $width = 0, $height = 0, $exact = 0) {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        if ($aImage = \CFile::GetFileArray($iImageId)) {
            $aImage = self::resizeImage($aImage, $width, $height, $exact);
        }
        return $aImage;
    }

    /** @deprecated */
    static public function resizeImage($aImage, $width = 0, $height = 0, $exact = 0) {
        if ($width > 0 && $height > 0) {
            $resizeType = ($exact) ? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL;
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */

            $aImage['SRC'] = !empty($aImage['SRC']) ? $aImage['SRC'] : \CFile::GetFileSRC($aImage);
            $resized = \CFile::ResizeImageGet($aImage, array("width" => $width, "height" => $height), $resizeType,
                true);

            if (isset($aImage['COLLECTION_ID'])) {
                $aImage = array(
                    "ID" => $aImage["ID"],
                    "COLLECTION_ID" => $aImage["COLLECTION_ID"],
                    "WIDTH" => $resized["width"],
                    "HEIGHT" => $resized["height"],
                    "SRC" => self::urlencodePath($resized["src"]),
                    "ORIGIN_SRC" => self::urlencodePath($aImage['SRC']),
                    "NAME" => $aImage['NAME'],
                    "DESCRIPTION" => htmlspecialchars($aImage['DESCRIPTION']),
                );
            } else {
                $aImage = array(
                    "ID" => $aImage["ID"],
                    "WIDTH" => $resized["width"],
                    "HEIGHT" => $resized["height"],
                    "SRC" => self::urlencodePath($resized["src"]),
                    "ORIGIN_SRC" => self::urlencodePath($aImage['SRC']),
                );
            }
        }
        return $aImage;
    }

    static protected function urlencodePath($path) {
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $len = count($parts);

        if ($len > 0) {
            $parts[$len - 1] = rawurlencode($parts[$len - 1]);
            $path = implode(DIRECTORY_SEPARATOR, $parts);
        }
        return $path;

    }
}