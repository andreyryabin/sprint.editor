<?php

namespace Sprint\Editor;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CIBlockElement;
use CIBlockProperty;
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

        $this->insertIds($fileIds, 0);

        return [
            'has_next_page' => count($fileIds) >= $limit,
            'files_count'   => count($fileIds),
        ];
    }

    public function getIblockIdsWithEditor(): array
    {
        $dbres = CIBlockProperty::GetList(['SORT' => 'ASC'], ['USER_TYPE' => 'sprint_editor']);
        $iblocks = [];
        while ($item = $dbres->Fetch()) {
            $iblockId = $item['IBLOCK_ID'];
            $iblocks[$iblockId] = $iblockId;
        }
        return array_values($iblocks);
    }

    public function scanIblockElementsSlice($iblockId, $pageNum = 1): array
    {
        $editorProps = $this->getEditorProps($iblockId);

        $dbres = $this->createIblockDbResult($iblockId, $pageNum, $editorProps);

        $fileIds = [];
        while ($item = $dbres->Fetch()) {
            foreach ($editorProps as $propId) {
                if (!empty($item[$propId . '_VALUE'])) {
                    $data = json_decode($item[$propId . '_VALUE'], true);
                    if (is_array($data)) {
                        $this->collectFilesFromEditorData($data, $fileIds);
                    }
                }
            }
        }

        $this->insertIds($fileIds, 1);

        return [
            'has_next_page' => $dbres->NavPageCount > $dbres->NavPageNomer,
            'files_count'   => count($fileIds),
        ];
    }

    protected function createIblockDbResult($iblockId, $pageNum, $select)
    {
        return CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID'         => $iblockId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            [
                'nPageSize'       => 10,
                'iNumPage'        => $pageNum,
                'checkOutOfRange' => true,
            ],
            array_merge(
                ['IBLOCK_ID', 'ID'],
                $select
            ),
        );
    }

    protected function getEditorProps($iblockId): array
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

    protected function collectFilesFromEditorData(array $haystack, array &$files)
    {
        $iterator = new RecursiveArrayIterator($haystack);
        $recursive = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($recursive as $key => $value) {
            if ($key === 'file' && !empty($value['ID']) && !empty($value['SRC'])) {
                $files[] = $value['ID'];
            }
        }
    }

    protected function insertIds(array $ids, $exists)
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
}



