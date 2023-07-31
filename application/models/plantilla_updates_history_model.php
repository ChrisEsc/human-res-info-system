<?php

require_once "my_model.php";
class Plantilla_Updates_History_Model extends My_Model {

	const DB_TABLE = 'plantilla_updates_history';
	const DB_TABLE_PK = 'id';

	public $id;
	public $table_id;
	public $table;
	public $changes;
	public $date_updated;
}