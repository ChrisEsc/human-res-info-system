<?php

require_once "my_model.php";
class Modules_Group extends My_Model {

	const DB_TABLE = 'modules_group';
	const DB_TABLE_PK = 'id';

	public $id;
	public $description;
}