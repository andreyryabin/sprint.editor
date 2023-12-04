<?php

namespace Sprint\Editor;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CIBlockElement;
use CIBlockProperty;
use CUserTypeEntity;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class TrashFiles
{
    /**
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule('iblock');
        Loader::includeModule('highloadblock');
    }

    /**
     * @throws SqlQueryException
     */
    public function createTable()
    {
        $connection = Application::getConnection();

        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS `sprint_editor_trash_files`(
            `id` INT NOT NULL AUTO_INCREMENT,
            `file_id` INT NOT NULL,
            `exists` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id), UNIQUE KEY (file_id)
        )ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;
SQL;
        $connection->query($sql);
        $connection->query('TRUNCATE TABLE `sprint_editor_trash_files`');
    }

    /**
     * @throws SqlQueryException
     */
    public function scanFileTableSlice($pageNum = 1): array
    {
        $connection = Application::getConnection();

        $limit = 50;

        $offset = ($pageNum - 1) * $limit;

        $sql = <<<SQL
            SELECT `ID` FROM `b_file` 
            WHERE `MODULE_ID` = "sprint.editor"
            LIMIT $limit OFFSET $offset;
SQL;
        $dbres = $connection->query($sql);

        $fileIds = [];
        while ($item = $dbres->fetch()) {
            $fileIds[] = $item['ID'];
        }

        $this->insertFilesToTable($fileIds, 0);

        return [
            'has_next_page' => count($fileIds) >= $limit,
            'files_count'   => count($fileIds),
        ];
    }

    public function getIblockIdsWithEditor(): array
    {
        $dbres = CIBlockProperty::GetList(['SORT' => 'ASC'], ['USER_TYPE' => 'sprint_editor']);
        $res = [];
        while ($item = $dbres->Fetch()) {
            $itemId = $item['IBLOCK_ID'];
            $res[$itemId] = $itemId;
        }
        return array_values($res);
    }

    public function scanIblockElementsSlice($iblockId, $pageNum = 1): array
    {
        $editorProps = $this->getIblockEditorProps($iblockId);

        $limit = 10;

        $dbres = $this->createIblockDbResult($iblockId, $editorProps, $pageNum, $limit);

        $filesCount = 0;
        $itemsCount = 0;
        while ($item = $dbres->Fetch()) {
            $itemsCount++;

            foreach ($editorProps as $propId) {
                if (!empty($item[$propId . '_VALUE'])) {
                    $filesCount += $this->collectFilesFromEditorJson($item[$propId . '_VALUE']);
                }
            }
        }

        return [
            'has_next_page' => $itemsCount >= $limit,
            'files_count'   => $filesCount,
        ];
    }

    protected function createIblockDbResult($iblockId, $editorProps, $pageNum, $limit)
    {
        return CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID'         => $iblockId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            [
                'nPageSize'       => $limit,
                'iNumPage'        => $pageNum,
                'checkOutOfRange' => true,
            ],
            array_merge(
                ['IBLOCK_ID', 'ID'],
                $editorProps
            ),
        );
    }

    protected function getIblockEditorProps($iblockId): array
    {
        $dbres = CIBlockProperty::GetList(
            [
                'SORT' => 'ASC',
            ],
            [
                'IBLOCK_ID' => $iblockId,
                'USER_TYPE' => 'sprint_editor',
            ]
        );

        $props = [];
        while ($item = $dbres->Fetch()) {
            $props[] = 'PROPERTY_' . $item['ID'];
        }

        return $props;
    }

    protected function collectFilesFromEditorJson($editorJson): int
    {
        $haystack = json_decode($editorJson, true);
        if (!is_array($haystack)) {
            return 0;
        }

        $fileIds = [];

        $iterator = new RecursiveArrayIterator($haystack);
        $recursive = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($recursive as $key => $value) {
            if ($key === 'file' && !empty($value['ID']) && !empty($value['SRC'])) {
                $fileIds[] = $value['ID'];
            }
        }

        $this->insertFilesToTable($fileIds, 1);

        return count($fileIds);
    }

    protected function insertFilesToTable(array $ids, $exists)
    {
        if (empty($ids)) {
            return;
        }

        $connection = Application::getConnection();

        $exists = intval($exists);

        $values = [];
        foreach ($ids as $id) {
            $values[] = '("' . intval($id) . '","' . $exists . '")';
        }
        $values = implode(',', $values);

        $str = <<<SQL
    INSERT INTO `sprint_editor_trash_files` (`file_id`,`exists`) 
    VALUES $values ON DUPLICATE KEY UPDATE `exists`="$exists";
SQL;

        $connection->query($str);
    }

    public function getHlblockIdsWithEditor()
    {
        $dbres = CUserTypeEntity::GetList([], ['USER_TYPE_ID' => 'sprint_editor']);

        $res = [];
        while ($item = $dbres->Fetch()) {
            if (0 === strpos($item['ENTITY_ID'], 'HLBLOCK_')) {
                $itemId = substr($item['ENTITY_ID'], 8);
                $res[$itemId] = $itemId;
            }
        }
        return array_values($res);
    }

    public function scanHlblockElementsSlice($hlblockId, $pageNum = 1)
    {
        $editorProps = $this->getHlblockEditorProps($hlblockId);

        $limit = 10;

        $dbres = $this->createHlblockDbResult($hlblockId, $editorProps, $pageNum, $limit);

        $itemsCount = 0;
        $filesCount = 0;

        while ($item = $dbres->fetch()) {
            $itemsCount++;

            foreach ($editorProps as $propId) {
                if (!empty($item[$propId])) {
                    $filesCount += $this->collectFilesFromEditorJson($item[$propId]);
                }
            }
        }

        return [
            'has_next_page' => $itemsCount >= $limit,
            'files_count'   => $filesCount,
        ];
    }

    protected function createHlblockDbResult($hlblockId, $editorProps, $pageNum, $limit)
    {
        $hlblock = HighloadBlockTable::getById($hlblockId)->fetch();

        $entity = HighloadBlockTable::compileEntity($hlblock);
        $dataManager = $entity->getDataClass();

        $offset = ($pageNum - 1) * $limit;

        return $dataManager::GetList([
            'order'  => ['ID' => 'ASC'],
            'offset' => $offset,
            'limit'  => $limit,
            'select' => array_merge(
                ['ID'],
                $editorProps
            ),
        ]);
    }

    protected function getHlblockEditorProps($hlblockId): array
    {
        $dbres = CUserTypeEntity::GetList(
            [],
            [
                'ENTITY_ID'    => 'HLBLOCK_' . $hlblockId,
                'USER_TYPE_ID' => 'sprint_editor',
            ]
        );

        $props = [];
        while ($item = $dbres->Fetch()) {
            $props[] = $item['FIELD_NAME'];
        }

        return $props;
    }
}



