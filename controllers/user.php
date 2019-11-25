<?php
namespace controllers;

class user extends \core\controller
{
	public function create()
	{
		$model = new \models\user();

		$data = [
			'login'    => $this->request->post('login'),
			'password' => $this->request->post('password')
		];

		$user = $model->create($data);

		if (!$user) {
			$this->response->sendStatus(400);
			$this->response->setContent(['errors' => $model->getErrors()]);
		}
		else {
			$this->response->sendStatus(201);
			$this->response->setContent(['msg' => 'User created!']);
		}
	}

	public function login()
	{
		$model = new \models\user();

		$data = [
			'login'    => $this->request->post('login'),
			'password' => $this->request->post('password')
		];

		$user = $model->getUserByData($data);

		if (!$user) {
			$this->response->sendStatus(401);
			$this->response->setContent(['error' => 'Wrong login or password!']);
		}
		else {
			$this->response->sendStatus(202);
			$this->response->setContent(['msg' => 'Successful!', 'JWT_token' => $this->getToken($user), 'desc' => 'Use this token in HTTP Header with attr. Authorization. Example header: "Authorization: Bearer #token#"']);
		}
	}

	private function getToken($user)
	{
		$auth = new \core\auth();
		return $auth->setToken($user);
	}
}