<?php


namespace Storage;


use Modular\ModularContextProto;

class StorageContext extends ModularContextProto
{
    const RESOURCE = 'resource';
    const DATABASE = 'database';
    
    const UNIVERSE_MODULE     = 'module';
    const UNIVERSE_CONTROLLER = 'controller';
    const UNIVERSE_MODEL      = 'model';
    const RPC_SERVICE         = 'service';
    const RPC_TYPE            = 'type';
    
    const TIMEOUT = 'timeout';
}