<?php
class BubenDB extends basicClass {
  /*
    SELECT WHERE
    INSERT INTO
    SET WHERE / UPDATE
    
    bot_info
      constants
        WEBHOOK_URL
        BOT_TOKEN
        API_URL
      bot_name
      
    chats
      chat // chat_id
        ?uid
        chat_id
        user_name
        last_question
        last_question_inline_btn
        chat_data
        
        
    chat_log // array
      log_item // time
        ?uid // unique id
        chat_id 
        time
        data
          text
        sender // user or bot
        reply_markup // bot only
    
    ? actions_log
    
    ?uids // array
    
    
    Структура файлов
      Построково заполнять файл (1 юзер - одна строка, одна запись в логе - одна строка)
      
    Функции
      @ $path array [3, 'chat_id']
      getFromTable('tableName', $path);
      
      
  */
  public function setChatLog($data){
    
    //$data = array(
    //  "chat_id" => $chat_id,
    //  "time" => "...",
    //  "data" => array("text" => "..."),
    //  "sender" => "user",
    //  "reply_markup" => false
    //  );
    
    $filepath = $this->folder."chat_log.json";
    $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    
    if(filesize($filepath) < 10){
      file_put_contents($filepath, $data, LOCK_EX);
    }else{
      file_put_contents($filepath, ",".PHP_EOL.$data, FILE_APPEND | LOCK_EX);
    }
    
    
  }
  
  private $folder = BDB_FOLDER;
  private $error_report = array('errorBDB' => false, 'msg' => array(), 'empty' => false );
  private $error_report_copy;
  // @ Подумать над уникализацией названия файлов. Добавлять какой-то префикс или постфикс?..
  private $files = array("chats", "chat_log" /*, "bot_info","?uids"*/ );
  
  public function __construct() {
    $this->error_report_copy = $this->error_report;
    if(!is_dir($this->folder)){
      mkdir($this->folder, 0777, true);
    }
    foreach($this->files as $file){
      $filepath = $this->folder.$file.".json";
      if(!file_exists($filepath) || filesize($filepath) === 0){
        file_put_contents($filepath, "[]");
      }
    }
  }
  
  
  

  
  public function errorReportSend(){
    if(empty($this->error_report['msg'])){
      $this->error_report['errorBDB'] = false;
      return false;
    }else{
      $this->error_report['errorBDB'] = true;
      return $this->error_report;
    }
  }
  // @ getFrom("answersdata", [0, "routing", 0, "response", 0, "response_text"]);
  public function getFrom($tableName, $path = array()){
    $this->error_report = $this->error_report_copy;
    if(!is_array($path)){
      $this->error_report['msg'][] = 'Empty $path in '.__FILE__.' in '.__METHOD__.' on line '.__LINE__;
      return $this->errorReportSend();
    }
    
    if(!in_array($tableName, $this->files)){
      $this->error_report['msg'][] = 'Wrong file name "'.$tableName.'" in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
      return $this->errorReportSend();
    }
    
    $db_file = $this->folder . $tableName.  '.json';
    
    if(filesize($db_file) === 0){
      $this->error_report['empty'] = true;
      return $this->errorReportSend();
    }
    
    $table_json = json_decode(file_get_contents($db_file), true);
    
    if(!is_array($table_json)){
      $this->error_report['msg'][] = 'Failed to get JSON from "'.$db_file.'" in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
      return $this->errorReportSend();
    }
    
    $path_done = array();
    
    $level =& $table_json;
    if(isset($path['index']) AND empty($path['index']) AND count($path) === 1){
      return $table_json;
    }else{
      foreach($path as $key){
        if(isset($level[$key])){
          $level =& $level[$key];
          $path_done[] = $key;
        }else{
          if(count($path) !== count($path_done)){
            $this->error_report['empty'] = true;
            $this->error_report['msg'][] = 'Can\'t get final value. Stack on "'. implode(",",$path_done) .'" in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
            $this->error_report['msg'][] = 'path: '.implode($path,true);
            
            //ob_start();
            //var_dump($path);
            //// var_export($path, true);
            //$this->error_report['msg'][] = ob_get_clean();
            
            $this->error_report['msg'][] = 'key: value: "'.$key.'", type: '.gettype($key);
            if(is_array($level)){
              $this->error_report['msg'][] = 'last value: '.print_r($level, true);
            }else{
              $this->error_report['msg'][] = 'last value (type '.gettype($level).'): '.$level;
            }
            $this->error_report['path_done'] = $path_done;
            return $this->errorReportSend();
          }else{
            $this->error_report['empty'] = true;
            return $this->errorReportSend();
          }
        }
      }
      return $level;
    }
  }
  
