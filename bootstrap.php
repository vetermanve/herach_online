<?php

chdir(__DIR__);

include __DIR__.'/vendor/autoload.php';

spl_autoload_register(['iConto\Autoload', 'autoload']);
