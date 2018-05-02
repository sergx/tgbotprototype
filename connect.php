<?php
// Подключение к Телеграм АПИ
// Подключение к БД
// Обработка запросов БД
class Connect extends basicClass {
    public function init(){
        if (php_sapi_name() == 'cli') {
            // command in config.php
            // if run from console, set or delete webhook
            $action = 'delete';
            $action = WEBHOOK_URL;
            
            $this->apiRequest('setWebhook', array('url' => $action));
            exit;
        }
        
        $content = file_get_contents("php://input");
        //$this->answer->techLog($content);
        $update = json_decode($content, true);
        
        if (!$update) {
            // receive wrong update, must not happen
            exit;
        }
        
        if (isset($update["message"])) {
          $this->react->init($update["message"]);
        }
    }
    
    public function apiRequestWebhook($method, $parameters) {
        if (!is_string($method)) {
            error_log("Method name must be a string\n");
            return false;
        }
        
        if (!$parameters) {
            $parameters = array();
        } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");
            return false;
        }
        
        $parameters["method"] = $method;
        
        header("Content-Type: application/json");
        echo json_encode($parameters);
        return true;
    }

    public function exec_curl_request($handle) {
    $response = curl_exec($handle);
    
    if ($response === false) {
        $errno = curl_errno($handle);
        $error = curl_error($handle);
        error_log("Curl returned error $errno: $error\n");
        curl_close($handle);
        return false;
    }
    
    $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
    curl_close($handle);
    
    if ($http_code >= 500) {
        // do not wat to DDOS server if something goes wrong
        sleep(10);
        return false;
    } else if ($http_code != 200) {
        $response = json_decode($response, true);
        error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
    if ($http_code == 401) {
        throw new Exception('Invalid access token provided');
    }
    return false;
        } else {
            $response = json_decode($response, true);
            if (isset($response['description'])) {
                error_log("Request was successful: {$response['description']}\n");
            }
            $response = $response['result'];
        }
        
        return $response;
    }   
    
    public function apiRequest($method, $parameters) {
        if (!is_string($method)) {
            error_log("Method name must be a string\n");
            return false;
        }
        
        if (!$parameters) {
            $parameters = array();
        } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");
            return false;
        }
        
        foreach ($parameters as $key => &$val) {
            // encoding to JSON array parameters, for example reply_markup
            if (!is_numeric($val) && !is_string($val)) {
                $val = json_encode($val);
            }
        }
        $url = API_URL.$method.'?'.http_build_query($parameters);
        
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        
        return $this->exec_curl_request($handle);
    }
    
    public function apiRequestJson($method, $parameters) {
        if (!is_string($method)) {
            error_log("Method name must be a string\n");
            return false;
        }
        
        if (!$parameters) {
            $parameters = array();
        } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");
            return false;
        }
        
        $parameters["method"] = $method;
        
        $handle = curl_init(API_URL);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        
        return $this->exec_curl_request($handle);
    }
    
    function processMessage($message) {
        // process incoming message
        $message_id = $message['message_id'];
        $chat_id = $message['chat']['id'];
        
        
        $user_data = getUserValue($chat_id);
        if (isset($message['location'])) {
            if(!empty($user_data['price']) AND !empty($user_data['volume'])){
                $location = $message['location']['latitude'].",".$message['location']['longitude'];
                setUserValue($chat_id, array("coord" => $location,"last_question" => "full"));
                $user_data['coord'] = $location;
                apiRequest("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown', "text" => "Запрос получен. Счетаем стоимость до координат *".$location."*"));
                $response = getGoogleApi($user_data);
                apiRequest("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown', "text" => $response));
                //apiRequest("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown', "text" => "Ок, вы можете изменить указаные данные:\r\n*/литры [число]* - чтобы изменить кол-во литров\r\n*/[число] руб* - чтобы поменять цену за км.\r\nЧтобы поменять место доставки пришлите заново *местоположение*"));
                
                return true;
            }else{
                apiRequest("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown', "text" => "*MESSAGE INFO*\r\n```".print_r($user_data, true)."```"));
            }
        }
    
      if (isset($message['text'])) {
        // incoming text message
        $text = $message['text'];
    
    
        if (strpos($text, "/start") === 0) {
            apiRequestJson("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown', "text" => "Здравствуйте! Расчитайте стоимость доставки топлива\r\n*ДТ ЕВРО-5* от *ЛПДС «КРАСНЫЙ БОР»*.\r\nСтоимость литра на 02.03.2018 составляет *35,90 руб/литр*."));
            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Укажите кол-во литров'));
            setUserValue($chat_id, array("last_question" => "volume"));
        }else{
            switch($user_data['last_question']){
                case "volume":
                    if($text == (int)$text){
                        $value = (int)$text;
                        apiRequest("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown', "text" => 'Ясно, *'.$value.'* литров.'));
                        apiRequest("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown', "text" => 'Напишите сколько стоит транспортировка 1 литра на 1 км'));
                        setUserValue($chat_id, array($user_data['last_question'] => $value,"last_question" => "price"));
                    }else{
                        apiRequest("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown', "text" => 'Хм, что вы имели в виду? Я бы понял, если бы Вы указали кол-во литров *числом*!:)'));
                    }
                    break;
                case "price":
                    $value = tofloat($text);
                    if($value){
                        apiRequest("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown', "text" => 'Окей, *'.$value.'* рублей за перевозку литра на *1 км.*'));
                        apiRequest("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown', "text" => 'Теперь отправьте *точку на карте*, куда вы бы хотели получить доставку.'));
                        
                        setUserValue($chat_id, array($user_data['last_question'] => $value,"last_question" => "coord"));
                    }else{
                        apiRequest("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown', "text" => 'Хм, что вы имели в виду? Я бы понял, если бы Вы указали стоимость *числом*!:)'));
                    }
                    break;
                default:
                    apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => 'Ччччё?'));
                    break;
            }
        }
      } else {
        apiRequest("sendMessage", array('chat_id' => $chat_id, 'parse_mode' => 'Markdown' /*HTML*/, "text" => '*Error!*'));
      }
    }
}