<?php

use Phinx\Migration\AbstractMigration;

class UpdateStockImportFields extends AbstractMigration
{
    const TABLE = 'stockImportFiles';

    public function up()
    {
        $this->table(static::TABLE)
            ->renameColumn('type', 'updateOption')
            ->removeColumn('status')
            ->update();
    }

    public function down()
    {
        $this->table(static::TABLE)
            ->renameColumn('updateOption', 'type')
            ->addColumn('status', 'string')
            ->update();
    }
}
