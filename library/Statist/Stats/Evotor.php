<?php
/**
 * Created by PhpStorm.
 * User: alexander
 * Date: 16.03.17
 * Time: 14:47
 */

namespace Statist\Stats;


use App\Evotor\Lib\Register\ScenarioLib;
use Statist\Config;

class Evotor extends AbstractStats
{
    const F_CHECK_LOYALITY_STATE        = 'check_loyality_state';
    const F_VERIFY_DISCOUNTCARD_CONNECT = 'verify_discountcard_connect';
    const F_DISCOUNTCARD_CONNECT        = 'discountcard_connect';
    const F_GET_COMPANY_DISCOUNTCARD    = 'get_company_discountcard';

    public static $scenarios = [
        ScenarioLib::SCENARIO_CHECK_LOYALTY_STATE         => self::F_CHECK_LOYALITY_STATE,
        ScenarioLib::SCENARIO_VERIFY_DISCOUNTCARD_CONNECT => self::F_VERIFY_DISCOUNTCARD_CONNECT,
        ScenarioLib::SCENARIO_DISCOUNTCARD_CONNECT        => self::F_DISCOUNTCARD_CONNECT,
    ];

    public function getId()
    {
        return Config::STATS_MAIN;
    }
    
    public function getName()
    {
        return 'Эвотор';
    }

    protected function loadGlobalDiggers()
    {
        return [];
    }

    public function loadSpecificDiggers ()
    {
        return [];
    }

    public function hasUnique()
    {
        return false;
    }
    
    

}