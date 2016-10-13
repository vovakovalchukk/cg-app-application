<?php

use Phinx\Migration\AbstractMigration;

class RemoveOutOfDateSessions extends AbstractMigration
{
   public function up()
    {
        $this->dropTable('sessions');
    }

    public function down()
    {
        $this->createSessionsTable();
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
