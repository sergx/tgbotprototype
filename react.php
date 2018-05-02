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
      
      /*
      // Пытаемся обработать команду в сообщении:
      
      foreach($this->comands as $v){
        switch($v['data_type']){
          case "text":
            if(empty($message[$v['data_type']])){
              continue;
            }
            if(strpos($message['text'], $v['content']) === 0){
              $this->{$v['method_react']}(array("set_prev_question" => $v['set_prev_question']));
              goto reacted;
            }
            break;
        }
      }
      
      */
      
      // Преоритетно обрабатываем команды
      if(!empty($message['text'])){
        $command = $message['text'];
        foreach($this->answers() as $v){
          if(in_array($command, $v['command'])){
            if($v['validation']){
              $validate = $this->validation($message[$v['data_container']],$v['validation']);
            }else{
              $validate = array('result' => true, 'value' => $message['text']);
            }
            if($validate['result'] === true){
              //$this->answer->techLog(123);
              foreach($v['answer_ok'] as $string){
                
                // Вызываем специальный метод, либо просто обрабатываем строки
                if(strpos($string, "METHOD:") === 0){
                  $methodName = substr($string, strlen("METHOD:"));
                  //$method_result = 
                  $this->answer->sendMessage($this->$methodName($validate['value']));
                }else{
                  if($validate['value']){
                    $this->answer->sendMessage(str_replace("{value}", $validate['value'] ,$string));
                  }//else{
                    //$this->answer->sendMessage($string);
                  //}
                }
              }
              // Записываем полученный ответ
              //$this->answer->setUserJsonData($prev_question, $validate['value']);
              // Устанавливаем новый prev_question
              $this->answer->setPrevQuestion($v['next_question']);
            }else{
              $this->answer->sendMessage($v['answer_error']);
            }
            goto reacted;
          }
        }
      }
      
      // Обрабатывает ответ на prev_question
      
      $prev_question = $this->userData['db_data']['prev_question'];
      //$prev_question = "volume";
      if($prev_question){
        foreach($this->answers() as $v){
          if(in_array($prev_question, $v['question'])){
            if($v['validation']){
              $validate = $this->validation($message[$v['data_container']],$v['validation']);
            }else{
              $validate = array('result' => true, 'value' => $message[$v['data_container']]);
            }
            
            if($validate['result'] === true){
              //$this->answer->techLog(123);
              foreach($v['answer_ok'] as $string){
                
                // Вызываем специальный метод, либо просто обрабатываем строки
                if(strpos($string, "METHOD:") === 0){
                  $methodName = substr($string, strlen("METHOD:"));
                  //$method_result = 
                  $this->answer->sendMessage($this->$methodName($validate['value']));
                }else{
                  if($validate['value']){
                    $this->answer->sendMessage(str_replace("{value}", $validate['value'] ,$string));
                  }//else{
                    //$this->answer->sendMessage($string);
                  //}
                }
              }
              // Записываем полученный ответ
              //$this->answer->setUserJsonData($prev_question, $validate['value']);
              // Устанавливаем новый prev_question
              $this->answer->setPrevQuestion($v['next_question']);
            }else{
              $this->answer->sendMessage($v['answer_error']);
            }
            goto reacted;
          }
        }
      }else{
        foreach($this->answers() as $v){
          //if()
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
      
      /*
      $response_array_old = array(
//    array(
//      "command" => "/start"
//      "question" => ""
//      ,"data_container" => "text"
//      ,"validation" => ""
//      ,"answer_ok" => array(
//        "Ясно, *{value}* литров."
//        ,"Напишите сколько стоит транспортировка 1 литра на 1 км")
//      ,"answer_error" => ""
//      )
//    ,
        array(
          "question" => ["volume"]
          ,"next_question" => "price"
          ,"data_container" => "text"
          ,"validation" => "questionvolume"
          ,"answer_ok" => array(
            "Ясно, *{value}* литров."
            ,"Напишите сколько стоит транспортировка 1 литра на 1 км")
          ,"answer_error" => "Хм, что вы имели в виду? Я бы понял, если бы Вы указали кол-во литров *числом*!:)"
          )
        ,array(
          "question" => ["price"]
          ,"next_question" => "location"
          ,"data_container" => "text"
          ,"validation" => "questionprice"
          ,"answer_ok" => array(
            "Окей, *{value}* рублей за перевозку литра на *1 км.*"
            ,"Теперь отправьте *точку на карте*, куда вы бы хотели получить доставку.")
          ,"answer_error" => "Хм, что вы имели в виду? Я бы понял, если бы Вы указали стоимость *числом*!:)"
          )
        ,array(
          "question" => ["location","full"]
          ,"data_container" => "location"
          ,"next_question" => "full"
          ,"validation" => "questionlocation"
          ,"answer_ok" => array(
            "Запрос получен. Счетаем стоимость до координат *{value}*"
            , "METHOD:getGoogleApi" // $this->getGoogleApi($location)
            , "Чтобы произвести новый расчет введите [/start]")
          ,"answer_error" => "Для расчета необходимо указать точку на карте.."
          )
        );
        
        $this->answer->techLog($response_array_old);
        */
        return $response_array;
    }
    
    public function validation($input, $type){
      $validation = array("result" => false, "value" => false);
      switch($type){
        case "questionvolume":
          if($input == (int)$input){
            $validation['value'] = (int)$input;
            $validation['result'] = true;
          }
          break;
        case "questionprice":
          $input = $this->tools->tofloat($input);
          if($input){
            $validation['value'] = $input;
            $validation['result'] = true;
          }
          break;
        case "questionlocation":
          if(!empty($input)){
            $validation['value'] = implode(",", $input);
            $validation['result'] = true;
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