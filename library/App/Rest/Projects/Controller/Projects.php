<?php


namespace App\Rest\Projects\Controller;


use App\Rest\Projects\Storage\ProjectStorage;
use App\Rest\Run\RestControllerProto;
use Uuid\Uuid;

class Projects extends RestControllerProto
{
    public function get()
    {
        $count = $this->p('count', 1);
        
        if ($id = $this->p('id')) {
            $ids = [$id]; 
        } else {
            $ids = $this->p('ids');
        }
    
        $storage = new ProjectStorage();
        
        $filter = [];
        
        if ($ownerId = $this->p(ProjectStorage::F_OWNER_ID)) {
            $filter[ProjectStorage::F_OWNER_ID] = $ownerId; 
        }
        
        if ($ids) {
            $data = $storage->read()->mGet($ids, __METHOD__, []);
        } else {
            $data = $storage->search()->find($filter, $count, __METHOD__);
        }
    
        return array_values($data);
    }
    
    public function post()
    {
        $id = $this->p(ProjectStorage::ID, null) ?? Uuid::v4();
        $currentUserId = $this->_getCurrentUserId();
        
        if (!$currentUserId) {
            throw new \Exception("Not authorised", 401);
        }
        
        $data = [
            ProjectStorage::F_TITLE => $this->p(ProjectStorage::F_TITLE),
            ProjectStorage::F_DESC  => $this->p(ProjectStorage::F_DESC),
            ProjectStorage::F_OWNER_ID => $currentUserId,
        ];
        
        $storage = new ProjectStorage();
        $result  = $storage->write()->insert($id, $data, __METHOD__);
        
        return $result;
    }
    
}