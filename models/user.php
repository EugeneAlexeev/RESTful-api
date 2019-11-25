<?php
namespace models;

class user
{
	private $errors;

	public function __construct()
	{
		$this->db = \core\app::instance()->getDB();
	}

	public function getUserByData($data)
	{
		if (!$this->validateData($data))
		{
			return false;
		}

		$this->db->select([
			'select' => '*',
			'from'   => 'users',
			'where'  => "login = '" . $data['login'] . "'"
		]);

		if (!$this->db->num_rows()) {
			return false;
		}

		$record = $this->db->fetch_assoc();

		if ($record['hash'] != md5(md5($record['salt']).md5($data['password']))) {
			return false;
		}

		return $record;
	}

	public function create($data)
	{
		if (!$this->validateData($data))
		{
			return false;
		}

		if (!$this->freeLogin($data['login']))
		{
			return false;
		}

		$data = $this->formatData($data);

        $this->saveData($data);
		return true;
	}

	private function formatData($data)
	{
		$salt = $this->generate_password_salt();

		$data = [
			'login' => $data['login'],
			'hash'  => md5(md5($salt) . md5($data['password'])),
			'salt'  => $salt
		];
		
		return $data;
	}

	private function generate_password_salt($len=5)
	{
		$s = '';
		for ($i = 0; $i < $len; $i++) {
			$num = rand(33, 126);
			if ($num == 39) {$num = 40;} // '
			if ($num == 92) {$num = 93;} // \
			$s .= chr($num);
		}
		return $s;
	}

	private function freeLogin($login)
	{
		$errors = [];
		$this->db->select([
			'select' => 'login',
			'from'   => 'users',
			'where'  => "login = '" . $login . "'"
		]);
		
		if ($this->db->num_rows())
		{
			$errors[] = 'The specified login is already taken.';
		}

		if (count($errors)) {
			$this->setErrors($errors);
			return false;
		}

		return true;
	}

	private function saveData($data)
	{
		$this->db->insert([
			'into'	 => 'users',
			'values' => $data
		]);
	}

	private function validateData($data)
	{
		$errors = [];
		$loginPattern = '^\w{6,16}$';
		if (!preg_match("/".$loginPattern."/iu", $data['login']))
		{
			$errors[] = 'The login must match: "' . $loginPattern . '"';
		}

		$passwordPattern = '^\w{6,16}$';
		if (!preg_match("/".$passwordPattern."/iu", $data['password']))
		{
			$errors[] = 'The password must match: "' . $passwordPattern . '"';
		}

		if (count($errors)) {
			$this->setErrors($errors);
			return false;
		}

		return true;
	}

	private function setErrors($errors)
	{
		$this->errors = $errors;
	}

	public function getErrors()
	{
		return $this->errors;
	}
}
