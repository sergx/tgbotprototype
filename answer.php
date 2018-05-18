<?php
class Answer extends basicClass {
  //$this->answer->
  
  
  public function greeting($message){
    
    /*
    Идентификация юзера
    Если юзера нет, создаем запись
    Если юзер есть, получаем дынные о нем
    */

//BDB RENEW
/*
$getUserInfo = $this->db->query("SELECT chat_data FROM ".MAIN_TABLE." WHERE id = '".BOT_PROTO_ID."' LIMIT 1");

$getUserInfo = json_decode($getUserInfo['chat_data'], true);

if(!empty($getUserInfo) AND !empty($getUserInfo[$message['chat']['id']])){
  $userInfo = $getUserInfo[$message['chat']['id']];
}else{
  $userInfo = array("prev_question" => "/start", "chat_id" => $message['chat']['id']);
  $getUserInfo = array();
  $getUserInfo[$message['chat']['id']] = $userInfo;
}
$message['db_data'] = $userInfo;
return $message;
*/
    // BDB RENEW
    
    $userInfo = $this->db->getFrom("chats", array( $message['chat']['id'] ));
    //$this->answer->techLog(print_r($userInfo,true));
    if(empty($userInfo['chat_id'])){
      
      $userInfo = array("prev_question" => "/start", "chat_id" => $message['chat']['id']);
      $res = $this->db->setTo("chats", array($message['chat']['id']), $userInfo, "INSERT");
      //$this->answer->techLog(print_r($res,true));
    }
    $message['db_data'] = $userInfo;
    return $message;
    
    // /BDB RENEW
  }
  
  
  public function sendMessage($text, $reply_markup = false){
    //echo "sendMessage: ".$string."\r\n";
    //return;
    $dataToSend = array(
      'chat_id' => $this->react->userData['chat']['id'],
      'parse_mode' => 'Markdown',
      'text' => $text
      );
    if($reply_markup !== false){
      $dataToSend['reply_markup'] = $reply_markup;
    }else{
      $dataToSend['reply_markup']['remove_keyboard'] = true;
    }
    
    //$this->techLog(print_r($dataToSend, true));
    $this->connect->apiRequestJson("sendMessage", $dataToSend);
    
    $this->db->setChatLog(array(
      "chat" => $dataToSend['chat_id'],
      "t" => date("Y-m-d H:i:s"),
      "s" => "b",
      "data" => array("text" => $dataToSend['text']),
      "rm" => $reply_markup ? true : false
    ));
      
    // Запись в статистику, в БД, то-се
  }
  
  
  public function setPrevQuestion($string, $chat_id = false){
    if(!$chat_id){
      $chat_id = $this->react->userData['chat']['id'];
    }
    $this->react->userData['db_data']['prev_question'] = $string;

//BDB RENEW
/*
$userInfo = $this->db->query("SELECT chat_data FROM ".MAIN_TABLE." WHERE id = '".BOT_PROTO_ID."' LIMIT 1");

$userInfo = json_decode($userInfo['chat_data'], true);

$userInfo[$chat_id]['prev_question'] = $string;

$userInfo = json_encode($userInfo);

$this->db->query("UPDATE ".MAIN_TABLE." SET chat_data = '".addslashes($userInfo)."' WHERE id = '".BOT_PROTO_ID."' LIMIT 1");
*/
    // BDB RENEW
    
    $userInfo = $this->db->setTo("chats", array( $chat_id , "prev_question" ), $string, "SET");
    
    
    // /BDB RENEW
    
  }
  /*
  public function setUserJsonData($key, $value, $chat_id = false){
    if(!$chat_id){
      $chat_id = $this->react->userData['db_data']['chat_id'];
    }
    $user_json_data = $this->react->userData['db_data']['data_json'];
    if(!empty($user_json_data)){
      $user_json_data[$key] = $value;
    }else{
      $user_json_data = array($key => $value);
    }
    $this->db->query("UPDATE ".DB_PREFIX."users SET data_json = '".json_encode($user_json_data)."' WHERE chat_id = '".$chat_id."' LIMIT 1");
  }
  */
  public function techLog($string){
    if(!DEBUG){ return false;}
    
    if(is_array($string)){
      $string = print_r($string, true);
    }
    
    $chat_id = $this->react->userData['chat']['id'];
    if(empty($chat_id)){
      $chat_id = 337506768;
    }
      $this->connect->apiRequestJson("sendMessage",array(
    'chat_id' => $chat_id,
    "text" => $string
    ));
    //trigger_error($string);
  }
  /*
  public function hardTechLog($string){
    if(!DEBUG){ return false;}
    
    $data = array(
            "errorMSG" =>array(
              "botName" => "WincubProtoFather"
              ,"content" => $string
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
    file_get_contents('https://bot.wincub.ru/bot/WincubErrorLogBot/index.php', false, $context);
  }
  */
}













