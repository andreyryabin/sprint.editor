<?php

namespace Sprint\Editor\Cleaner;

use DirectoryIterator;
use Sprint\Editor\Module;

class EditorPackStepper extends AbstractStepper
{
    public function getEntityIds(): array
    {
        return ['packs'];
    }

    public function scanEntityElements($entityId, $pageNum = 1): array
    {
        $filesCount = 0;
        foreach ($this->getPackFiles() as $filePath) {
            $fileIds = (new EditorTools())->getFileIds(file_get_contents($filePath));
            $filesCount += (new TrashFilesTable())->insertFilesToTable($fileIds, 1);
        }

        return [
            'has_next'    => false,
            'files_count' => $filesCount,
        ];
    }

    public function getSearchMessage($entityId, int $filesCount): string
    {
        return sprintf('Поиск макетов редактора. Найдено файлов: %d', $filesCount);
    }

    protected function getPackFiles(): array
    {
        $dir = Module::getPacksDir();
        $files = [];
        $iterator = new DirectoryIterator($dir);
        foreach ($iterator as $item) {
            if ($item->getExtension() == 'json') {
                $files[] = $item->getPathname();
            }
        }
        return $files;
    }
}
