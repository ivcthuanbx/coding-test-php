<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class ArticleLikesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Define table name
        $this->setTable('article_likes');

        // Define associations
        $this->belongsTo('Users');
        $this->belongsTo('Articles');
    }
}
