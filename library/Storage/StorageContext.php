<?php


namespace Storage;


use Modular\ModularContextProto;

class StorageContext extends ModularContextProto
{
    const UNIVERSE_MODULE     = 'module';
    const UNIVERSE_CONTROLLER = 'controller';
    const UNIVERSE_MODEL      = 'model';
    const RPC_SERVICE         = 'service';
    const RPC_TYPE            = 'type';
    
    const RPC_TIMEOUT = 'timeout';
}