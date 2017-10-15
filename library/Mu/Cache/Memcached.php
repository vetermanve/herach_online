<?php

namespace Mu\Cache;

use Mu;
use Mu\Exception;

/**
 * Class Memcached
 *
 * @package iConto\Cache
 */
class Memcached extends Mu\Cache
{

    /**
     * @var string
     */
    protected $_host = 'localhost';

    /**
     * @var int
     */
    protected $_port = 11211;

    /**
     * @var \Memcached
     */
    private $_instance = null;

    /**
     * @inheritdoc
     */
    protected function __construct($params)
    {
        $this->_instance = new \Memcached();

        if ($params['host']) {
            $this->_host = $params['host'];
        }
        if ($params['port']) {
            $this->_port = $params['port'];
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        try {
            return $this->_instance->get($key);
        } catch (Exception $e) {
            throw new Exception\InternalError("Memcached put exception. " . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function put($key, $value, array $params = [])
    {
        $exp = isset($params['expiration']) ? $params['expiration'] : null;
        $tags = isset($params['tags']) ? $params['tags'] : [];
        if ($tags) {
            $this->updateTagsReferences($key, (array)$params['tags']);
        }

        try {
            $this->_instance->set($key, $value, $exp);
        } catch (Exception $e) {
            throw new Exception\InternalError("Memcached put exception. " . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        return $this->_instance->delete($key);
    }

    /**
     * @inheritdoc
     */
    protected function _init()
    {
        try {
            $this->_instance->addServer($this->_host, $this->_port);
        } catch (Exception $e) {
            throw new Exception\InternalError("Memcached connect exception. " . $e->getMessage());
        }
    }
}
