<?php

use Phinx\Migration\AbstractMigration;

class StockImportFileTable extends AbstractMigration
{
    public function change()
    {
        $this->table("stockImportFiles", ['id' => false, 'collation' => 'utf8_general_ci'])
            ->addColumn('status', 'string')
            ->addColumn('type', 'string')
            ->addColumn('fileContents', 'text')
            ->addColumn('timestamp', 'integer')
            ->addColumn('initiatingItid', 'string')
            ->addColumn('id', 'string')
            ->addIndex(['id'])
            ->create();
    }
}
