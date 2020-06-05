<?php
ignore_user_abort(TRUE);
require_once 'vendor/autoload.php';
use RedBeanPHP\R as R;
use App\WorkdayDemon;
R::setup( 'mysql:host=mysqloff;dbname=proff','test', 'Password%34', false);
// Проверка подключения к БД
if(!R::testConnection()) die('No DB connection!');

while(true){
    sleep(3600);
    $workdayDemon = new WorkdayDemon();

}


