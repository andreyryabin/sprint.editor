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
        if ($filesCount > 0) {
            return sprintf('Очищение корзины. Удалено файлов: %d', $filesCount);
        }
        return 'Корзина пуста. Не найдено файлов для удаления';
    }

    public function getSearchColor(): string
    {
        return 'warning';
    }

    public function scanEntityElements($entityId, $pageNum = 1): array
    {
        $trashFiles = new TrashFilesTable();

        if ($pageNum == 1) {
            $trashFiles->cleanExists();
        }

        $limit = 20;
        $filesCount = $trashFiles->cleanTrashByStep($limit);

        return [
            'has_next'    => $filesCount >= $limit,
            'files_count' => $filesCount,
        ];
    }
}
