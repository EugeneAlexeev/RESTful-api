<?php
namespace core\http;

class Request {

    public $request;

    public function __construct() {
        $this->request = ($_REQUEST);
    }

    public function get(String $key = '') {
        if ($key != '')
            return isset($_GET[$key]) ? $this->clean($_GET[$key]) : null;
        return  $this->clean($_GET);
    }

    public function post(String $key = '') {
        if ($key != '')
            return isset($_POST[$key]) ? $this->clean($_POST[$key]) : null;
        return  $this->clean($_POST);
    }

    public function input(String $key = '') {
        $body = file_get_contents("php://input");
		parse_str($body, $body);
        if ($key != '') {
            return isset($body[$key]) ? $this->clean($body[$key]) : null;
        } 
        return ($body);
    }

    public static function server(String $key = '') {
        return isset($_SERVER[strtoupper($key)]) ? self::clean($_SERVER[strtoupper($key)]) : self::clean($_SERVER);
    }

    public static function getMethod() {
        return strtoupper(self::server('REQUEST_METHOD'));
    }

    public static function getUrl() {
        return self::server('REQUEST_URI');
    }

    public static function clean($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $data[self::clean($key)] = self::clean($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
        }
        return $data;
    }
}