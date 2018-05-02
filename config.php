<?php
//error_reporting(E_ALL & ~E_NOTICE);

define('DEBUG', 'true');

// MYSQL
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'ci33756_bot');
define('DB_PASSWORD', 'ci33756_bot');
define('DB_DATABASE', 'ci33756_bot');
define('DB_PREFIX', '');

define('BOT_PROTO_ID', 1);

define('MAIN_TABLE', "prototype_bot");

// php -f /home/c/ci33756/bot.wincub.ru/public_html/bot/WincubProtoFather/index.php
define('WEBHOOK_URL', 'https://bot.wincub.ru/bot/WincubProtoFather/index.php');
define('BOT_TOKEN', '557183486:AAGO4-R6UHCuN5lBVm2el3-O2rl5PT0kzg8');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

