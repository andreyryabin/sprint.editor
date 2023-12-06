<?php

namespace Sprint\Editor\Cleaner;

use Bitrix\Main\Loader;
use CIBlockResult;
use CIBlockSection;
use CUserTypeEntity;

class IblockCategoryStepper extends AbstractStepper
{
    public function __construct()
    {
        Loader::includeModule('iblock');
    }

    public function getSearchMessage($entityId, int $filesCount): string
    {
        return sprintf('Поиск категорий в инфоблоке c ID:%s. Найдено файлов: %d', $entityId, $filesCount);
    }

    public function getEntityIds(): array
    {
        $dbres = CUserTypeEntity::GetList([], ['USER_TYPE_ID' => 'sprint_editor']);
        $res = [];
        while ($item = $dbres->Fetch()) {
            if (preg_match('/IBLOCK_(\d+)_SECTION/', $item['ENTITY_ID'], $matches)) {
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

        while ($item = $dbres->Fetch()) {
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

    protected function getEditorFields($entityId): array
    {
        $dbres = CUserTypeEntity::GetList(
            [],
            [
                'ENTITY_ID'    => 'IBLOCK_' . $entityId . '_SECTION',
                'USER_TYPE_ID' => 'sprint_editor',
            ]
        );

        $props = [];
        while ($item = $dbres->Fetch()) {
            $props[] = $item['FIELD_NAME'];
        }

        return $props;
    }

    protected function createDbResut($entityId, $editorProps, $pageNum, $limit): CIBlockResult
    {
        return CIBlockSection::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID'         => $entityId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            array_merge(
                ['IBLOCK_ID', 'ID'],
                $editorProps
            ),
            [
                'nPageSize'       => $limit,
                'iNumPage'        => $pageNum,
                'checkOutOfRange' => true,
            ],
        );
    }
}
