<?php
error_reporting(E_ALL & ~E_NOTICE);

$monitor = array();
$monitor['time_start'] = microtime(true);
$monitor['memory_start'] = memory_get_usage();

require_once('config.php');
require_once('registry.php');
$registry = new Registry();

require_once('basicClass.php');

require_once('bot.php');
$bot = new Bot($registry);

require_once('db.php');
$db = new DB(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

require_once('tools.php');
$tools = new Tools($registry);
$registry->set('tools', $tools);

require_once('answer.php');
$answer = new Answer($registry);
$registry->set('answer', $answer);

require_once('react.php');
$react = new React($registry);
$registry->set('react', $react);


require_once('connect.php');
$connect = new Connect($registry);
$registry->set('connect', $connect);



$connect->init();

//$bot->answer->techLog("script time: ".substr(microtime(true) - $monitor['time_start'], 0, 7));


