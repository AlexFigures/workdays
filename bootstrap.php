<?php

use RedBeanPHP\R as R;

R::setup( 'mysql:host=mysqloff;dbname=proff','test', 'Password%34', false);
// Проверка подключения к БД
if(!R::testConnection()) die('No DB connection!');

$workdayDemon = dirname(__FILE__).'/shedule.php';
$pid = exec("nohup nice /usr/local/bin/php -q $workdayDemon /dev/null 2>&1 &");
