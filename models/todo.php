<?php
namespace models;

class todo
{
	private $errors;

	public function __construct()
	{
		$this->db = \core\app::instance()->getDB();
	}

	public function create($data)
	{
		if (!$this->validateData($data))
		{
			return false;
		}

		$data = $this->formatData($data);
        $this->saveData($data);
		return true;
	}

	public function update($data)
	{
		if (!$this->validateDataForUpdate($data))
		{
			return false;
		}

        $rowCountUpdated = $this->updateData($data);

		if (!$rowCountUpdated) {
			$this->errors[] = 'Nothing to update.';
		}

		return $rowCountUpdated ? true : false;
	}

	private function validateDataForUpdate($data)
	{
		$errors = [];

		if (empty($data['status']))
		{
			$errors[] = 'The status cannot be empty!';
		}

		if (count($errors)) {
			$this->setErrors($errors);
			return false;
		}

		return true;
	}

	private function formatData($data)
	{
		$data = [
			'title'   => (string) $data['title'],
			'desc'    => (string) $data['desc'],
			'status'  => (string) $data['status'],
			'user_id' => (int) $data['user_id']
		];
		
		return $data;
	}

	private function validateData($data)
	{
		$errors = [];
		if (empty($data['title']))
		{
			$errors[] = 'The title cannot be empty!';
		}

		if (empty($data['desc']))
		{
			$errors[] = 'The description cannot be empty!';
		}

		if (empty($data['status']))
		{
			$errors[] = 'The status cannot be empty!';
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
			'into'	 => 'todos',
			'values' => $data
		]);
	}

	private function updateData($data)
	{
		$this->db->update([
			'update' => 'todos',
			'set'    => [
				'status' => $data['status']
			],
			'where'  => 'todo_id = '.$data['todo_id'].' AND user_id = '.$data['user_id']
		]);

		return $this->db->affected_rows();
	}

	public function getById($data)
	{
		$errors = [];
		$this->db->select([
			'select' => '*',
			'from'   => 'todos',
			'where'  => 'todo_id = '. $data['todo_id'] . ' AND user_id = '.$data['user_id']
		]);

		if (!$this->db->num_rows())
		{
			$errors[] = 'ToDo not Found!';
		}

		if (count($errors)) {
			$this->setErrors($errors);
			return false;
		}

		$todo = $this->db->fetch_assoc();
		return $todo;
	}

	public function delete($data)
	{
		$errors = [];

		$rowCountDeleted = $this->deleteData($data);

		if (!$rowCountDeleted) {
			$this->errors[] = 'Nothing to delete :)';
		}

		return $rowCountDeleted ? true : false;
	}

	private function deleteData($data)
	{
		$this->db->delete([
			'from'   => 'todos',
			'where'  => 'todo_id = '. $data['todo_id'] . ' AND user_id = '.$data['user_id']
		]);

		return $this->db->affected_rows();
	}

	public function getIdsByFilter($data)
	{
		$errors = [];
		$where = [];

		$where[] = 'user_id = '.$data['user_id'];

		if (!empty($data['status'])) {
			$where[] = '"status" = '."'".$data['status']."'";
		}

		if (!empty($data['title'])) {
			$where[] = '"title" LIKE ' . "'%".$data['title']."%'";
		}

		if (!empty($data['desc'])) {
			$where[] = '"desc" LIKE ' . "'%".$data['desc']."%'";
		}

		$where = implode(' AND ', $where);

		$this->db->select([
			'select' => 'todo_id',
			'from'   => 'todos',
			'where'  => $where
		]);

		if (!$this->db->num_rows())
		{
			$errors[] = 'ToDo not Found!';
		}

		if (count($errors)) {
			$this->setErrors($errors);
			return false;
		}

		$ids = [];
		while ($todo = $this->db->fetch_assoc()) {
			$ids[] = $todo['todo_id'];
		}
		return $ids;
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