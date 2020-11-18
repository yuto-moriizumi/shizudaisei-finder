<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateFriendships extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('friendships');
        $table->addColumn('from_id', 'string', [
            'default' => null,
            'limit' => 127,
            'null' => false,
        ]);
        $table->addColumn('to_id', 'string', [
            'default' => null,
            'limit' => 127,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->create();
    }
}
