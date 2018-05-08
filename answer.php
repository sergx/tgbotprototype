<?php
class Answer extends basicClass {
  //$this->answer->
  
  
  public function greeting($message){
    
    /*
    Идентификация юзера
    Если юзера нет, создаем запись
    Если юзер есть, получаем дынные о нем
    */

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
  }
  
  
  public function sendMessage($text, $reply_markup = false){
    //echo "sendMessage: ".$string."\r\n";
    //return;
    $dataToSend = array(
      'chat_id' => $this->react->userData['chat']['id'],
      'parse_mode' => 'Markdown',
      'text' => $text
      );
    if($reply_markup){
      
      $dataToSend['reply_markup']['inline_keyboard'] = array(
        array(
          array(
            "text" => "friend"
            ,"callback_data" => "friend"
            ),
          array(
            "text" => "enemy"
            ,"callback_data" => "enemy"
            ),
          )
        );
      
      //$dataToSend['reply_markup']['keyboard'] = $reply_markup;
      //$dataToSend['reply_markup']['resize_keyboard'] = true;
      //$dataToSend['reply_markup']['one_time_keyboard'] = true;
      //$this->techLog(print_r($dataToSend, true));
    }else{
      $dataToSend['reply_markup']['remove_keyboard'] = true;
    }
    
    
    $this->connect->apiRequestJson("sendMessage", $dataToSend);
      
    // Запись в статистику, в БД, то-се
  }
  
  
  public function setPrevQuestion($string, $chat_id = false){
    if(!$chat_id){
      $chat_id = $this->react->userData['chat']['id'];
    }
    $this->react->userData['db_data']['prev_question'] = $string;
    
    $userInfo = $this->db->query("SELECT chat_data FROM ".MAIN_TABLE." WHERE id = '".BOT_PROTO_ID."' LIMIT 1");
    
    $userInfo = json_decode($userInfo['chat_data'], true);
    
    $userInfo[$chat_id]['prev_question'] = $string;
    
    $userInfo = json_encode($userInfo);

    //$getUserInfo = $this->db->query("SELECT chat_data FROM ".MAIN_TABLE." WHERE id = '".BOT_PROTO_ID."' LIMIT 1");
    
    $this->db->query("UPDATE ".MAIN_TABLE." SET chat_data = '".addslashes($userInfo)."' WHERE id = '".BOT_PROTO_ID."' LIMIT 1");
    
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
    $this->connect->apiRequestJson("sendMessage",array(
      'chat_id' => $this->react->userData['chat']['id'],
      "text" => $string
      ));
  }
  
}













