<?php

namespace Sprint\Editor\Cleaner;

use Bitrix\Main\DB\SqlQueryException;

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
        $limit = 50;

        $offset = ($pageNum - 1) * $limit;

        $filesCount = (new TrashFilesTable())->copyFilesFromBitrix($limit, $offset);

        return [
            'has_next'    => $filesCount >= $limit,
            'files_count' => $filesCount,
        ];
    }
}
