<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class LargeStockImportFiles extends AbstractOnlineSchemaChange
{
    const TABLE = 'stockImportFiles';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange(static::TABLE, 'DROP INDEX id, ADD PRIMARY KEY (id), MODIFY COLUMN fileContents LONGTEXT');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange(static::TABLE, 'MODIFY COLUMN fileContents TEXT');
    }
}