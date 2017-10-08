<?php


namespace Modular;


interface ModularStrategyInterface extends ModularSystemModule
{
    public function prepare();
    public function run();
    public function shouldProcess();
}