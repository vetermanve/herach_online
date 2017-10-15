<?php

namespace Statist\Stats;

use Mu\Model\CompanyReview;
use Statist\Config;
use Statist\Digger\Operator;
use Statist\FlowData;
use Statist\Graph;
use Statist\Transformer\AddToRelatedData;
use Statist\Transformer\ColorizePercent;
use Statist\Transformer\DiffPercent;
use Statist\Transformer\ExtractUnq;
use Statist\Transformer\Math;
use Statist\Transformer\Ratio;
use Statist\Transformer\TimeShiftDay;

class Chats extends AbstractStats
{
    const CT_OPERATOR = 'op';
    
    const CHAT_TYPE_MAIN = 0;
    
    const F_NEW_MSG_FROM_USER     = 'msg_from_user';
    const F_NEW_MSG_FROM_MERCHANT = 'msg_from_merch';
    
    const F_LIKE   = 'chat_like';
    const F_LIKE_GLAD   = 'chat_like_in_glad';
    const F_LIKE_UNGLAD = 'chat_like_in_unglad';
    const F_LIKE_IDEA   = 'chat_like_in_idea';
    
    const F_DISLIKE = 'chat_dislike';
    const F_DISLIKE_GLAD   = 'chat_dislike_in_glad';
    const F_DISLIKE_UNGLAD = 'chat_dislike_in_unglad';
    const F_DISLIKE_IDEA   = 'chat_dislike_in_idea';
    
    const F_REVIEW_CREATE = 'new_review_room';
    const F_REVIEW_CREATE_GLAD   = 'new_review_room_glad';
    const F_REVIEW_CREATE_UNGLAD = 'new_review_room_unglad';
    const F_REVIEW_CREATE_IDEA   = 'new_review_room_idea';
    
    const F_ROOM_GET = 'room_get';
    const F_ROOM_GET_MAIN = 'room_get_main';
    const F_ROOM_GET_GLAD = 'room_get_glad';
    const F_ROOM_GET_UNGLAD = 'room_get_unglad';
    const F_ROOM_GET_IDEA = 'room_get_idea';
    
    const F_REVIEW_RESOLVE = 'review_resolve';
    const F_REVIEW_RESOLVE_GLAD   = 'review_resolve_glad';
    const F_REVIEW_RESOLVE_UNGLAD = 'review_resolve_unglad';
    const F_REVIEW_RESOLVE_IDEA   = 'review_resolve_idea';
    
    const F_PROCESSING_CLOSING   = 'chat_processing_count';
    
    const F_ROOM_TIME        = 'cht_proc_time';
    const F_ROOM_TIME_GLAD   = 'cht_proc_time_gald';
    const F_ROOM_TIME_UNGLAD = 'cht_proc_time_ungald';
    const F_ROOM_TIME_IDEA   = 'cht_proc_time_idea';
    const F_ROOM_TIME_MAIN   = 'cht_proc_time_main';
    
    const F_ROOM_CLOSING        = 'cht_proc_closing';
    const F_ROOM_CLOSING_GLAD   = 'cht_proc_closing_gald';
    const F_ROOM_CLOSING_UNGLAD = 'cht_proc_closing_ungald';
    const F_ROOM_CLOSING_IDEA   = 'cht_proc_closing_idea';
    const F_ROOM_CLOSING_MAIN   = 'cht_proc_closing_main';
    
