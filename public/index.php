<?php

/* @var $core \Run\RunCore */
$core = require_once __DIR__.'/../run.php';

$core->configure();
$core->prepare();
$core->run();

