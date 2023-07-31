<?php

require_once "my_model.php";
class Applicants_Training extends My_Model {

	const DB_TABLE = 'applicants_training';
	const DB_TABLE_PK = 'id';

	public $id;
	public $applicant_id;
	public $training_type_id;
	public $title;
	public $from_date;
	public $to_date;
	public $duration;
	public $conducted_by;
}