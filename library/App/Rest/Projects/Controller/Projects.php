<?php


namespace App\Rest\Projects\Controller;


use App\Rest\Projects\Storage\ProjectStorage;
use App\Rest\Run\RestControllerProto;
use Uuid\Uuid;

class Projects extends RestControllerProto
{
    public function get()
    {
        $count = $this->p('count', 9);
        
        if ($id = $this->p('id')) {
            $ids = [$id]; 
        } else {
            $ids = $this->p('ids');
        }
    
        $storage = new ProjectStorage();
        
        $filter = [];
        
        if ($ownerId = $this->p(ProjectStorage::F_OWNER_ID)) {
            $filter[] = [ProjectStorage::F_OWNER_ID, '=', $ownerId]; 
        }
        
        if ($filter || !$ids /* select all */) {
            if ($ids) {
                $filter[] = [ProjectStorage::ID, 'in', $ids];
            }
            
            $data = $storage->search()->find($filter, $count, __METHOD__, [
                'sort' => [[ProjectStorage::F_TITLE, 'asc']],
            ]);   
        } else {
            $data = $storage->read()->mGet($ids, __METHOD__, []);
        }
    
        return array_values($data);
    }
    
    public function post()
    {
        $id = $this->p(ProjectStorage::ID, null) ?: Uuid::v4();
        $currentUserId = $this->_getCurrentUserId();
        
        if (!$currentUserId) {
            throw new \Exception("Not authorised", 401);
        }
        
        $title = trim($this->p(ProjectStorage::F_TITLE, ''));
        if (strlen($title) < 3) {
            throw new \Exception("Bad title", 409);
        }
        
        $data = [
            ProjectStorage::F_TITLE => $title,
            ProjectStorage::F_DESC  => $this->p(ProjectStorage::F_DESC),
            ProjectStorage::F_OWNER_ID => $currentUserId,
        ];
        
        $storage = new ProjectStorage();
        $result  = $storage->write()->insert($id, $data, __METHOD__);
        
        return $result;
    }
    
    public function put () 
    {
        $id = $this->p(ProjectStorage::ID, null);
        if (!$id) {
            throw new \Exception("Id not passed", 409);
        }
        
        $currentUserId = $this->_getCurrentUserId();
    
        if (!$currentUserId) {
            throw new \Exception("Not authorised", 401);
        }
    
        $storage = new ProjectStorage();
        
        $data = $storage->read()->get($id, __METHOD__);
        if (!$data) {
            throw new \Exception("Project not found", 404);
        }
        
        if ($data[ProjectStorage::F_OWNER_ID] !== $currentUserId) {
            throw new \Exception("Only project owner can edit project", 403);
        }
    
        $title = trim($this->p(ProjectStorage::F_TITLE, ''));
        if (strlen($title) < 3) {
            throw new \Exception("Bad title", 409);
        }
    
        $data = [
            ProjectStorage::F_TITLE => $title,
            ProjectStorage::F_DESC  => $this->p(ProjectStorage::F_DESC),
            ProjectStorage::F_OWNER_ID => $currentUserId,
        ] + $data;
        
        $result = $storage->write()->update($id, $data, __METHOD__);
    
        return $result;
    }
    
}