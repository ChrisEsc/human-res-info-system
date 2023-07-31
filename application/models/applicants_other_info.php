<?php

require_once "my_model.php";
class Applicants_Other_Info extends My_Model {

	const DB_TABLE = 'applicants_other_info';
	const DB_TABLE_PK = 'id';

	public $id;
	public $applicant_id;
	public $type;
	public $description;
}