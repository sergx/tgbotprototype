<?php

class DB extends basicClass {
  
    private $link;
    public function __construct($hostname, $username, $password, $database) {
        if (!$this->link = mysql_connect($hostname, $username, $password)) {
            trigger_error('Error: Could not make a database link using ' . $username . '@' . $hostname);
        }

        if (!mysql_select_db($database, $this->link)) {
            trigger_error('Error: Could not connect to database ' . $database);
        }
        mysql_query("SET NAMES 'utf8'", $this->link);
        mysql_query("SET CHARACTER SET utf8", $this->link);
        mysql_query("SET CHARACTER_SET_CONNECTION=utf8", $this->link);
        mysql_query("SET SQL_MODE = ''", $this->link);
    }
    public function query($sql, $full_requerst = false) {
        if ($this->link) {
            $resource = mysql_query($sql, $this->link);
            if ($resource) {
                if (is_resource($resource)) {
                    $i = 0;
                    $data = array();

                    while ($result = mysql_fetch_assoc($resource)) {
                        $data[$i] = $result;
                        $i++;
                    }

                    mysql_free_result($resource);
                    if($full_requerst){
                        $query = new stdClass();
                        $query->row = isset($data[0]) ? $data[0] : array();
                        $query->rows = $data;
                        $query->num_rows = $i;
                    }else{
                        if(count($data) > 1){
                            $query = $data;
                        }else{
                            if(strpos(strtolower($sql),"limit 1")){
                                $query = $data[0];
                            }else{
                                $query = $data;
                            }
                        }
                    }

                    unset($data);

                    return $query;
                } else {
                    return true;
                }
            } else {
              $this->answer->techLog("Error: " . mysql_error($this->link) . "\r\nError No: " . mysql_errno($this->link) . "\r\n" . $sql);
                //trigger_error('Error: ' . mysql_error($this->link) . '<br />Error No: ' . mysql_errno($this->link) . '<br />' . $sql);
                exit();
            }
        }
    }

    public function escape($value) {
        if ($this->link) {
            return mysql_real_escape_string($value, $this->link);
        }
    }

    public function countAffected() {
        if ($this->link) {
            return mysql_affected_rows($this->link);
        }
    }

    public function getLastId() {
        if ($this->link) {
            return mysql_insert_id($this->link);
        }
    }

    public function lastError() {
        if ($this->link) {
            return mysql_error($this->link);
        }
    }

    public function __destruct() {
        if ($this->link) {
            mysql_close($this->link);
        }
    }
}