<?php

namespace Statist\Stats;

use Statist\Config;
use Statist\FlowData;
use Statist\Graph;
use Statist\Transformer\AddText;
use Statist\Transformer\AddToRelatedData;
use Statist\Transformer\ColorizePercent;
use Statist\Transformer\Concat;
use Statist\Transformer\Diff;
use Statist\Transformer\DiffPercent;
use Statist\Transformer\ExtractUnq;
use Statist\Transformer\Ratio;
use Statist\Transformer\RatioPercent;
use Statist\Transformer\TimeShiftDay;

class Connections extends AbstractStats
{
    const F_DAU         = Main::F_DAU;
    
    const F_FIRST_CONNECT_GIFT = 'first_connect_gift';
    
    const F_USERS_CONNECT_1 = 'users_connect_1';
    const F_USERS_CONNECT_2 = 'users_connect_2';
    const F_USERS_CONNECT_3 = 'users_connect_3';
    const F_USERS_CONNECT_4 = 'users_connect_4';
    const F_USERS_CONNECT_5 = 'users_connect_5';
    
    const F_BROWSING_UNQ_PAIR = 'browsing_unq';
    
    public function getFields()
    {
        return [
            self::F_DAU,
            
            self::F_FIRST_CONNECT_GIFT,
            self::F_BROWSING_UNQ_PAIR
        ];
    }
    
    public function getViews() 
    {
        $view['mainLead'] =array(
            'title' => 'Статистика продукта',
            'fields' => array(
                [
                    'fields' => [self::F_DAU],
                    'title' => 'DAU',
                    'hideOnGraph' => true,
                    'format' => $this->calcDayDiffUnique()
                ],
                [
                    'fields' => [self::F_BROWSING, self::F_DAU],
                    'title' => 'листают фид',
                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
                ],
                [
                    'fields' => [self::F_VISIT, self::F_DAU],
                    'title' => 'смотрят профиль',
                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
                ],
                [
                    'fields' => [self::F_QUIZ_ANSWER, self::F_DAU],
                    'title' => 'угадывают',
                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
                ],
            ),
            'gr' => Graph::GR_LINE,
        );
    
        $view['firstConnect'] =array(
            'title' => 'Первые взаимодействия - Уники к Дао',
            'fields' => array(
                [
                    'fields' => [self::F_DAU],
                    'title' => 'DAU',
                    'hideOnGraph' => true,
                    'format' => $this->calcDayDiffUnique()
                ],
//                [
//                    'fields' => [self::F_BROWSING_UNQ_PAIR, self::F_DAU],
//                    'title' => 'уникальные пары показов',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
//                ],
                [
                    'fields' => [self::F_QUIZ_FIRST_CONNECT, self::F_DAU],
                    'title' => 'Первое угадыване к Дау',
                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
                ],
                [
                    'fields' => [self::F_QUIZ_ANSWER, self::F_DAU],
                    'title' => 'угадывают',
                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
                ],
            ),
            'gr' => Graph::GR_LINE,
        );
        
        return $view;
    }
    
    public function loadGlobalDiggers()
    {
        return [
            Config::DIG_SEX,
            Config::DIG_TRAFFIC_SOURCE
        ];
    }
    
    public function hasUnique()
    {
        return true;
    }
    
    public function getName()
    {
        return 'Общая';
    }
    
    public function getId()
    {
        return Config::STATS_MAIN;
    }
    
    public function getDefaultView ()
    {
        return function ($data) {
            return new AddToRelatedData($data, new Concat(new ColorizePercent(new DiffPercent($data, new TimeShiftDay($data))), new ExtractUnq($data)));
        };
    }
}
