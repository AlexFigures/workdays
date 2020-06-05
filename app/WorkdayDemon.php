<?php

namespace App;

use App\Workday;
use DateTime;
use RedBeanPHP\R as R;

class WorkdayDemon extends Workday
{

    function __construct()
    {
        $this->closeWorkday();
        $this->lateCheck();
    }

    private function closeWorkday()
    {
        $now = R::isoDate();
        $workday = R::getAll("SELECT profile_id, offset FROM workday JOIN profile on profile_id = profile.id WHERE isnull(date_stop) AND date(date_start) = :date",[':date' => $now]);//Выбираем незакрытые дни
        foreach ($workday as $id)
        {
            $date = new DateTime();
            $date->modify("+{$id['offset']} hour");
            $localNow = $date->format('H:i'); //узнаем местное время по настройкам пользователя

            if('01:00' >= $localNow && $localNow >= '00:00') //проверяем полночь уже или нет
            {
                $this->Stop($id['profile_id']);
            }
        }
    }

    private function lateCheck()
    {
        //выбрать всех пользователей кто не начал сегодня день и записать в таблицу опоздавших
        $now = R::isoDate();
        $lateUsers = R::getAll("SELECT id, offset as `offset` FROM profile WHERE id NOT IN (SELECT profile_id FROM workday WHERE date(date_start) = :date AND (time(date_start) + interval profile.offset hour) >= '09:05:00')",[':date' => $now]);//выбираем всех кто не начал день или начал с опозданием
        foreach ($lateUsers as $id)
        {
            $this->Delay($id['id']); //пишем опоздание в таблицу
        }

    }

    public function __destruct()
    {

    }

}
