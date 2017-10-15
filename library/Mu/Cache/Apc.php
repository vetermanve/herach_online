<?php

namespace Mu\Cache;
use Mu\Exception;

/**
 * Class Apc
 *
 * @package iConto\Cache
 */
class Apc extends \Mu\Cache
{

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (!function_exists('apc_fetch')) {
            return null;
        }
        
        return apc_fetch($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param array $params
     * @throws
     */
    public function put($key, $value, array $params = [])
    {
        if (!function_exists('apc_add')) {
            return;
        }
        
        $exp = isset($params['expiration']) ? $params['expiration'] : 0;
        $tags = isset($params['tags']) ? $params['tags'] : [];
        if ($tags) {
            $this->updateTagsReferences($key, (array)$params['tags']);
        }

        try {
            apc_add($key, $value, $exp);
        } catch (Exception $e) {
            throw new Exception\InternalError("Memcached put exception. " . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        return apc_delete($key);
    }

    protected function _init()
    {
    }
}
