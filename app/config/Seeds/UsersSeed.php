<?php
declare(strict_types=1);

use Cake\Auth\DefaultPasswordHasher;
use Cake\I18n\FrozenTime;
use Migrations\AbstractSeed;

/**
 * Users seed.
 */
class UsersSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run(): void
    {
        $userData = [
            [
                'email' => 'user1@mail.com',
                'password' => (new DefaultPasswordHasher())->hash('password1'),
                'created_at' => FrozenTime::now()->format('Y-m-d H:i:s'),
                'updated_at' => FrozenTime::now()->format('Y-m-d H:i:s')
            ],
            [
                'email' => 'user2@mail.com',
                'password' => (new DefaultPasswordHasher())->hash('password2'),
                'created_at' => FrozenTime::now()->format('Y-m-d H:i:s'),
                'updated_at' => FrozenTime::now()->format('Y-m-d H:i:s')
            ]
        ];
    
        $table = $this->table('users');
        foreach ($userData as $data) {
            $table->insert($data)->save();
        }
    }
}