    public function getViews()
    {
        $simpleFormat = $this->getDefaultView();
        $uniqueAndTimeShiftFormat = $this->getUniqueAndTimeShiftView();

        $formatAvgTime = function (FlowData $timeFlow, FlowData $countFlow) {
            $ratioSec = new Ratio($timeFlow, $countFlow);
            $flow = new Math($ratioSec, Math::DO_DIVISION, 60);
        
            return new AddToRelatedData($flow, new ColorizePercent(new DiffPercent($flow, new TimeShiftDay($flow))));
        };
        
        
        // Общее состояние по компании
        $view['chatOverview'] = array(
            'title' => 'Обзор сообщения/отзывы',
            'fields' => array(
                [
                    'fields' => [self::F_NEW_MSG_FROM_MERCHANT],
                    'title' => 'Новые сообщения от Компании',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_NEW_MSG_FROM_MERCHANT],
                    'title' => 'Уникальные',
                    'hideOnGraph' => true,
                    'format' => $uniqueAndTimeShiftFormat
                ],
                [
                    'fields' => [self::F_NEW_MSG_FROM_USER],
                    'title' => 'Уникальные',
                    'hideOnGraph' => true,
                    'format' => $uniqueAndTimeShiftFormat
                ],
                [
                    'fields' => [self::F_NEW_MSG_FROM_USER],
                    'title' => 'Новые сообщения от Пользователей',
                    'format' => $simpleFormat
                ],

                [
                    'fields' => [self::F_ROOM_CLOSING],
                    'title' => 'Обработано обращений',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_ROOM_TIME, self::F_ROOM_CLOSING],
                    'title' => 'Cр. время обработки (мин)',
                    'format' => $formatAvgTime
                ],
                [
                    'fields' => [self::F_REVIEW_CREATE],
                    'title' => 'Новых отзывов (Довольки и пр.)',
                    'format' => $simpleFormat
                ],
            ),
            'gr' => Graph::GR_LINE,
        );
    
        $view['newRooms'] = array(
            'title' => 'Входящие отзывы по типам',
            'fields' => array(
                [
                    'fields' => [self::F_REVIEW_CREATE_GLAD],
                    'title' => 'Новые Довольки',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_REVIEW_CREATE_UNGLAD],
                    'title' => 'Новые Печальки',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_REVIEW_CREATE_IDEA],
                    'title' => 'Новые Хотелки',
                    'format' => $simpleFormat
                ],
            ),
            'gr' => Graph::GR_LINE,
        );
    
        // отдельно по типам
        $view['glads'] =array(
            'title' => 'Довольки',
            'fields' => array(
                [
                    'fields' => [self::F_REVIEW_CREATE_GLAD],
                    'title' => 'Новые',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_REVIEW_RESOLVE_GLAD],
                    'title' => 'Закрытия',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_LIKE_GLAD],
                    'title' => 'Лайки',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_DISLIKE_GLAD],
                    'title' => 'Дислайки',
                    'format' => $simpleFormat
                ],
            ),
            'gr' => Graph::GR_LINE,
        );
    
        $view['unGlads'] =array(
            'title' => 'Печальки',
            'fields' => array(
                [
                    'fields' => [self::F_REVIEW_CREATE_UNGLAD],
                    'title' => 'Новые',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_REVIEW_RESOLVE_UNGLAD],
                    'title' => 'Закрытия',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_LIKE_UNGLAD],
                    'title' => 'Лайки',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_DISLIKE_UNGLAD],
                    'title' => 'Дислайки',
                    'format' => $simpleFormat
                ],
            ),
            'gr' => Graph::GR_LINE,
        );
    
        $view['ideas'] =array(
            'title' => 'Хотелки',
            'fields' => array(
                [
                    'fields' => [self::F_REVIEW_CREATE_IDEA],
                    'title' => 'Новые',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_REVIEW_RESOLVE_IDEA],
                    'title' => 'Закрытия',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_LIKE_IDEA],
                    'title' => 'Лайки',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_DISLIKE_IDEA],
                    'title' => 'Дислайки',
                    'format' => $simpleFormat
                ],
            ),
            'gr' => Graph::GR_LINE,
        );
    
        $view['chatMarks'] =array(
            'title' => 'Оценки по отзывам',
            'fields' => array(
                [
                    'fields' => [self::F_LIKE_GLAD],
                    'title' => 'Лайки в Довольках',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_LIKE_UNGLAD],
                    'title' => 'Лайки в Печальках',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_LIKE_IDEA],
                    'title' => 'Лайки в Хотелках',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_DISLIKE_GLAD],
                    'title' => 'Дислайки в Довольках',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_DISLIKE_UNGLAD],
                    'title' => 'Дислайки в Печальках',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_DISLIKE_IDEA],
                    'title' => 'Дислайки в Хотелках',
                    'format' => $simpleFormat
                ],
                
            ),
            'gr' => Graph::GR_LINE,
        );
    
        $view['newMsg'] =array(
            'title' => 'Новые сообщения',
            'fields' => array(
                [
                    'fields' => [self::F_NEW_MSG_FROM_MERCHANT],
                    'title' => 'Новые сообщения от Компании',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_NEW_MSG_FROM_USER],
                    'title' => 'Новые сообщения от Пользователей',
                    'format' => $simpleFormat
                ],
            ),
            'gr' => Graph::GR_LINE,
        );
