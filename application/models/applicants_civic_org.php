<?php

require_once "my_model.php";
class Applicants_Civic_Org extends My_Model {

	const DB_TABLE = 'applicants_civic_org';
	const DB_TABLE_PK = 'id';

	public $id;
	public $applicant_id;
	public $org_name;
	public $address;
	public $from;
	public $to;
	public $duration;
	public $position;
}