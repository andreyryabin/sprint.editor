<?php

namespace Sprint\Editor\Cleaner;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use CUserTypeEntity;

class HlblockStepper extends AbstractStepper
{
    public function __construct()
    {
        Loader::includeModule('highloadblock');
    }

    public function getSearchMessage($entityId, int $filesCount): string
    {
        return sprintf('Поиск в highload-блоке c ID:%s. Найдено файлов: %d', $entityId, $filesCount);
    }

    public function getEntityIds(): array
    {
        $dbres = CUserTypeEntity::GetList([], ['USER_TYPE_ID' => 'sprint_editor']);

        $res = [];
        while ($item = $dbres->Fetch()) {
            if (preg_match('/HLBLOCK_(\d+)/', $item['ENTITY_ID'], $matches)) {
                $itemId = $matches[1];
                $res[$itemId] = $itemId;
            }
        }
        return array_values($res);
    }

    public function scanEntityElements($entityId, $pageNum = 1): array
    {
        $editorProps = $this->getEditorFields($entityId);

        $limit = 10;

        $dbres = $this->createDbResut($entityId, $editorProps, $pageNum, $limit);

        $itemsCount = 0;
        $filesCount = 0;

        while ($item = $dbres->fetch()) {
            $itemsCount++;

            foreach ($editorProps as $propId) {
                if (!empty($item[$propId])) {
                    $fileIds = (new EditorTools())->getFileIds($item[$propId]);
                    $filesCount += (new TrashFilesTable())->copyFilesFromEditor($fileIds);
                }
            }
        }

        return [
            'has_next'    => $itemsCount >= $limit,
            'files_count' => $filesCount,
        ];
    }

    protected function createDbResut($entityId, $editorProps, $pageNum, $limit)
    {
        $hlblock = HighloadBlockTable::getById($entityId)->fetch();

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

    protected function getEditorFields($entityId): array
    {
        $dbres = CUserTypeEntity::GetList(
            [],
            [
                'ENTITY_ID'    => 'HLBLOCK_' . $entityId,
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