//    
        
        $view['chatProcessing'] = array(
            'title'  => 'Обработка чатов',
            'fields' => array(
                [
                    'fields' => [self::F_ROOM_CLOSING_MAIN],
                    'title'  => 'Обработано Основных чатов',
                    'format' => $simpleFormat
                ],
                [
                    'fields' => [self::F_ROOM_CLOSING_UNGLAD],
                    'title'  => 'Обработано Печалек',
                    'format' => $simpleFormat,
                ],
                [
                    'fields' => [self::F_ROOM_CLOSING_GLAD],
                    'title'  => 'Обработано Доволек',
                    'format' => $simpleFormat,
                ],
                [
                    'fields' => [self::F_ROOM_CLOSING_IDEA],
                    'title'  => 'Обработано Хотелок',
                    'format' => $simpleFormat,
                ],
                
                [
                    'fields' => [self::F_ROOM_TIME_MAIN, self::F_ROOM_CLOSING_MAIN],
                    'title'  => 'Ср. время Основные чаты',
                    'format' => $formatAvgTime
                ],
                [
                    'fields' => [self::F_ROOM_TIME_UNGLAD, self::F_ROOM_CLOSING_UNGLAD],
                    'title'  => 'Ср. время Печальки',
                    'format' => $formatAvgTime,
                ],
                [
                    'fields' => [self::F_ROOM_TIME_GLAD, self::F_ROOM_CLOSING_GLAD],
                    'title'  => 'Ср. время Довольки',
                    'format' => $formatAvgTime,
                ],
                [
                    'fields' => [self::F_ROOM_TIME_IDEA, self::F_ROOM_CLOSING_IDEA],
                    'title'  => 'Ср. время Хотелки',
                    'format' => $formatAvgTime,
                ],
            ),
            'gr' => Graph::GR_LINE,
        );
        
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
    
    protected function loadGlobalDiggers()
    {
        return [];
    }
    
    public function loadSpecificDiggers () 
    {
        return [
            (new Operator())->setCompanyId($this->companyId)
        ];
    }
    
    public function hasUnique()
    {
        return true;
    }
    
    public function getName()
    {
        return 'Чаты';
    }
    
    public function getId()
    {
        return Config::STATS_MAIN;
    }
    
    public function getDefaultView()
    {
        return function ($flow) {
            return $flow;
        };
    }

    public function getUniqueAndTimeShiftView()
    {
        return function($flow)
        {
            return new ExtractUnq($flow);
        };
    }

    
    public static function getReviewCreateFieldsByReviewType ($reviewType) 
    {
        static $reviewTypes;
        !$reviewTypes && $reviewTypes = [
            CompanyReview::TYPE_SMILE => self::F_REVIEW_CREATE_GLAD,
            CompanyReview::TYPE_SAD   => self::F_REVIEW_CREATE_UNGLAD,
            CompanyReview::TYPE_IDEA  => self::F_REVIEW_CREATE_IDEA,  
        ];
        
        return [self::F_REVIEW_CREATE, $reviewTypes[$reviewType]]; 
    }
    
    public static function getRatingFieldsByReviewTypeAndRate ($reviewType, $rating)
    {
        static $ratingFields;
    
        !$ratingFields && $ratingFields = [
            CompanyReview::RATING_POSITIVE => self::F_LIKE,
            CompanyReview::RATING_NEGATIVE => self::F_DISLIKE,
        ];
        
        static $byTypeRatingFields;
        $and = '.';
        
        !$byTypeRatingFields && $byTypeRatingFields = [
            CompanyReview::TYPE_SMILE . $and . CompanyReview::RATING_POSITIVE => self::F_LIKE_GLAD,
            CompanyReview::TYPE_SAD . $and . CompanyReview::RATING_POSITIVE   => self::F_LIKE_UNGLAD,
            CompanyReview::TYPE_IDEA . $and . CompanyReview::RATING_POSITIVE  => self::F_LIKE_IDEA,
    
            CompanyReview::TYPE_SMILE . $and . CompanyReview::RATING_NEGATIVE => self::F_DISLIKE_GLAD,
            CompanyReview::TYPE_SAD . $and . CompanyReview::RATING_NEGATIVE   => self::F_DISLIKE_UNGLAD,
            CompanyReview::TYPE_IDEA . $and . CompanyReview::RATING_NEGATIVE  => self::F_DISLIKE_IDEA,
        ];
        
        return [$ratingFields[$rating], $byTypeRatingFields[$reviewType.$and.$rating]];
    }
    
    public static function getReviewResolveFields($reviewType)
    {
        static $reviewTypes;
        !$reviewTypes && $reviewTypes = [
            CompanyReview::TYPE_SMILE => self::F_REVIEW_RESOLVE_GLAD,
            CompanyReview::TYPE_SAD   => self::F_REVIEW_RESOLVE_UNGLAD,
            CompanyReview::TYPE_IDEA  => self::F_REVIEW_RESOLVE_IDEA,
        ];
    
        return [self::F_REVIEW_RESOLVE, $reviewTypes[$reviewType]];
    }
    
    public static function getProcessingTimeFields($reviewType)
    {
        static $reviewTypes;
        !$reviewTypes && $reviewTypes = [
            self::CHAT_TYPE_MAIN      => self::F_ROOM_TIME_MAIN,
            CompanyReview::TYPE_SMILE => self::F_ROOM_TIME_GLAD,
            CompanyReview::TYPE_SAD   => self::F_ROOM_TIME_UNGLAD,
            CompanyReview::TYPE_IDEA  => self::F_ROOM_TIME_IDEA,
        ];
        
        return [self::F_ROOM_TIME, $reviewTypes[$reviewType]];
    }
    
    public static function getProcessingClosedFields($reviewType)
    {
        static $reviewTypes;
        !$reviewTypes && $reviewTypes = [
            self::CHAT_TYPE_MAIN      => self::F_ROOM_CLOSING_MAIN,
            CompanyReview::TYPE_SMILE => self::F_ROOM_CLOSING_GLAD,
            CompanyReview::TYPE_SAD   => self::F_ROOM_CLOSING_UNGLAD,
            CompanyReview::TYPE_IDEA  => self::F_ROOM_CLOSING_IDEA,
        ];
        
        return [self::F_ROOM_CLOSING, $reviewTypes[$reviewType]];
    }
}
