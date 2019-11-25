<?php
namespace core;

use core\JWT\JWT;
use core\http\Request;
use controllers\user;

class auth
{
	public function __construct()
	{
		$this->secret_key = \core\app::instance()->getSecretKey();
	}

	public function getUser()
	{
		$JWT_token = $this->getToken();

		if (!$JWT_token) {
			return false;
		}

		try {
			$decoded = JWT::decode($JWT_token, $this->secret_key, array('HS256'));
		} catch (Exception $e) {
			// сообщать пользователю если токен истек
            //echo $e->getMessage();
        }

		$user = $decoded->data->user;

		$user->id = (int) $user->id;

		return $user;
	}

	private function getToken()
	{
		$token = (isset($_SERVER['HTTP_X_AUTHORIZATION'])) ? $_SERVER['HTTP_X_AUTHORIZATION'] : '';

		if (empty($token)) {
			return false;
		}

		$token = Request::clean($token);

		list($JWT_token) = sscanf($token, 'Bearer %s');

		if (empty($JWT_token)) {
			return false;
		}

		return $JWT_token;
	}

	public function setToken($user)
	{
        $issuedAt = time();
        $payload  = array(
            'iss' => Request::server('SERVER_NAME'),
            'iat' => $issuedAt,
            'nbf' => $issuedAt,
            'exp' => time()+(86400*7),
            'data' => [
                'user' => [
                    'id'    => $user['user_id'],
				    'login' => $user['login']
                ],
            ],
        );
        /** Let the user modify the token data before the sign. */
        $token = JWT::encode($payload, $this->secret_key);
		return $token;
	}
}
