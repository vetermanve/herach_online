<?php


namespace Storage\SearchModule;


interface SearchModuleInterface
{
    public function find($filters, $limit, $meta = []);
    public function findOne($filters, $meta = []);
}