<?php

namespace Statist\Digger;

use iConto\Service\Container;
use Statist\Config;

class Operator extends AbstractDigger {
    
    private $operators;
    private $operatorsIdsIdx;
    
    public function digData()
    {
        if (!$this->companyId) {
            $this->result = [];         
        }
        
        $operatorsIdsIdx = $this->getOperatorsIdsIdx();
        
        foreach ($this->ids as $id) {
            if (isset($operatorsIdsIdx[$id]) || $id == 0) {
                $this->result[$id] = $id;
            }
        }
    }
    
    public function getId()
    {
        return Config::DIG_OPERATOR;
    }
    
    public function getDataRange()
    {
        return $this->getOperators();
    }
    
    public function getOperators () 
    {
        //проверка на то, что companyId действительно имеет операторов
        if ($this->operators === null && $operatorsIdsIdx = array_values($this->getOperatorsIdsIdx())) {
            $users = $this->getUserService()->getUsersByIds($operatorsIdsIdx);
            $users = $users ? $users->asArray() : [];
        
            foreach ($users as $user) {
                $this->operators[$user['id']] = $user['nickname'] 
                    ? $user['nickname'] 
                    : 'Оператор #'.$user['id'];
            }
        
            $this->operators[0] = 'Без оператора';
        }
        
        return $this->operators;
    }
    
    public function getOperatorsIdsIdx () 
    {
        if ($this->operatorsIdsIdx === null) {
            $contacts              = $this->getCompanyService()->getCompanyContacts($this->companyId);
            $contactUserIds        = is_object($contacts) ? array_filter(array_column($contacts->asArray(), 'user_id')) : [];
            $this->operatorsIdsIdx = array_combine($contactUserIds, $contactUserIds);    
        }
        
        return $this->operatorsIdsIdx;
    }
    
    public function getName()
    {
        return 'По оператору';
    }
}