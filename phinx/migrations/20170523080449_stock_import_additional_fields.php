<?php

use Phinx\Migration\AbstractMigration;

class StockImportAdditionalFields extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
     */
    public function change()
    {

        $table = $this->table('stockImportFiles');

        $table
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('userId', 'integer')
            ->update();
    }

}
