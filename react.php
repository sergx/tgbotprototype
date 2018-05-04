<?php

class React extends basicClass {
// $this->react->
/*
 - определение состояние юзера (Последний вопрос, какие данные у нас есть)
 - Соответственно тут прописаны эти состояния
 - Приветствие юзера, если давно не заходил и имеет уже введенные данные. Предложение их очистить
*/

/*
Приоретет выполнения операций:
 - прямая команда типа /start /help
 - ответ на prev_question
 - попытка ответить на свободный ввод пользователя
 - сообщение об ошибки приема данных

*/

/*
Форматирование
https://core.telegram.org/bots/api#formatting-options
(Нужно добавить 'parse_mode' => 'Markdown')

Кнопочки - KeyboardButton
https://core.telegram.org/bots/api#replykeyboardmarkup


*/
    public $userData;
    
    public function startCommand(array $data = array()){
      //echo __METHOD__." (строка ".__LINE__.")\r\n";
      $this->answer->sendMessage(
"Здравствуйте! Расчитайте стоимость доставки топлива
*ДТ ЕВРО-5* от *ЛПДС «КРАСНЫЙ БОР»*.
Стоимость литра на 02.03.2018 составляет *35,90 руб/литр*.");
      $this->answer->sendMessage("Укажите кол-во литров");
      
      $this->answer->setPrevQuestion($data['set_prev_question']);
    }
    

    public function init($message){
      /*
      Идентификация юзера
      Если юзера нет, создаем запись
      Если юзер есть, получаем дынные о нем
      */
      
      $this->userData = $this->answer->greeting($message);
      //$this->answer->techLog($this->userData);
      
      // Commands and Questions handling:
      if(!empty($message['text'])){
        $command = $message['text'];
        $prev_question = $this->userData['db_data']['prev_question'];
        
        // Commands first. If no commands, than questions handling
        foreach(array('command' => $command ,'question' => $prev_question) as $queue_key => $queue_elem){
          if($queue_key === 'question' AND empty($queue_elem)){
            break;
          }
          
          foreach($this->answers() as $v){
            $data_container = !empty($v['data_container']) ? $v['data_container'] : "text";
            
            if(in_array($queue_elem, $v[$queue_key])){
              
              // Routing
              if(!empty($v['routing'][0])){
                foreach($v['routing'] as $r){
                  $route = explode("----",$r);
                  array_walk($route, function (&$value) {
                    $value = explode("||",trim($value));
                  });
                  
                  // $route[0] - array of valid input commands
                  // $route[1] - array of messages to send
                  // $route[2][0] - prev_question to set
                  
                  if(in_array($message[$data_container], $route[0])){
                    foreach($route[1] as $route_answer){
                      $this->answer->sendMessage(str_replace("{value}", $message[$data_container], $route_answer));
                    }
                    $this->answer->setPrevQuestion(!empty($route[2][0]) ? $route[2][0] : false);
                    goto reacted;
                  }
                  
                }
              }
              //$this->answer->techLog(print_r($route, true));
              
              if($v['validation']){
                $validate = $this->validation($message[$data_container],$v['validation']);
              }else{
                $validate = array('result' => true, 'value' => $message[$data_container]);
              }
              if($validate['result'] === true){
                //$this->answer->techLog(123);
                foreach($v['answer_ok'] as $string){
                  
                  // Вызываем специальный метод, либо просто обрабатываем строки
                  if(strpos($string, "METHOD:") === 0){
                    $methodName = substr($string, strlen("METHOD:"));
                    //$method_result = 
                    $this->answer->sendMessage($this->$methodName($validate['value']));
                  }elseif(strpos($string, "<?php") === 0){
                    $string_to_eval = substr($string, 5);
                    $eval_result = eval($string_to_eval);
                    $this->answer->sendMessage($eval_result);
                  }else{
                    if($validate['value']){
                      $this->answer->sendMessage(str_replace("{value}", $validate['value'] ,$string));
                    }
                  }
                }
                // Записываем полученный ответ
                // $this->answer->setUserJsonData($prev_question, $validate['value']);
                // Устанавливаем новый prev_question
                $this->answer->setPrevQuestion($v['next_question']);
              }else{
                $this->answer->sendMessage($v['answer_error']);
                $this->answer->setPrevQuestion($v['next_question_onerror']);
              }
              goto reacted;
            }
          }
        }
      }
      
      $this->answer->sendMessage("Нда, и вот что мне теперь с этим делать - не понятно...");
      
      reacted:
    }
    
    // Функця, отвечающая за отправку сообщение в ответ на prev_question
    public function answers() {
      //Массив данных будет хранится где-то
      
      $getUserInfo = $this->db->query("SELECT answers FROM ".MAIN_TABLE." WHERE id = '".BOT_PROTO_ID."' LIMIT 1");
      $response_array = json_decode($getUserInfo['answers'], true);
      return $response_array;
    }
    
    public function validation($input, $type){
      $validation = array("result" => false, "value" => false);
      switch($type){
        case "enter-riddle":
          if(strtolower($input) === "friend"){
            $validation['value'] = $input;
            $validation['result'] = true;
          }
          break;
        case "enter-the-mine?":
          switch(strtolower($input)){
            case "/yes":
            case "yes":
            case "/okey":
            case "okey":
              $validation['value'] = $input;
              $validation['result'] = true;
              break;
            case "/no":
            case "no":
              $validation['value'] = $input;
              $validation['result'] = false;
              break;
          }
          break;
        case "entered-the-mine":
          switch(strtolower($input)){
            case "/runintothemines":
            case "runintothemines":
            case "run into the mines":
              $validation['value'] = $input;
              $validation['result'] = true;
              break;
            case "/smokeApipe":
              $validation['value'] = $input;
              $validation['result'] = false;
              break;
          }
          break;
      }
      return $validation;
    }
    
    /*
    public function getGoogleApi($point_to){
      $user_data = $this->userData['db_data']['data_json'];
      $point_from = $this->settings['bases'][0]['coords'];
      $litre_price = $this->settings['bases'][0]['price_litre'];
      //$point_to = $this->userData['db_data']['json_data']['location'];
      
      $url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$point_from."&destination=".$point_to."&language=ru&key=".GOOGLE_MAP_KEY;
    //return $url;
      $response = file_get_contents($url);
      $data = json_decode($response, true);
      if(!empty($data['routes'][0]['legs'][0])){
        $km = (int)$data['routes'][0]['legs'][0]['distance']['value'] / 1000; // Км
        $address = $data['routes'][0]['legs'][0]['end_address']; // Адрес
        
        $price_final = array(
          "fuel" => intval($litre_price*$user_data['volume'])
          ,"delivery" => intval($km*$user_data['price']*$user_data['volume'])
        );
        $price_final['sum'] = $price_final['fuel'] + $price_final['delivery'];
        
        $result = "Стоимость топлива составляет *".$price_final['fuel']." руб.* за _".$user_data['volume']." л._\r\nРасстояние до вашего объекта _".$address."_ — *".(int)$km." км*.\r\nДоставка будет стоить *". $price_final['delivery'] ." руб.* за _".$user_data['volume']."_ литров.\r\n*Итого: ".$price_final['sum']." руб.*";
        
      }else{
        $result = $url;
        //$result .= $response;
      }
      return $result;
    }
    */

}