<?php

require_once "my_model.php";
class Approver_List extends My_Model {

	const DB_TABLE = 'approver_list';
	const DB_TABLE_PK = 'id';

	public $id;
	public $approver_hierarchy_id;
	public $approver_id;
}