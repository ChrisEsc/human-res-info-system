<?php

require_once "my_model.php";
class Training_Types extends My_Model {

	const DB_TABLE = 'training_types';
	const DB_TABLE_PK = 'id';

	public $id;
	public $description;
}