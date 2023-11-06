<?php

namespace Sprint\Editor;

use Sprint\Editor\Exceptions\AdminPageException;

class PackBuilder
{
    public static function getPackJson($packId)
    {
        if ($packId) {
            $file = self::getPackFile($packId);

            if (is_file($file)) {
                return file_get_contents($file);
            }
        }
        return '';
    }

    public static function getPackTitle($packId)
    {
        $packJson = self::getPackJson($packId);
        if ($packJson) {
            $packContent = json_decode($packJson, true);

            return $packContent['packname'] ?? $packId;
        }

        return '';
    }

    /**
     * @throws AdminPageException
     */
    public static function createPack($packId, $packJson, $packTitle, $settingsName)
    {
        if (is_file(self::getPackFile($packId))) {
            throw new AdminPageException(GetMessage('SPRINT_EDITOR_pack_err_exists'));
        }

        return self::updateBlock($packId, $packJson, $packTitle, $settingsName);
    }

    public static function deletePack($packId)
    {
        $file = self::getPackFile($packId);
        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * @throws AdminPageException
     */
    public static function updateBlock($packId, $packJson, $packTitle, $settingsName)
    {
        if (empty($packId)) {
            throw new AdminPageException(GetMessage('SPRINT_EDITOR_pack_err_name'));
        }

        if (empty($packTitle)) {
            throw new AdminPageException(GetMessage('SPRINT_EDITOR_pack_err_title'));
        }

        $packJson = json_decode($packJson, true);
        if (!is_array($packJson)) {
            throw new AdminPageException(GetMessage('SPRINT_EDITOR_pack_err_build'));
        }

        $packJson = array_merge([
            'version'          => 2,
            'packname'         => $packTitle,
            'userSettingsName' => $settingsName,
            'blocks'           => [],
            'layouts'          => [],
        ], $packJson);

        file_put_contents(
            self::getPackFile($packId),
            json_encode($packJson, JSON_UNESCAPED_UNICODE)
        );

        return $packId;
    }

    protected static function getPackFile($packId)
    {
        return Module::getPacksDir() . $packId . '.json';
    }
}
