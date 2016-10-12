<?php

use Phinx\Migration\AbstractMigration;

class SessionsMigration extends AbstractMigration
{
    /**
     * Change.
     */
    public function change()
    {
        $this->createSessionsTable();
    }

    /**
     * Migrate Up.
     */
    public function up()
    {

    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }

    private function createSessionsTable()
    {
        $table = $this->table('sessions', array('id' => false, 'primary_key' => 'session_id', 'collation' => 'utf8_general_ci'));
        $table->addColumn('session_id', 'string', array('limit' => 32))
            ->addColumn('session_data', 'text')
            ->addColumn('session_expiration', 'integer', array('limit' => 11))
            ->create();
    }
}
