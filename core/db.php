<?php
namespace core;

defined('inc') or exit('Доступ закрыт!');

//-----------------------------------------------
// Класс для работы с БД.
// 
// Комментировать тут ничего не стал, так как 
// нечего. Если надо - читаем документацию по
// MySQLi.
// ----------------------------------------------

class db {
	var $cfg	= [];		// массив конфига для коннекта к базе
	var $conn	= '';		// идентификатор коннекта
	var $q		= '';		// составленный запрос
	var $query	= '';		// выполненный зарпос
	var $nums	= 0;		// для возвращенного числа строк
	var $rows	= 0;
	var $qqs	= [];		// массив для хранения запросов, сделаных за один сеанс работы скрипта
	var $qq_num	= 0;		// счетчик числа запросов
	var $error = false;
	var $show_error = true;

//-----------------------------------------------
// Соединяемся с сервером базы и выбираем базу, 
// а заодно сопоставляем кодировку соединения
//-----------------------------------------------
	function connect() {
		$this->conn = @pg_connect(implode(' ',[
			'host='     . $this->cfg['db_host'],
			'port='     . $this->cfg['db_port'],
			'dbname='   . $this->cfg['db_name'],
			'user='     . $this->cfg['db_user'],
			'password=' . $this->cfg['db_pass']
		]));

		if ($this->conn) {
			pg_query( $this->conn , "SET NAMES '" . $this->cfg['db_encoding'] . "'" );
		}
		else {
			$this->error_db_no_conn();
		}
	}

//-----------------------------------------------
// Выполнение запроса
//-----------------------------------------------
	function query() {
		$this->query = pg_query( $this->conn, $this->q );

		if ($this->query) {
			$this->qq_num++;
			$this->qqs[$this->qq_num] = $this->q;
		}
		else {
			$this->error = pg_last_error( $this->conn );
			if ($this->show_error) {
				$this->error_db();
			}
		}
	}

