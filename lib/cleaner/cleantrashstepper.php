<?php

namespace Sprint\Editor\Cleaner;

use CFile;

class CleanTrashStepper extends AbstractStepper
{
    public function getEntityIds(): array
    {
        return ['clean'];
    }

    public function getSearchMessage($entityId, int $filesCount): string
    {
        return sprintf('Удаление неиспользуемых файлов: %d', $filesCount);
    }

    public function getSearchColor(): string
    {
        return 'warning';
    }

    public function scanEntityElements($entityId, $pageNum = 1): array
    {
        $limit = 20;

        $trashFiles = new TrashFilesTable();

        $dbres = $trashFiles->getTrashList($pageNum, $limit);

        $filesCount = 0;
        while ($item = $dbres->fetch()) {
            $filesCount++;
            CFile::Delete($item['file_id']);
        }

        return [
            'has_next'    => $filesCount >= $limit,
            'files_count' => $filesCount,
        ];
    }
}
