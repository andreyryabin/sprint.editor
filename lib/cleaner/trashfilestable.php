<?php

namespace Sprint\Editor\Cleaner;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use CFile;

class TrashFilesTable
{
    /**
     * @throws SqlQueryException
     */
    public function createTable()
    {
        $connection = Application::getConnection();
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS `sprint_editor_trash_files`(
            `ID` int(18) NOT NULL AUTO_INCREMENT,
            `FILE_ID` int(18) NOT NULL,
            `EXISTS` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (ID), UNIQUE KEY (FILE_ID)
        )ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;
SQL;
        $connection->query($sql);
        $connection->query('TRUNCATE TABLE `sprint_editor_trash_files`');
    }

    public function dropTable()
    {
        $connection = Application::getConnection();

        $connection->query('DROP TABLE IF EXISTS `sprint_editor_trash_files`;');
    }

    public function copyFilesFromBitrix(int $limit, int $offset)
    {
        $connection = Application::getConnection();

        $sql = <<<SQL
            SELECT `ID` FROM `b_file` 
            WHERE `MODULE_ID`="sprint.editor"
            LIMIT $limit OFFSET $offset;
SQL;
        $dbres = $connection->query($sql);

        $values = [];
        $filesCnt = 0;
        while ($row = $dbres->fetch()) {
            $values[] = '(' .
                        '"' . $this->forSql($row['ID']) . '",' .
                        '"0")';
            $filesCnt++;
        }
        $values = implode(',', $values);

        if ($filesCnt > 0) {
            $sql = <<<SQL
        INSERT INTO `sprint_editor_trash_files` 
            (`FILE_ID`, `EXISTS`) 
        VALUES $values ON DUPLICATE KEY UPDATE `EXISTS`=`EXISTS`;
SQL;

            $connection->query($sql);
        }

        return $filesCnt;
    }

    public function copyFilesFromEditor(array $fileIds)
    {
        if (empty($fileIds)) {
            return 0;
        }

        $connection = Application::getConnection();

        $values = [];
        foreach ($fileIds as $fileId) {
            $values[] = '(' .
                        '"' . intval($fileId) . '",' .
                        '"1")';
        }
        $values = implode(',', $values);

        $sql = <<<SQL
        INSERT INTO `sprint_editor_trash_files` (`FILE_ID`,`EXISTS`) 
        VALUES $values ON DUPLICATE KEY UPDATE `EXISTS`="1";
SQL;

        $connection->query($sql);

        return count($fileIds);
    }

    public function getTrashFilesCount()
    {
        $connection = Application::getConnection();

        $sql = <<<SQL
        SELECT COUNT(`ID`) cnt FROM `sprint_editor_trash_files` WHERE `EXISTS`="0";
SQL;

        $item = $connection->query($sql)->fetch();

        return $item['cnt'] ?? 0;
    }

    public function cleanTrashByStep(int $limit)
    {
        $connection = Application::getConnection();

        $sql = <<<SQL
        SELECT `ID`, `FILE_ID` FROM `sprint_editor_trash_files` WHERE `EXISTS`="0" LIMIT $limit;
SQL;
        $dbres = $connection->query($sql);

        $ids = [];
        while ($item = $dbres->fetch()) {
            CFile::Delete($item['FILE_ID']);
            $ids[] = $item['ID'];
        }

        return $this->deleteIds($ids);
    }

    public function cleanExists(): int
    {
        $connection = Application::getConnection();

        $sql = <<<SQL
        DELETE FROM `sprint_editor_trash_files` WHERE `EXISTS`="1";
SQL;

        $connection->query($sql);

        return $connection->getAffectedRowsCount();
    }

    protected function deleteIds(array $fileIds)
    {
        if (empty($fileIds)) {
            return 0;
        }

        $connection = Application::getConnection();

        $fileIds = '(' . implode(',', $fileIds) . ')';

        $sql = <<<SQL
        DELETE FROM `sprint_editor_trash_files` WHERE `ID` IN $fileIds;
SQL;

        $connection->query($sql);

        return $connection->getAffectedRowsCount();
    }

    protected function forSql($value): string
    {
        return Application::getConnection()->getSqlHelper()->forSql($value);
    }
}



