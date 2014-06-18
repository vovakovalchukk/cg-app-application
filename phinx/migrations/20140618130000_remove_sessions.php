<?php

use Phinx\Migration\AbstractMigration;

class RemoveSessions extends AbstractMigration
{

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('sessions');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('sessions', array('id' => false, 'primary_key' => 'session_id'));
        $table->addColumn('session_id', 'string', array('limit' => 32))
            ->addColumn('session_data', 'text')
            ->addColumn('session_expiration', 'integer', array('limit' => 11))
            ->create();
    }
}