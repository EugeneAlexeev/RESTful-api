<?php
defined('inc') or exit('Доступ закрыт!');
define('DATE_FORMAT_MYSQL','Y-m-d H:i:s'); // формат даты для запроса в базу. подробнее php.net/date

$cfg = [
	'db_host'		=> 'localhost',
	'db_port'		=> 5432,
	'db_user'		=> 'test',
	'db_pass'		=> 'test',
	'db_name'		=> 'postgres',
	'db_encoding'	=> 'UTF8',
	'db_tb_p'		=> '',

	'secret_key'	=> 'ZBmTVYS+TFNLP?yjiz4oqp!c6JL3NFQWd%gWbeMXX12U*).YZfbhA}"ObajYxOl-'
];