	function free_result() {
		pg_free_result( $this->query );
	}

//-----------------------------------------------
// Выбор из базы
//-----------------------------------------------
	function select($a) {
		$q = 'SELECT '.$a['select'].' FROM '.$this->cfg['db_tb_p'].$a['from'];

		if (isset($a['join']) AND is_array($a['join'])) {
			$q .= ' LEFT JOIN '.$this->cfg['db_tb_p'].$a['join']['from'].' ON ('.$a['join']['where'].')';
		}

		/*if (isset($a['left_join']) AND is_array($a['left_join'])) {
			$q .= ' LEFT JOIN '.$this->cfg['db_tb_p'].$a['left_join']['table'].' ON ('.$a['left_join']['on'].')';
		}*/

		if ( isset($a['left_join'] ) && is_array( $a['left_join'] ) ) {
			if ( isset( $a['left_join'][0] ) && is_array( $a['left_join'][0] ) ) {
				foreach( $a['left_join'] as $v ) {
					$q .= ' LEFT JOIN ' . $this->cfg['db_tb_p'] . $v['table'] . ' ON (' . $v['on'] . ')';
				}
			}
			else {
				$q .= ' LEFT JOIN '.$this->cfg['db_tb_p'].$a['left_join']['table'].' ON ('.$a['left_join']['on'].')';
			}
		}

		if ( isset($a['inner_join'] ) && is_array( $a['inner_join'] ) ) {
			if ( isset( $a['inner_join'][0] ) && is_array( $a['inner_join'][0] ) ) {
				foreach( $a['inner_join'] as $v ) {
					$q .= ' INNER JOIN ' . $this->cfg['db_tb_p'] . $v['table'] . ' ON (' . $v['on'] . ')';
				}
			}
			else {
				$q .= ' INNER JOIN ' . $this->cfg['db_tb_p'] . $a['inner_join']['table'] . ' ON (' . $a['inner_join']['on'] . ')';
			}
		}

		if (isset($a['where']) AND $a['where']) {
			$q .= ' WHERE '.$a['where'];
		}

		if (isset($a['group']) AND $a['group']) {
			$q .= ' GROUP BY '.$a['group'];
		}

		if (isset($a['having']) AND $a['having']) {
			$q .= ' HAVING '.$a['having'];
		}

		if (isset($a['order']) AND $a['order']) {
			$q .= ' ORDER BY '.$a['order'];
		}

		if (isset($a['limit']) AND $a['limit']) {
			$q .= ' LIMIT '.$a['limit'];
		}

		$q .= ';';

		$this->q = $q;
		unset($q,$a);
		$this->query();
	}

//-----------------------------------------------
// Запись в базу
//-----------------------------------------------
	function insert($a,$t='') {
		$names	= array();
		$values	= array();

		foreach ($a['values'] as $n => $v) {
			$names[] = '"'.$n.'"';
			$values[] = (!is_null($v)) ? "'".$this->escape($v)."'" : 'NULL';
			unset($n,$v);
		}

		$names	= implode(', ', $names);
		$values	= implode(', ', $values);

		$t = in_array(strtoupper($t),array('IGNORE')) ? ' '.strtoupper($t) : '';

		$this->q = 'INSERT'.$t.' INTO '.$this->cfg['db_tb_p'].$a['into'].' ('.$names.') VALUES ('.$values.');';
		unset($names,$values,$a,$t);
		$this->query();
	}

//-----------------------------------------------
// Запись в базу - множественная вставка
//-----------------------------------------------
	function inserts($a,$t='') {
		$names	= array();
		$first_value = array_shift($a['values']);
		foreach ($first_value as $n => $v) {
			$names[] = $n;
			unset($n,$v);
		}
		array_unshift($a['values'], $first_value);
		$names	= implode(', ', $names);

		$valueses = array();
		foreach ($a['values'] as $e) {
			$values	= array();

			foreach ($e as $v) {
				$values[] = (!is_null($v)) ? "'".$this->escape($v)."'" : 'NULL';
				unset($v);
			}

			$valueses[]	= implode(', ', $values);
			unset($e,$values);
		}

		$valueses	= implode('), (', $valueses);

		$odku = '';
		if (isset($a['odku']) && count($a['odku']) > 0) {
			$odku = array();
			$i = 0;
			foreach ($a['odku'] as $k => $v) {
				if ($i == $k) {
					$odku[] = $v." = VALUES('".$v."')";
					$i++;
				}
				else {
					$odku[] = $k.' = '.(!is_null($v) ? "'".$this->escape($v)."'" : 'NULL');
				}
				unset($k,$v);
			}
			unset($i);
			$odku = ' ON DUPLICATE KEY UPDATE '.implode(', ', $odku);
		}

		$t = in_array(strtoupper($t),array('IGNORE')) ? ' '.strtoupper($t) : '';

		$this->q = 'INSERT'.$t.' INTO '.$this->cfg['db_tb_p'].$a['into'].' ('.$names.') VALUES ('.$valueses.')'.$odku.';';
		unset($a,$t,$names,$valueses,$odku);
		$this->query();
	}

//-----------------------------------------------
// Обновление
//-----------------------------------------------
	function update($a,$is_field=0) {
		$set = array();
		foreach ($a['set'] as $n => $v) {
			if (!$is_field) {
				$v = (!is_null($v)) ? "'".$this->escape($v)."'" : 'NULL';
			}
			$set[] = $n.' = '.$v;
			unset($n,$v);
		}

		$this->q = 'UPDATE '.$this->cfg['db_tb_p'].$a['update'].' SET '.implode(', ', $set);
		unset($set);

		if (isset($a['where']) AND $a['where']) {
			$this->q .= ' WHERE '.$a['where'];
		}
		unset($a);

		$this->q .= ';';

		$this->query();
	}

//-----------------------------------------------
// Удаление
//-----------------------------------------------
	function delete($a) {
		$this->q = 'DELETE FROM '.$this->cfg['db_tb_p'].$a['from'];

		if (isset($a['where']) AND $a['where']) {
			$this->q .= ' WHERE '.$a['where'];
		}
		unset($a);

		$this->q .= ';';
		$this->query();
	}

/*-----------------------------------------------
	Получение последнего вставленного id
-----------------------------------------------*/
	function last_id() {
		return 0;//
	}

//-----------------------------------------------
// Функции для работы с полученными данными
//-----------------------------------------------
	function fetch_array() {
		return pg_fetch_array( $this->query );
	}

	function fetch_assoc() {
		return pg_fetch_assoc( $this->query );
	}

	function fetch_row() {
		return pg_fetch_row( $this->query );
	}

//-----------------------------------------------
// Очищаем вводимы данные для mysql
//-----------------------------------------------
	function escape($a) {
		if (!get_magic_quotes_gpc()) {
			$a = pg_escape_string( $this->conn , $a );
		}
		else {
			$a = $a;
		}

		return $a;
	}
//-----------------------------------------------
// Считаем кол-во возвращенныз строк
//-----------------------------------------------
	function num_rows() {
		$this->nums = pg_num_rows( $this->query );
		return $this->nums;
	}

	function affected_rows() {
		$this->rows = pg_affected_rows( $this->query );
		return $this->rows;
	}


//-----------------------------------------------
// Если ошибка, то принудительно завершаем работу скрипта
//-----------------------------------------------
	function error_db() {
		header('Content-Type: text/html; charset=utf-8');
		echo '<h3>Возникла ошибка при работе с базой '.$this->cfg['db_name'].':</h3>'."\n";
		echo '<b>Ошибка:</b> <font color="red">'.@pg_last_error( $this->conn ).'</font><br>'."\r\n";
		echo '<b>Запрос:</b> <font color="blue">'.$this->q.'</font>';
		exit();
	}

// Если ошибка, то принудительно завершаем работу скрипта
	function error_db_no_conn() {
		header('Content-Type: text/html; charset=utf-8');
		echo 'Нет подключения к базе '.$this->cfg['db_name'].'.'."\n";
		exit();
	}

//-----------------------------------------------
// Закрываем наш коннект к базе
//-----------------------------------------------
	function close() {
		pg_close( $this->conn );
	}
}
