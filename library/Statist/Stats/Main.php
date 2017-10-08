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
use Statist\Transformer\MinusRight;
use Statist\Transformer\Ratio;
use Statist\Transformer\RatioPercent;
use Statist\Transformer\TimeShiftDay;

class Main extends AbstractStats
{
    const F_DAU         = 'dau';
    const F_BROWSING    = 'browsing';
    const F_WAS_BROWSED = 'was_browsed';
    
    const F_VISIT         = 'visit';
    const F_VISITED       = 'visited';
    const F_QUIZ_ANSWER   = 'quiz_answer';
    const F_QUIZ_ANSWERED = 'quiz_answered';
    
    const F_QUIZ_FIRST_CONNECT   = 'first_connect_message';
    const F_QUIZ_FIRST_CONNECTED = 'first_connect_message_to';
    const F_FIRST_CONNECT_GIFT   = 'first_connect_gift';
    
    const F_USERS_CONNECT_1 = 'users_connect_1';
    const F_USERS_CONNECT_2 = 'users_connect_2';
    const F_USERS_CONNECT_3 = 'users_connect_3';
    const F_USERS_CONNECT_4 = 'users_connect_4';
    const F_USERS_CONNECT_5 = 'users_connect_5';
    
    const F_BROWSING_UNQ_PAIR = 'browsing_unq';

