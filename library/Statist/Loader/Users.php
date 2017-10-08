<?php

namespace Statist\Loader;

class Users extends AbstractLoader {
    
    function doLoad($keys)
    {
        $users  = $keys ? \Yii::app()->db->createCommand("SELECT * FROM users where id IN (".implode(',', $keys).")")->queryAll() : [];
        
        if($users) {
            $users = array_combine(array_column($users, 'id'), $users);
        }
        
        return $users;
    }
}
