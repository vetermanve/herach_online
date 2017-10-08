<?php


namespace Run\Execution\Rest\MsgModificator;


use Run\ChannelMessage\ChannelMsgProto;
use Run\RunRequest;

abstract class MsgModificatorProto 
{
    abstract public function process (RunRequest $request, ChannelMsgProto $message); 
}