    const F_PUSH_BLOCKED = 'push_blocked';

    
    public function getFields()
    {
        return [
            self::F_DAU,
            self::F_BROWSING,
            self::F_WAS_BROWSED,
            self::F_VISIT,
            self::F_VISITED,
            
            self::F_QUIZ_ANSWER,
            self::F_QUIZ_ANSWERED,
    
            self::F_QUIZ_FIRST_CONNECT,
            self::F_QUIZ_FIRST_CONNECTED,
            self::F_FIRST_CONNECT_GIFT,
            self::F_BROWSING_UNQ_PAIR,
            self::F_PUSH_BLOCKED
        ];
    }
    
    
    public function getViews() 
    {
//        $class = $this;
        
        $view['dauGraph'] =array(
            'title' => 'DAU график',
            'fields' => array(
                [
                    'fields' => [self::F_DAU],
                    'title' => 'DAU',
                    'format' => $this->calcDayDiffUnique()
                ]
            ),
            'gr' => Graph::GR_LINE,
        );
        
//        $view['mainLead'] =array(
//            'title' => 'Статистика продукта',
//            'fields' => array(
//                [
//                    'fields' => [self::F_DAU],
//                    'title' => 'DAU',
//                    'hideOnGraph' => true,
//                    'format' => $this->calcDayDiffUnique()
//                ],
//                [
//                    'fields' => [self::F_BROWSING, self::F_DAU],
//                    'title' => 'листают фид',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
//                ],
//                [
//                    'fields' => [self::F_VISIT, self::F_DAU],
//                    'title' => 'смотрят профиль',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
//                ],
//                [
//                    'fields' => [self::F_QUIZ_ANSWER, self::F_DAU],
//                    'title' => 'угадывают',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
//                ],
//            ),
//            'gr' => Graph::GR_LINE,
//        );
//    
//        $view['firstConnect'] =array(
//            'title' => 'Первые взаимодействия - Уники к Дао',
//            'fields' => array(
//                [
//                    'fields' => [self::F_DAU],
//                    'title' => 'DAU',
//                    'hideOnGraph' => true,
//                    'format' => $this->calcDayDiffUnique()
//                ],
////                [
////                    'fields' => [self::F_BROWSING_UNQ_PAIR, self::F_DAU],
////                    'title' => 'уникальные пары показов',
////                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
////                ],
//                [
//                    'fields' => [self::F_QUIZ_FIRST_CONNECT, self::F_DAU],
//                    'title' => 'Первое угадыване к Дау',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
//                ],
//                [
//                    'fields' => [self::F_QUIZ_ANSWER, self::F_DAU],
//                    'title' => 'угадывают',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
//                ],
//            ),
//            'gr' => Graph::GR_LINE,
//        );
//    
//        $view['stepQuality'] = array(
//            'title' => 'Продукт - качество',
//            'fields' => array(
//                [
//                    'fields' => [self::F_DAU],
//                    'title' => 'DAU',
//                    'hideOnGraph' => true,
//                    'format' => $this->calcDayDiffUnique()
//                ],
//                [
//                    'fields' => [self::F_BROWSING],
//                    'title' => 'пролистываний ',
//                    'subTitle' => 'на уника',
//                    'format' => $this->calcRatioWithSelfUniqueWithDayDiff()
//                ],
//                [
//                    'fields' => [self::F_VISIT],
//                    'title' => 'просмотров профилей',
//                    'subTitle' => 'на уника',
//                    'format' => $this->calcRatioWithSelfUniqueWithDayDiff()
//                ],
//                [
//                    'fields' => [self::F_QUIZ_ANSWER],
//                    'title' => 'угадываний',
//                    'subTitle' => 'на уника',
//                    'format' => $this->calcRatioWithSelfUniqueWithDayDiff()
//                ],
//            ),
//            'gr' => Graph::GR_LINE,
//        );
//        
//        
//        $view['mainLead2'] =array(
//            'title' => 'Статистика Охват',
//            'fields' => array(
//                [
//                    'fields' => [self::F_DAU],
//                    'title' => 'DAU',
//                    'hideOnGraph' => true,
//                    'format' => $this->calcDayDiffUnique()
//                ],
//                [
//                    'fields' => [self::F_WAS_BROWSED, self::F_DAU],
//                    'title' => 'Уников показано в фиде',
//                    'subTitle' => 'процент от DAU',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiffAddPercents()
//                ],
//                [
//                    'fields' => [self::F_VISITED, self::F_DAU],
//                    'title' => 'Просмотрен профиль',
//                    'subTitle' => 'процент от DAU',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiffAddPercents()
//                ],
//                [
//                    'fields' => [self::F_QUIZ_ANSWERED, self::F_DAU],
//                    'title' => 'отправлена угадайка',
//                    'subTitle' => 'процент от DAU',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiffAddPercents()
//                ],
//            ),
//            'gr' => Graph::GR_LINE,
//        );
//    
//        $view['coverageQuality'] = array(
//            'title' => 'Качество Охвата',
//            'fields' => array(
//                [
//                    'fields' => [self::F_DAU],
//                    'title' => 'DAU',
//                    'hideOnGraph' => true,
//                    'format' => $this->calcDayDiffUnique()
//                ],
//                [
//                    'fields' => [self::F_WAS_BROWSED],
//                    'title' => 'показов уника cp.',
//                    'format' => $this->calcRatioWithSelfUniqueWithDayDiff()
//                ],
//                [
//                    'fields' => [self::F_VISITED],
//                    'title' => 'заходов на профиль ср.',
//                    'format' => $this->calcRatioWithSelfUniqueWithDayDiff()
//                ],
//                [
//                    'fields' => [self::F_QUIZ_ANSWERED],
//                    'title' => 'угадываний к уник ср.',
//                    'format' => $this->calcRatioWithSelfUniqueWithDayDiff()
//                ],
//            ),
//            'gr' => Graph::GR_LINE,
//        );
//    
//        $view['secondLead'] =array(
//            'title' => 'Продукт по шагам',
//            'fields' => array(
//                [
//                    'fields' => [self::F_DAU],
//                    'title' => 'DAU',
//                    'hideOnGraph' => true,
//                    'format' => $this->calcDayDiffUnique()
//                ],
//                [
//                    'fields' => [self::F_BROWSING, self::F_DAU],
//                    'title' => 'листают фид',
//                    'subTitle' => 'относительно DAU',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
//                ],
//                [
//                    'fields' => [self::F_VISIT, self::F_BROWSING],
//                    'title' => 'смотрят профиль',
//                    'subTitle' => 'относительно листающих фид',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
//                ],
//                [
//                    'fields' => [self::F_QUIZ_ANSWER, self::F_VISIT],
//                    'title' => 'угадывают',
//                    'subTitle' => 'относительно смотрящих профиль',
//                    'format' => $this->calсUniquePercentRatioOfTowValuesWithDayDiff()
//                ],
//            ),
//            'gr' => Graph::GR_LINE,
//        );
//        
//        $view['browsingResolution'] = array(
//            'title' => 'Распределение листания Мал. Шаг',
//            'type' => 'resolutionOne',
//            'fields' => array(
//                [
//                    'title' => 'листают фид',
//                    'subTitle' => 'относительно DAU',
//                    'applyAllFields' => 1,
//                    'fields' => function () use ($class) {
//                        return $class->getResolutionFieldsById('browsing_1to20by1');
//                    },
//                    'selfFormat' => 1,
//                    'format' => function (FlowData $flow) {
//                        return new MinusRight(new AddToRelatedData($flow, $flow));
////                        return new AddToRelatedData($flow, new ColorizePercent(new DiffPercent($flow, new TimeShiftDay($flow))));
//                    },
//                ],
//            ),
//            'gr' => Graph::GR_SPLINE,
//        );
//    
//        $view['browsingResolution2'] = array(
//            'title' => 'Распределение листания Бол. Шаг',
//            'type' => 'resolutionOne',
//            'fields' => array(
//                [
//                    'title' => 'листают фид',
//                    'subTitle' => 'относительно DAU',
//                    'applyAllFields' => 1,
//                    'fields' => function () use ($class) {
//                        return $class->getResolutionFieldsById('browsing_50to300by30');
//                    },
//                    'selfFormat' => 1,
//                    'format' => function (FlowData $flow) {
//                        return new MinusRight(new AddToRelatedData($flow, $flow));
////                        return new AddToRelatedData($flow, new ColorizePercent(new DiffPercent($flow, new TimeShiftDay($flow))));
//                    },
//                ],
//            ),
//            'gr' => Graph::GR_SPLINE,
//        );
        
        return $view;
    }
    
    public function loadGlobalDiggers()
    {
        return [Config::DIG_SEX];
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
    
    public function getResolutionConfig()
    {
        return [
            'browsing_1to20by1'    => [self::F_BROWSING, array_reverse(range(1, 20))],
            'browsing_50to300by30' => [self::F_BROWSING, array_reverse(array_merge([1,2,10],range(50, 320, 30)))]
        ];
    }
    
    
}
