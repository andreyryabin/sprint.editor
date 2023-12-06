<?php

namespace Sprint\Editor\Cleaner;

use Bitrix\Main\Loader;
use CIBlockElement;
use CIBlockProperty;
use CIBlockResult;

class IblockElementStepper extends AbstractStepper
{
    public function __construct()
    {
        Loader::includeModule('iblock');
    }

    public function getSearchMessage($entityId, int $filesCount): string
    {
        return sprintf('Поиск элементов в инфоблоке c ID:%s. Найдено файлов: %d', $entityId, $filesCount);
    }

    public function getEntityIds(): array
    {
        $dbres = CIBlockProperty::GetList(['SORT' => 'ASC'], ['USER_TYPE' => 'sprint_editor']);
        $res = [];
        while ($item = $dbres->Fetch()) {
            $itemId = $item['IBLOCK_ID'];
            $res[$itemId] = $itemId;
        }
        return array_values($res);
    }

    public function scanEntityElements($entityId, $pageNum = 1): array
    {
        $editorProps = $this->getEditorFields($entityId);

        $limit = 10;

        $dbres = $this->createDbResut($entityId, $editorProps, $pageNum, $limit);

        $filesCount = 0;
        $itemsCount = 0;

        while ($item = $dbres->Fetch()) {
            $itemsCount++;

            foreach ($editorProps as $propId) {
                if (!empty($item[$propId . '_VALUE'])) {
                    $fileIds = (new EditorTools())->getFileIds($item[$propId . '_VALUE']);
                    $filesCount += (new TrashFilesTable())->copyFilesFromEditor($fileIds);
                }
            }
        }

        return [
            'has_next'    => $itemsCount >= $limit,
            'files_count' => $filesCount,
        ];
    }

    protected function createDbResut($entityId, $editorProps, $pageNum, $limit): CIBlockResult
    {
        return CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID'         => $entityId,
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

    protected function getEditorFields($entityId): array
    {
        $dbres = CIBlockProperty::GetList(
            [
                'SORT' => 'ASC',
            ],
            [
                'IBLOCK_ID' => $entityId,
                'USER_TYPE' => 'sprint_editor',
            ]
        );

        $props = [];
        while ($item = $dbres->Fetch()) {
            $props[] = 'PROPERTY_' . $item['ID'];
        }

        return $props;
    }
}
