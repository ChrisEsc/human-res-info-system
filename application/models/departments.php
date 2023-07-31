<?php

require_once "my_model.php";
class Departments extends My_Model {

	const DB_TABLE = 'departments';
	const DB_TABLE_PK = 'id';

	public $id;
	public $depcode;
	public $description;
}