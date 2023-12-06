<?php

namespace Sprint\Editor\Cleaner;

class CleanTrashStepper extends AbstractStepper
{
    public function getEntityIds(): array
    {
        return ['clean_trash'];
    }

    public function getSearchMessage($entityId, int $filesCount): string
    {
        return sprintf('Очищение корзины. Удалено файлов: %d', $filesCount);
    }

    public function getSearchColor(): string
    {
        return 'warning';
    }

    public function scanEntityElements($entityId, $pageNum = 1): array
    {
        $trashFiles = new TrashFilesTable();

        $limit = 20;
        $filesCount = $trashFiles->cleanTrashByStep($limit);

        return [
            'has_next'    => $filesCount >= $limit,
            'files_count' => $filesCount,
        ];
    }
}
