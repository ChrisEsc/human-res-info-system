<?php

require_once "my_model.php";
class Approver_Transactions extends My_Model {

	const DB_TABLE = 'approver_transactions';
	const DB_TABLE_PK = 'id';

	public $id;
	public $code;
	public $description;
	public $active;
}