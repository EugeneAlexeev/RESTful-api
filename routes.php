<?php

class routes extends \core\Router\Router
{
    public function setRoutes()
	{
		// user
		$this->post(  '/users/',      'user/create',         ['guest']);  // зарегистрироваться
		$this->post(  '/users/login', 'user/login',          ['guest']);  // авторизоваться

		// todos
		$this->post(  '/todos/',      'todo/create',         ['user'] ); // создать todo
		$this->put(   '/todos/:id',   'todo/update',         ['user'] ); // изменить статус todo
		$this->get(   '/todos/:id',   'todo/getById',        ['user'] ); // получить todo по ID
		$this->get(   '/todos/',      'todo/getIdsByFilter', ['user'] ); // получить список ID с фильтрацией по статусу и поиску по подстроке в полях заголовок и описание
		$this->delete('/todos/:id',   'todo/delete',         ['user'] ); // удалить todo
	}
}
