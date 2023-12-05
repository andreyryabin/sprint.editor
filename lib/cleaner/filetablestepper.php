<?php

namespace Sprint\Editor\Cleaner;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use CFile;

class FileTableStepper extends AbstractStepper
{
    public function getEntityIds(): array
    {
        return ['b_file'];
    }

    public function getSearchMessage($entityId, int $filesCount): string
    {
        return sprintf('Поиск загруженных файлов редактора. Найдено: %d', $filesCount);
    }

    /**
     * @throws SqlQueryException
     */
    public function scanEntityElements($entityId, $pageNum = 1): array
    {
        $connection = Application::getConnection();

        $limit = 50;

        $offset = ($pageNum - 1) * $limit;


        $sql = <<<SQL
            SELECT `ID` FROM `b_file` 
            WHERE `MODULE_ID`="sprint.editor"
            LIMIT $limit OFFSET $offset;
SQL;
        $dbres = $connection->query($sql);

        $fileIds = [];
        while ($item = $dbres->fetch()) {
            $fileIds[] = $item['ID'];
        }

        $filesCount = (new TrashFilesTable())->insertFilesToTable($fileIds, 0);

        return [
            'has_next'    => $filesCount >= $limit,
            'files_count' => $filesCount,
        ];
    }
}
