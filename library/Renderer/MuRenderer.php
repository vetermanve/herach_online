<?php

namespace Renderer;

interface MuRenderer
{
    public function render (string $templateName, array $data); 
}