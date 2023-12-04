<?php

namespace Sprint\Editor\Controller;

use Bitrix\Main\Engine\Controller;
use Sprint\Editor\TrashFiles;

class Cleaner extends Controller
{
    const NEXT_ACTION  = 'next_action';
    const OUT_MESSAGES = 'messages';


    public function startAction(array $fields): array
    {
        $trashFiles = new TrashFiles;

        $trashFiles->createTable();

        $this->addMessage($fields, 'Создание временной таблицы');

        $fields[self::NEXT_ACTION] = 'scanIblockElements';

        return $fields;
    }

    public function scanIblockElementsAction($fields)
    {
        $trashFiles = new TrashFiles;

        if (!isset($fields['iblock_ids'])) {
            $iblockIds = $trashFiles->getIblockIdsWithEditor();
            if (empty($iblockIds)) {
                $this->addMessage($fields, 'Инфоблоки с редактором не найдены');
                $fields[self::NEXT_ACTION] = '';
                return $fields;
            }
            $this->addMessage(
                $fields,
                sprintf(
                    'Найдено инфоблоков с редактором: %d',
                    count($iblockIds)
                )
            );
            $fields['iblock_id'] = $iblockIds[0];
            $fields['iblock_ids'] = implode(',', $iblockIds);
            $fields[self::NEXT_ACTION] = 'scanIblockElements';
            return $fields;
        }

        $fields['page_num'] = (int)($fields['page_num'] ?? 1);

        $slice = $trashFiles->scanIblockElementsSlice($fields['iblock_id'], $fields['page_num']);

        $fields['files_count'] = (int)($fields['files_count'] ?? 0);
        $fields['files_count'] += $slice['files_count'];

        $this->addMessage(
            $fields,
            sprintf(
                'Поиск в инфоблоке с ID: %d. Найдено файлов: %d',
                $fields['iblock_id'],
                $fields['files_count']
            ),
            'iblock_result_' . $fields['iblock_id']
        );

        if ($slice['has_next_page']) {
            $fields['page_num']++;
            $fields[self::NEXT_ACTION] = 'scanIblockElements';
            return $fields;
        }

        $iblockIds = explode(',', $fields['iblock_ids']);
        $nextIndex = array_search($fields['iblock_id'], $iblockIds) + 1;
        if (isset($iblockIds[$nextIndex])) {
            $fields['iblock_id'] = $iblockIds[$nextIndex];
            $fields['page_num'] = 1;
            $fields['files_count'] = 0;
            $fields[self::NEXT_ACTION] = 'scanIblockElements';
            return $fields;
        }

        $fields[self::NEXT_ACTION] = 'scanFileTable';
        return $fields;
    }

    public function scanFileTableAction($fields)
    {
        $trashFiles = new TrashFiles;
        $fields['page_num'] = (int)($fields['page_num'] ?? 1);

        $slice = $trashFiles->scanFileTableSlice($fields['page_num']);

        $fields['files_count'] = (int)($fields['files_count'] ?? 0);
        $fields['files_count'] += $slice['files_count'];

        $this->addMessage(
            $fields,
            sprintf(
                'Поиск в таблице файлов. Найдено файлов: %d',
                $fields['files_count']
            ),
            'files_result'
        );

        if ($slice['has_next_page']) {
            $fields['page_num']++;
            $fields[self::NEXT_ACTION] = 'scanFileTable';
            return $fields;
        }

        $fields[self::NEXT_ACTION] = '';
        return $fields;
    }

    protected function addMessage(&$fields, $message, $id = '')
    {
        if (!isset($fields[self::OUT_MESSAGES])) {
            $fields[self::OUT_MESSAGES] = [];
        }

        $fields[self::OUT_MESSAGES][] = [
            'id'   => 'sp-m-' . $id,
            'text' => $message,
        ];
    }
}