  // @param $action 'add' || 'REPLACE'
  public function setTo($tableName, $path = array(), $value = NULL, $action){
    $this->error_report = $this->error_report_copy;
    if(!is_array($path)){
      $this->error_report['msg'][] = 'Empty $path in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
      return $this->errorReportSend();
    }
    if(is_null($value)){
      $this->error_report['msg'][] = 'Empty $value in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
      return $this->errorReportSend();
    }
    
    $db_file = $this->folder.$tableName.'.json';
    
    $table_json = json_decode(file_get_contents($db_file), true);
    
    if(is_null($table_json)){
      $this->error_report['msg'][] = 'Failed to get JSON from "'.$db_file.'" in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
      return $this->errorReportSend();
    }
      
    $path_done = array();
    
    $level =& $table_json;
    
    foreach($path as $key){
      $path_done[] = $key;
      if(isset($level[$key])){
        $level =& $level[$key];
        if(count($path) === count($path_done)){
          switch($action){
            case "APPEND":
              if(is_array($level)){
                $level[] = $value;
              }else{
                $this->error_report['msg'][] = 'Can\'t "APPEND" value to non-array. Use $action "UPDATE" in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
                return $this->errorReportSend();
              }
              break;
            case "UPDATE":
            case "SET":
              $level = $value;
              break;
            case "INSERT":
                $this->error_report['msg'][] = 'Can\'t "INSERT" value here. Use $action "UPDATE" in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
                return $this->errorReportSend();
              break;
          }
        }
      }else{
        if(count($path) !== count($path_done)){
          $this->error_report['empty'] = true;
          $this->error_report['msg'][] = 'Can\'t get final value. Stack on "'. implode(", ",$path_done) .'" in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
          $this->error_report['msg'][] = 'path: '.implode($path,true);
          ob_start();
          var_dump($path);// var_export($path, true);
          $this->error_report['msg'][] = ob_get_clean();
          $this->error_report['msg'][] = 'key: value: "'.$key.'", type '.gettype($key);
          if(is_array($level)){
            $this->error_report['msg'][] = 'last value: '.print_r($level, true);
          }else{
            $this->error_report['msg'][] = 'last value (type '.gettype($level).'): '.$level;
          }
          $this->error_report['path_done'] = $path_done;
          return $this->errorReportSend();
        }else{
          switch($action){
            case "INSERT":
            case "SET":
              if(is_array($level)){
                $level[$key] = $value;
              }else{
                $this->error_report['msg'][] = 'Can\'t "INSERT" value to non-array. Use $action "UPDATE" in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
                return $this->errorReportSend();
              }
              break;
            case "APPEND":
            case "UPDATE":
                $this->error_report['msg'][] = 'Can\'t "'.$action.'" value. Key ["'.$key.'"] was not found. in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
                return $this->errorReportSend();
              break;
          }
        }
      }
    }
    
    // Set new value to the file
    $new_JSON_data = json_encode($table_json, JSON_UNESCAPED_UNICODE);
    if($new_JSON_data){
      if(file_put_contents($db_file, $new_JSON_data) === false){
        $this->error_report['msg'][] = 'Error while update file in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
        return $this->errorReportSend();
      }
    }else{
      $this->error_report['msg'][] = 'Error while update file in '.__FILE__.' at '.__METHOD__.' on line '.__LINE__;
      return $this->errorReportSend();
    }
    return true;
  }
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
}