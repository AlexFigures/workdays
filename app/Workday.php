<?php


namespace App;

use RedBeanPHP\R as R;
use RedBeanPHP\RedException\SQL;

class Workday
{
    public $workday_id;

    //Будем считать что id в таблице профиля это Табельный номер сотрудника. Каждый сотрудник знает свой табельный

    public static function Start($id)
    {
        //пишем в таблицу workday, profile_id и date_start

        $profile = R::findOne('profile', 'id = ?', [$id]); //ищем id профиля по введеному табельному номеру

        if($profile['id'] === $id) //если нашли
        {
            $now =R::isoDate();
            $workday = R::findOne('workday', 'profile_id = :id AND date(date_start) = :date ', [':id' => $id, ':date' => $now]); //ищем id рабочего дня по введеному табельному номеру
            if(!isset($workday['id'])) {
                $profile_id = $profile['id'];
                $workday = R::dispense('workday'); //пишем начало дня для найденного сотрудника
                $workday->profile_id = $profile_id;
                $workday->date_start = R::isoDateTime();
                try {
                    R::store($workday);
                } catch (SQL $e) {
                }
            } else
            {
                echo 'day already started!';
            }
        } else
        {
            echo 'user not found!'; //надо кинуть исключение
        }
    }

    public static function Stop($id)
    {
        //обновляем таблицу workday, profile_id и date_stop
        $now = R::isoDate();
        $workday = R::findOne('workday', 'profile_id = :id AND date(date_start) = :date ', [':id' => $id, ':date' => $now]); //ищем id рабочего дня по введеному табельному номеру
        if($workday['profile_id'] === $id)
        {
            $workday_id = $workday['id'];
            $workday = R::load('workday', $workday_id);
            $workday->date_stop = R::isoDateTime();
            try {
                R::store($workday);
            } catch (SQL $e) {
            }
        } else
        {
            echo 'workday not started!';
        }
    }

    public static function pauseStart($id)
    {
        //пишем в таблицу workday_pause, workday_id и date_start
        $now = R::isoDate();
        $workday = R::findOne('workday', 'profile_id = :id AND date(date_start) = :date AND isnull(date_stop)', [':id' => $id, ':date' => $now]); //ищем id рабочего дня по введеному табельному номеру

        if($workday['id'])
        {
            $workday_id = $workday['id'];
            R::ext('xdispense', function ($type) { //нужен для работы с таблицами в имени которых есть "_"
            return R::getRedBean()->dispense($type);
            });

            $workday_pause = R::xdispense('workday_pause'); //пишем новый перерыв рабочего дня
            $workday_pause->workday_id = $workday_id;
            $workday_pause->date_start = R::isoDateTime();
            try {
                R::store($workday_pause);
            } catch (SQL $e) {
            }
        } else
        {
            echo 'workday not found';
        }
    }

    public static function pauseStop($id)
    {
        //пишем в таблицу workday_pause, workday_id и date_start
        $now = R::isoDate();
        $workday = R::findOne('workday', 'profile_id = :id AND date(date_start) = :date AND isnull(date_stop)', [':id' => $id, ':date' => $now]); //ищем id рабочего дня по введеному табельному номеру

        if($workday['id'])
        {
            $workday_id = $workday['id'];
            R::ext('xdispense', function ($type) { //нужен для работы с таблицами в имени которых есть "_"
            return R::getRedBean()->dispense($type);
            });

            $workday_pause = R::findOne('workday_pause', 'workday_id = :id AND date(date_start) = :date AND isnull(date_stop)', [':id' => $workday_id, ':date' => $now]); //ищем незакрытую паузу
            if($workday_pause['id'])
                $id = $workday_pause['id'];
                $workday_pause = R::load('workday_pause', $id);
                $workday_pause->date_stop = R::isoDateTime();
            try {
                R::store($workday_pause);
            } catch (SQL $e) {
            }
        } else
        {
            echo 'workday not in pause';
        }

    }

    public static function Delay($id)
    {
        $now = R::isoDate();
        $delay_exists = R::findOne('lateness', 'profile_id = :id AND date = :now',[':id' => $id, 'now' => $now]);//проверяем что сегодня еще записи не было
        if(!isset($delay_exists)) {
            $delay = R::dispense('lateness');
            $delay->profile_id = $id;
            $delay->date = R::isoDate();
            try {
                R::store($delay); //пишем
            } catch (SQL $e) {
            }
        }
    }



}