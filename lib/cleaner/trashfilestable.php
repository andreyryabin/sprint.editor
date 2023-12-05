<?php

namespace Sprint\Editor\Cleaner;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;

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
            `id` INT NOT NULL AUTO_INCREMENT,
            `file_id` INT NOT NULL,
            `exists` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id), UNIQUE KEY (file_id)
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

    public function insertFilesToTable(array $ids, int $exists)
    {
        if (empty($ids)) {
            return 0;
        }

        $connection = Application::getConnection();

        $exists = $exists ? 1 : 0;

        $values = [];
        foreach ($ids as $id) {
            $values[] = '("' . intval($id) . '","' . $exists . '")';
        }
        $values = implode(',', $values);

        if ($exists) {
            $update = '`exists`="' . $exists . '"';
        } else {
            $update = '`exists`=`exists`';
        }

        $sql = <<<SQL
        INSERT INTO `sprint_editor_trash_files` (`file_id`,`exists`) 
        VALUES $values ON DUPLICATE KEY UPDATE $update;
SQL;

        $connection->query($sql);

        //return $connection->getAffectedRowsCount();
        return count($ids);
    }

    public function getTrashList(int $pageNum, int $limit)
    {
        $connection = Application::getConnection();

        $offset = ($pageNum - 1) * $limit;

        $sql = <<<SQL
        SELECT * FROM `sprint_editor_trash_files` WHERE `exists`="0" LIMIT $limit OFFSET $offset;
SQL;
        return $connection->query($sql);
    }

    public function delete(int $id)
    {
        $connection = Application::getConnection();

        $sql = <<<SQL
        DELETE FROM `sprint_editor_trash_files` WHERE `id`="$id";
SQL;

        $connection->query($sql);

        return $connection->getAffectedRowsCount();
    }

    public function cleanExists()
    {
        $connection = Application::getConnection();

        $sql = <<<SQL
        DELETE FROM `sprint_editor_trash_files` WHERE `exists`="1";
SQL;

        $connection->query($sql);

        return $connection->getAffectedRowsCount();
    }
}



