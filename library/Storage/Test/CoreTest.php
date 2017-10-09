<?php


namespace Storage\Test;

use Storage\SimpleStorage;
use Testing\TestBase;

include_once __DIR__.'/../../Testing/bootstrap.php';

class CoreTest extends TestBase
{
    public function testStorage () 
    {
        $storage = new SimpleStorage();
        $storage->setController('Like');
        $storage->setModule('Like');
        $storage->setName('likes-log');
        
        $res = $storage->getDataAdapter()->find([
            'user_id' => 644, 
        ])->fetch();
        
    }
}