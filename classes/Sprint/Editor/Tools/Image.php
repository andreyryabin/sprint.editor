<?php

namespace Sprint\Editor\Tools;

class Image
{

    static public function resizeImageById($iImageId, $width = 0, $height = 0, $exact = 0) {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        if ($aImage =  \CFile::GetFileArray($iImageId)) {
            $aImage = self::resizeImage($aImage, $width, $height, $exact);
        }
        return $aImage;
    }

    static public function resizeImage($aImage, $width = 0, $height = 0, $exact = 0) {
        if ($width > 0 && $height > 0) {
            $resizeType = ($exact) ? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL;
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */

            $aImage['SRC'] = !empty($aImage['SRC']) ? $aImage['SRC'] : \CFile::GetFileSRC($aImage);
            $resized = \CFile::ResizeImageGet($aImage, array("width" => $width, "height" => $height), $resizeType, true);
            if (isset($aImage['COLLECTION_ID'])){
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

    static protected function urlencodePath($path){
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $len = count($parts);

        if ($len > 0){
            $parts[$len - 1] = rawurlencode($parts[$len - 1]);
            $path = implode(DIRECTORY_SEPARATOR, $parts);
        }
        return $path;

    }
}