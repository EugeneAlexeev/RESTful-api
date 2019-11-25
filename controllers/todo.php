<?php
namespace controllers;

class todo extends \core\controller
{
	public function create()
	{
		$model = new \models\todo();

		$data = [
			'title'   => $this->request->post('title'),
			'desc'    => $this->request->post('desc'),
			'status'  => $this->request->post('status'),
			'user_id' => $this->user->id
		];

		$todo = $model->create($data);

		if (!$todo) {
			$this->response->sendStatus(400);
			$this->response->setContent(['errors' => $model->getErrors()]);
		}
		else {
			$this->response->sendStatus(201);
			$this->response->setContent(['msg' => 'ToDo created!']);
		}
	}

	public function update()
	{
		$id = (int) $this->getParam('id');

		if (!$id) {
			$this->response->sendStatus(400);
			$this->response->setContent(['errors' => 'ID not defined']);
			return;
		}

		$model = new \models\todo();

		$data = [
			'todo_id' => $id,
			'status'  => $this->request->input('status'),
			'user_id' => $this->user->id
		];

		$c = $model->update($data);

		if (!$c) {
			$this->response->sendStatus(404);
			$this->response->setContent(['errors' => $model->getErrors()]);
		}
		else {
			$this->response->sendStatus(200);
			$this->response->setContent(['msg' => 'ToDo updated!']);
		}
	}

	public function getById()
	{
		$id = (int) $this->getParam('id');

		if (!$id) {
			$this->response->sendStatus(400);
			$this->response->setContent(['errors' => 'ID not defined']);
			return;
		}

		$model = new \models\todo();

		$data = [
			'todo_id' => $id,
			'user_id' => $this->user->id
		];

		$todo = $model->getById($data);

		if ($todo) {
			$this->response->sendStatus(200);
			$this->response->setContent($todo);
		}
		else {
			$this->response->sendStatus(404);
			$this->response->setContent(['errors' => $model->getErrors()]);
		}
	}

	public function getIdsByFilter()
	{
		$model = new \models\todo();

		$data = [
			'title'   => $this->request->input('title'),
			'desc'    => $this->request->input('desc'),
			'status'  => $this->request->input('status'),
			'user_id' => $this->user->id
		];

		$ids = $model->getIdsByFilter($data);

		if (is_array($ids) && count($ids)) {
			$this->response->sendStatus(200);
			$this->response->setContent($ids);
		}
		else {
			$this->response->sendStatus(404);
			$this->response->setContent(['errors' => $model->getErrors()]);
		}
	}

	public function delete()
	{
		$id = (int) $this->getParam('id');

		if (!$id) {
			$this->response->sendStatus(400);
			$this->response->setContent(['errors' => 'ID not defined']);
			return;
		}

		$model = new \models\todo();

		$data = [
			'todo_id' => $id,
			'user_id' => $this->user->id
		];

		$c = $model->delete($data);

		if ($c) {
			$this->response->sendStatus(200);
			$this->response->setContent(['msg' => 'ToDo deleted :(']);
		}
		else {
			$this->response->sendStatus(404);
			$this->response->setContent(['errors' => $model->getErrors()]);
		}
	}
}