<?php
namespace core\Router;

use core\http\Request;

class Router
{
    private $routes = [];
    private $url;
    private $method;
    private $params = [];

    public function __construct() {
        $this->url = Request::getUrl();
        $this->method = Request::getMethod();
    }

	public function get($pattern, $callback, $access) {
        $this->addRoute("GET", $pattern, $callback, $access);
    }

    public function post($pattern, $callback, $access) {
        $this->addRoute('POST', $pattern, $callback, $access);
    }

    public function put($pattern, $callback, $access) {
        $this->addRoute('PUT', $pattern, $callback, $access);
    }

    public function delete($pattern, $callback, $access) {
        $this->addRoute('DELETE', $pattern, $callback, $access);
    }

    public function addRoute($method, $pattern, $callback, $access) {
        array_push($this->routes, new Route($method, $pattern, $callback, $access));
    }

    public function find() {
        foreach ($this->routes as $value) {
			if (strtoupper($this->method) != $value->getMethod()) {
				continue;
			}

			if ($this->dispatch($value->getPattern())) {
				return $value;
			}
        }
		return false;
    }

    private function dispatch($pattern) {
        preg_match_all('@:([\w]+)@', $pattern, $params, PREG_PATTERN_ORDER);

        $patternAsRegex = preg_replace_callback('@:([\w]+)@', [$this, 'convertPatternToRegex'], $pattern);

        if (substr($pattern, -1) === '/' ) {
	        $patternAsRegex = $patternAsRegex . '?';
	    }
        $patternAsRegex = '@^' . $patternAsRegex . '$@';
        
        // check match request url
        if (preg_match($patternAsRegex, $this->url, $paramsValue)) {
            array_shift($paramsValue);
            foreach ($params[0] as $key => $value) {
                $val = substr($value, 1);
                if ($paramsValue[$val]) {
                    $this->setParam($val, urlencode($paramsValue[$val]));
                }
            }

            return true;
        }

        return false;
    }

    public function getRoutes() {
        return $this->routes;
    }

    private function setParam($key, $value) {
        $this->params[$key] = $value;
    }

	public function getParams() {
		return $this->params;
	}

    private function convertPatternToRegex($matches) {
        $key = str_replace(':', '', $matches[0]);
        return '(?P<' . $key . '>[a-zA-Z0-9_\-\.\!\~\*\\\'\(\)\:\@\&\=\$\+,%]+)';
    }
}
