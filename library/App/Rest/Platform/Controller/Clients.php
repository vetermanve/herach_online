<?php


namespace App\Rest\Platform\Controller;


use App\Rest\Platform\Lib\Clients\PlatformClientsStorage;
use App\Rest\Run\RestControllerProto;
use Uuid\Uuid;

class Clients extends RestControllerProto
{
    public function get () 
    {
        $limit = $this->p('limit', 100);
        
        $storage = new PlatformClientsStorage();
        
        if ($id = $this->p('id')) {
            return $storage->read()->get($id, __METHOD__); 
        }
        
        ///
        $filter = [];
        if ($lastActive = $this->p('last_active')) {
            $filter[] = [
                PlatformClientsStorage::ACTIVE, '>', time() - $lastActive 
            ];
        }
        
        $res = $storage->search()->find($filter, $limit, __METHOD__);
    
        return $res;
    }
    
    public function post()
    {
        $id = Uuid::v4();

        $type      = $this->p('type');
        $ownerId   = $this->p('owner_id', $this->_getCurrentUserId());
//        $ownerType = $this->p('owner_type', 'user');
        $address   = $this->p('address', '');
        $key       = $this->p('key', '');
//        $salt      = $this->p('salt');
        $version   = $this->p('version', 0);
        $features  = $this->p('features', []);
        
//        if (is_null($salt)) {
        $salt = Uuid::v4() . '.' . Uuid::v4() . '.' . Uuid::v4();
//        }
        
        $bind = [
            PlatformClientsStorage::ID         => $id,
            PlatformClientsStorage::TYPE       => $type,
            PlatformClientsStorage::OWNER_ID   => $ownerId,
//            PlatformClientsStorage::OWNER_TYPE => $ownerType,
            PlatformClientsStorage::ADDRESS    => $address,
            PlatformClientsStorage::KEY        => $key,
            PlatformClientsStorage::SALT       => $salt,
            PlatformClientsStorage::ADDRESS    => $address,
            PlatformClientsStorage::VERSION    => $version,
            PlatformClientsStorage::FEATURES   => $features,
            PlatformClientsStorage::ACTIVE     => time(),
        ];
        
        $storage = new PlatformClientsStorage();
        
        $res = $storage->write()->insert($id, $bind, __METHOD__);
    
        $this->setState('device_id', $id);
        
        return $res;
    }
    
    public function put () 
    {
        $id      = $this->p('id');
        $ownerId   = $this->p('owner_id', $this->_getCurrentUserId());
//        $ownerType = $this->p('owner_type', 'user');
        $address   = $this->p('address', '');
//        $key       = $this->p('key', '');
        $salt      = $this->p('salt');
        $version   = $this->p('version', 0);
        $features  = $this->p('features', []);
    
        $storage = new PlatformClientsStorage();
        
        $res = $storage->read()->get($id, __METHOD__);
        if (!$res) {
            throw new \Exception("Device not found", 404);
        }
        
        if ($res['salt'] !== $salt) {
            throw new \Exception("Forbidden", 403);
        }
    
        $bind = [
            PlatformClientsStorage::ID         => $id,
            PlatformClientsStorage::OWNER_ID   => $ownerId,
            PlatformClientsStorage::ADDRESS    => $address,
            PlatformClientsStorage::VERSION    => $version,
            PlatformClientsStorage::FEATURES   => $features,
            PlatformClientsStorage::ACTIVE     => time(),
        ] + $res;
        
        $res = $storage->write()->update($id, $bind, __METHOD__);
        
        $this->setState('device_id', $id);
        
        return $res;
    }
}