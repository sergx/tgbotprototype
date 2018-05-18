<?php


register_shutdown_function('ShutDown');


function catchError($errno, $errstr, $errfile = '', $errline = ''){
    $message = "";
    $message .= FriendlyErrorType($errno). "\r\n";
    $message .= $errstr . "\r\n";
    $message .= "<b>".substr($errfile, strlen($_SERVER['DOCUMENT_ROOT'])) . "</b>\r\n";
    $message .= "on line <b>".$errline."</b>";
    //echo $message;
    $data = array(
            "errorMSG" =>array(
              "botName" => "tgbotprototype"
              ,"content" => $message
              )
            );

    $data = json_encode($data);
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => "Content-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($data) . "\r\n",
            'content' => $data
        )
    );

$context  = stream_context_create($opts);
    file_get_contents('https://bot.wincub.ru/WincubErrorLogBot/index.php', false, $context);
    exit();
}
function ShutDown(){
    $lasterror = error_get_last();
    if(!in_array($lasterror['type'], array(E_DEPRECATED) )){
        catchError($lasterror['type'],$lasterror['message'],$lasterror['file'],$lasterror['line']);
    }
}
function FriendlyErrorType($type) 
{
  $er_name = "";
    switch($type) {
        case 1: $er_name = "E_ERROR"; break;
        case 2: $er_name = "E_WARNING"; break;
        case 4: $er_name = "E_PARSE"; break;
        case 8: $er_name = "E_NOTICE"; break;
        case 16: $er_name = "E_CORE_ERROR"; break;
        case 32: $er_name = "E_CORE_WARNING"; break;
        case 64: $er_name = "E_COMPILE_ERROR"; break;
        case 128: $er_name = "E_COMPILE_WARNING"; break;
        case 256: $er_name = "E_USER_ERROR"; break;
        case 512: $er_name = "E_USER_WARNING"; break;
        case 1024: $er_name = "E_USER_NOTICE"; break;
        case 2048: $er_name = "E_STRICT"; break;
        case 4096: $er_name = "E_RECOVERABLE_ERROR"; break;
        case 8192: $er_name = "E_DEPRECATED"; break;
        case 16384: $er_name = "E_USER_DEPRECATED"; break;
    } 
    return $er_name; 
} 


