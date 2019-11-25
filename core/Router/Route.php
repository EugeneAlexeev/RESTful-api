<?php
namespace core\Router;

final class Route {

    private $method;
    private $pattern;
    private $callback;
	private $access;
    private $list_method = [
		'GET',
		'POST',
		'PUT',
		'DELETE'
	];

	public function __construct(String $method, String $pattern, $callback, $access) {
        $this->method = $this->validateMethod(strtoupper($method));
        $this->pattern = $pattern;
        $this->callback = $callback;
		$this->access = $access;
    }

    private function validateMethod(string $method) {
        if (in_array($method, $this->list_method)) 
            return $method;
        
        throw new \Exception('Invalid Method Name');
    }

    public function getMethod() {
        return $this->method;
    }

    public function getPattern() {
        return $this->pattern;
    }

    public function getCallback() {
        return $this->callback;
    }

	public function getAccess() {
        return $this->access;
    }
}
