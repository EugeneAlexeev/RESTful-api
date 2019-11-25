<?php
namespace core;

use core\http\{
	Response,
	Request
};

class controller
{
	protected $user;
	private $params;
	protected $response;
	protected $access;
	protected $token;

    public function __construct($params=[], $access=[])
    {
		if (isset($params)) {
			$this->setParams($params);
		}

		if (isset($access)) {
			$this->setAccess($access);
		}

		$this->response = new Response();
		$this->request = new Request();
		$this->setHeaders();
    }

	private function setHeaders()
	{
		$this->response->setHeader('Access-Control-Allow-Origin: *');
		$this->response->setHeader('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
		$this->response->setHeader('Content-Type: application/json; charset=UTF-8');
	}
    
    public function setParams($params)
    {
        $this->params = $params;
    }

    public function setAccess($access)
	{
		$this->access = $access;
	}

	public function getParams()
    {
        return $this->params;
    }

	public function getParam($name ,$default = '')
    {
        return isset($this->params[$name]) ? $this->params[$name] : $default;
    }

	public function NotFound()
	{
		$this->response->sendStatus(404);
		$this->response->setContent(['error' => 'Sorry! This Route Not Found!']);
	}

	public function Unauthorized()
	{
		$this->response->sendStatus(401);
		$this->response->setContent(['error' => 'Need authorization!']);
	}

	private function beforeAction() {
		$auth = new auth();
		$this->user = $auth->getUser();
	}

	protected function accessibleForGuest()
	{
		return (in_array('guest', $this->access));
	}

	protected function accessibleForUser()
	{
		return (in_array('user', $this->access));
	}

    public function dispatch($action = 'index')
	{
		$this->beforeAction();

		if (empty($this->user) && !$this->accessibleForGuest()) {
			$action = 'Unauthorized';
		}

		if (!empty($this->user) && !$this->accessibleForUser()) {
			$action = 'NotFound';
		}

        if (!method_exists($this, $action)) {
			$action = 'NotFound';
        }

		call_user_func([$this, $action]);

		$this->response->render();
    }
}
