<?php

use Phoenix\Migration\AbstractMigration;

class Initialization extends AbstractMigration
{
    protected function up(): void
    {
        $this->table('slimapp_users', 'id')
            ->setCharset('utf8')
            ->setCollation('utf8_general_ci')
            ->addColumn('id', 'biginteger', ['autoincrement' => true])
            ->addColumn('username', 'string', ['default' => '', 'length' => 50])
            ->addColumn('password', 'string', ['default' => '', 'length' => 250])
            ->addColumn('firstname', 'string', ['default' => '', 'length' => 50])
            ->addColumn('lastname', 'string', ['default' => '', 'length' => 50])
            ->addColumn('email', 'string', ['default' => '', 'length' => 80])
            ->create();
    }

    protected function down(): void
    {
        $this->table('slimapp_users')
            ->drop();
    }
}
