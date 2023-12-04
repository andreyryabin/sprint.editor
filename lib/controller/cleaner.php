<?php

namespace Sprint\Editor\Controller;

use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Engine\Controller;
use Sprint\Editor\TrashFiles;

/**
 * @noinspection PhpUnused
 * controller: sprint:editor.controller.cleaner
 */
class Cleaner extends Controller
{
    const NEXT_ACTION  = 'next_action';
    const OUT_MESSAGES = 'messages';
    const QUEUE        = [
        'startAction',
        'scanIblockElementsAction',
        'scanHlblockElementsAction',
        'scanFileTableAction',
        'finishAction',
    ];

    /**
     * @throws SqlQueryException
     *
     * @noinspection PhpUnused
     * controller action: start
     */
    public function startAction(array $fields): array
    {
        $trashFiles = new TrashFiles;

        $trashFiles->createTable();

        $this->setNext($fields, __FUNCTION__);

        return $fields;
    }

    /**
     * @throws SqlQueryException
     * @noinspection PhpUnused
     * controller action: scanFileTable
     */
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
            $this->setRestart($fields, __FUNCTION__);
            return $fields;
        }

        $this->setNext($fields, __FUNCTION__);
        return $fields;
    }

    /**
     * @noinspection PhpUnused
     * controller action: scanIblockElements
     */
    public function scanIblockElementsAction($fields)
    {
        $trashFiles = new TrashFiles;

        if (!isset($fields['iblock_ids'])) {
            $iblockIds = $trashFiles->getIblockIdsWithEditor();
            if (empty($iblockIds)) {
                $this->addMessage($fields, 'Инфоблоки с редактором не найдены');
                $this->setNext($fields, __FUNCTION__);
                return $fields;
            }
            $fields['iblock_id'] = $iblockIds[0];
            $fields['iblock_ids'] = implode(',', $iblockIds);
            $this->setRestart($fields, __FUNCTION__);
            return $fields;
        }

        $fields['page_num'] = (int)($fields['page_num'] ?? 1);

        $slice = $trashFiles->scanIblockElementsSlice(
            $fields['iblock_id'],
            $fields['page_num']
        );

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
            $this->setRestart($fields, __FUNCTION__);
            return $fields;
        }

        $iblockIds = explode(',', $fields['iblock_ids']);
        $searchIndex = array_search($fields['iblock_id'], $iblockIds);
        if ($searchIndex >= 0 && isset($iblockIds[$searchIndex + 1])) {
            $fields['iblock_id'] = $iblockIds[$searchIndex + 1];
            $fields['page_num'] = 1;
            $fields['files_count'] = 0;
            $this->setRestart($fields, __FUNCTION__);
            return $fields;
        }

        $this->setNext($fields, __FUNCTION__);
        return $fields;
    }

    /**
     * @noinspection PhpUnused
     * controller action: scanIblockElements
     */
    public function scanHlblockElementsAction($fields)
    {
        $trashFiles = new TrashFiles;

        if (!isset($fields['hlblock_ids'])) {
            $hlblockIds = $trashFiles->getHlblockIdsWithEditor();
            if (empty($hlblockIds)) {
                $this->addMessage($fields, 'Highload-блоки с редактором не найдены');
                $this->setNext($fields, __FUNCTION__);
                return $fields;
            }
            $fields['hlblock_id'] = $hlblockIds[0];
            $fields['hlblock_ids'] = implode(',', $hlblockIds);
            $this->setRestart($fields, __FUNCTION__);
            return $fields;
        }

        $fields['page_num'] = (int)($fields['page_num'] ?? 1);

        $slice = $trashFiles->scanHlblockElementsSlice(
            $fields['hlblock_id'],
            $fields['page_num']
        );

        $fields['files_count'] = (int)($fields['files_count'] ?? 0);
        $fields['files_count'] += $slice['files_count'];

        $this->addMessage(
            $fields,
            sprintf(
                'Поиск в highload-блоке с ID: %d. Найдено файлов: %d',
                $fields['hlblock_id'],
                $fields['files_count']
            ),
            'hlblock_result_' . $fields['hlblock_id']
        );

        if ($slice['has_next_page']) {
            $fields['page_num']++;
            $this->setRestart($fields, __FUNCTION__);
            return $fields;
        }

        $hlblockIds = explode(',', $fields['hlblock_ids']);
        $searchIndex = array_search($fields['hlblock_id'], $hlblockIds);
        if ($searchIndex >= 0 && isset($hlblockIds[$searchIndex + 1])) {
            $fields['hlblock_id'] = $hlblockIds[$searchIndex + 1];
            $fields['page_num'] = 1;
            $fields['files_count'] = 0;
            $this->setRestart($fields, __FUNCTION__);
            return $fields;
        }

        $this->setNext($fields, __FUNCTION__);
        return $fields;
    }

    protected function addMessage(&$fields, $message, $id = '')
    {
        if (!isset($fields[self::OUT_MESSAGES])) {
            $fields[self::OUT_MESSAGES] = [];
        }

        $fields[self::OUT_MESSAGES][] = [
            'id'   => $id ? 'sp-m-' . $id : '',
            'text' => $message,
        ];
    }

    protected function setNext(&$fields, $method)
    {
        $index = array_search($method, self::QUEUE);
        if ($index >= 0 && isset(self::QUEUE[$index + 1])) {
            $fields[self::NEXT_ACTION] = str_replace('Action', '', self::QUEUE[$index + 1]);
        }
    }

    protected function setRestart(&$fields, $method)
    {
        $fields[self::NEXT_ACTION] = str_replace('Action', '', $method);
    }
}
