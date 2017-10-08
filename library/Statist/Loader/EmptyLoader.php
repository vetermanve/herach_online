<?php

namespace Statist\Loader;

class EmptyLoader extends AbstractLoader {
    
    function doLoad($keys)
    {
        trigger_error('Empty loader data call');
        return [];
    }
}
