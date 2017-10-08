<?php

namespace Statist\Stats;

use Statist\Config;
use Statist\FlowData;
use Statist\Graph;
use Statist\Transformer\AddToRelatedData;
use Statist\Transformer\ColorizePercent;
use Statist\Transformer\Diff;
use Statist\Transformer\DiffPercent;
use Statist\Transformer\TimeShiftDay;

class DailyBonus extends AbstractStats
{
    const F_PUSH_SENT      = 'daily_bonuis_push_sent';
    const F_SHOW           = 'daily_bonus_show';
    const F_VISIT          = 'daily_bonus_visit';
    const F_ANSWER         = 'daily_bonus_answer';
    const F_ANSWER_CORRECT = 'daily_bonus_correct';
    const F_MONEY_ADD      = 'money_add_daily_bonus';
    
    /* 
    1.1) - daily_bonuis_push_sent
2) Сколько человек видело Джинна - daily_bonus_show
3) Сколько человек на профиль - daily_bonus_visit
3) Сколько человек угадывало вопрос - daily_bonus_answer
4) Сколько человек ответили верно - daily_bonus_correct
5) Сколько кредитов получено - money_add_daily_bonus */
    
    public function getFields()
    {
        return [
            Main::F_DAU,
            self::F_PUSH_SENT,
            self::F_SHOW,
            self::F_VISIT,
            self::F_ANSWER,
            self::F_ANSWER_CORRECT,
            self::F_MONEY_ADD,
        ];
    }
    
    public function getViews() 
    {
        $format = $this->calcDayDiffUnique();
        $formatDiff =  $this->calсUniquePercentRatioOfTowValuesWithDayDiff();
        
        $view['mainLead'] =array(
            'title' => 'Качество работы',
            'fields' => array(
                [
                    'fields' => [Main::F_DAU],
                    'title' => 'DAU',
                    'hideOnGraph' => true,
                    'format' => $format
                ],
                [
                    'fields' => [self::F_PUSH_SENT, Main::F_DAU],
                    'title' => 'пушей к дау',
                    'hideOnGraph' => true,
                    'format' => $formatDiff
                ],
                [
                    'fields' => [self::F_SHOW, Main::F_DAU],
                    'title' => 'пoказы к дау',
                    'format' => $formatDiff
                ],
                [
                    'fields' => [self::F_VISIT, Main::F_DAU],
                    'title' => 'заходы в профиль к дау',
                    'format' => $formatDiff
                ],
                [
                    'fields' => [self::F_ANSWER, Main::F_DAU],
                    'title' => 'ответы к дау',
                    'format' => $formatDiff
                ],
                [
                    'fields' => [self::F_MONEY_ADD],
                    'title' => 'кредитов выдано',
                    'hideOnGraph' => true,
                    'format' => $this->calcDayDiffAbsolute()
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
        return 'Бонус Дня';
    }
    
    public function getId()
    {
        return Config::STATS_DAILY_BONUS;
    }
}
