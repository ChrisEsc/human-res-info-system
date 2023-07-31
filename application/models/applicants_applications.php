<?php

require_once "my_model.php";
class Applicants_Applications extends My_Model {

	const DB_TABLE = 'applicants_applications';
	const DB_TABLE_PK = 'id';

	public $id;
	public $applicant_id;
	public $date_application_received;
	public $position_applied;
	public $notes;
	public $applic_type;
	public $active;
}