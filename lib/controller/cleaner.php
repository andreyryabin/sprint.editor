<?php

namespace Sprint\Editor\Controller;

use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Engine\Controller;
use Sprint\Editor\Cleaner\AbstractStepper;
use Sprint\Editor\Cleaner\CleanTrashStepper;
use Sprint\Editor\Cleaner\EditorPackStepper;
use Sprint\Editor\Cleaner\FileTableStepper;
use Sprint\Editor\Cleaner\HlblockStepper;
use Sprint\Editor\Cleaner\IblockCategoryStepper;
use Sprint\Editor\Cleaner\IblockElementStepper;
use Sprint\Editor\Cleaner\TrashFilesTable;

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
        'scanIblockElementAction',
        'scanIblockCategoryAction',
        'scanHlblockElementsAction',
        'scanEditorPacksAction',
        'scanFileTableAction',
        //'cleanTrashAction',
        'finishAction',
    ];

    /**
     * @throws SqlQueryException
     *
     * @noinspection PhpUnused
     */
    public function startAction(array $fields): array
    {
        $trashFiles = new TrashFilesTable();

        $trashFiles->createTable();

        $this->addMessage($fields, 'Создаём корзину', 'start', 'primary');

        $this->setNextAction($fields, __FUNCTION__);
        return $fields;
    }

    /** @noinspection PhpUnused */
    public function finishAction($fields)
    {
        $trashFiles = new TrashFilesTable();

        $trashFiles->cleanExists();

        $trashCnt = $trashFiles->getTrashFilesCount();

        $this->addMessage(
            $fields,
            'Корзина создана. Найдено файлов: ' . $trashCnt,
            'start',
            'success'
        );

        return $fields;
    }

    /** @noinspection PhpUnused */
    public function cleanTrashAction(array $fields)
    {
        $stepper = new CleanTrashStepper();

        return $this->stepper($stepper, $fields, __FUNCTION__);
    }

    /** @noinspection PhpUnused */
    public function scanFileTableAction($fields)
    {
        $stepper = new FileTableStepper();

        return $this->stepper($stepper, $fields, __FUNCTION__);
    }

    /** @noinspection PhpUnused */
    public function scanIblockElementAction($fields)
    {
        $stepper = new IblockElementStepper();

        return $this->stepper($stepper, $fields, __FUNCTION__);
    }

    /** @noinspection PhpUnused */
    public function scanIblockCategoryAction($fields)
    {
        $stepper = new IblockCategoryStepper();

        return $this->stepper($stepper, $fields, __FUNCTION__);
    }

    /** @noinspection PhpUnused */
    public function scanHlblockElementsAction($fields)
    {
        $stepper = new HlblockStepper();

        return $this->stepper($stepper, $fields, __FUNCTION__);
    }

    /** @noinspection PhpUnused */
    public function scanEditorPacksAction($fields)
    {
        $stepper = new EditorPackStepper();

        return $this->stepper($stepper, $fields, __FUNCTION__);
    }

    protected function addMessage(&$fields, $message, $id = '', $color = '')
    {
        if (!isset($fields[self::OUT_MESSAGES])) {
            $fields[self::OUT_MESSAGES] = [];
        }

        $fields[self::OUT_MESSAGES][] = [
            'id'    => $id ? 'sp-m-' . $id : '',
            'text'  => $message,
            'color' => $color,
        ];
    }

    protected function setNextAction(&$fields, $parentfunc)
    {
        $index = array_search($parentfunc, self::QUEUE);
        if ($index >= 0 && isset(self::QUEUE[$index + 1])) {
            $this->setAction($fields, self::QUEUE[$index + 1]);
        }
    }

    protected function setAction(&$fields, $parentfunc)
    {
        $action = str_replace('Action', '', $parentfunc);
        $fields[self::NEXT_ACTION] = $action;
    }

    private function stepper(AbstractStepper $stepper, $fields, $parentfunc)
    {
        if (!isset($fields['entity_ids'])) {
            $entityIds = $stepper->getEntityIds();
            if (empty($entityIds)) {
                $this->setNextAction($fields, $parentfunc);
                return $fields;
            }
            $fields['entity_id'] = $entityIds[0];
            $fields['entity_ids'] = implode(',', $entityIds);
            $this->setAction($fields, $parentfunc);
            return $fields;
        }

        $fields['page_num'] = (int)($fields['page_num'] ?? 1);

        $slice = $stepper->scanEntityElements(
            $fields['entity_id'],
            $fields['page_num']
        );

        $fields['files_count'] = (int)($fields['files_count'] ?? 0);
        $fields['files_count'] += $slice['files_count'];

        $this->addMessage(
            $fields,
            $stepper->getSearchMessage(
                $fields['entity_id'],
                $fields['files_count']
            ),
            $parentfunc . $fields['entity_id'],
            $stepper->getSearchColor()
        );

        if ($slice['has_next']) {
            $fields['page_num']++;
            $this->setAction($fields, $parentfunc);
            return $fields;
        }

        $entityIds = explode(',', $fields['entity_ids']);
        $searchIndex = array_search($fields['entity_id'], $entityIds);
        if ($searchIndex >= 0 && isset($entityIds[$searchIndex + 1])) {
            $fields['entity_id'] = $entityIds[$searchIndex + 1];
            $fields['page_num'] = 1;
            $fields['files_count'] = 0;
            $this->setAction($fields, $parentfunc);
            return $fields;
        }

        $this->setNextAction($fields, $parentfunc);
        return $fields;
    }
}
