<?php

require_once "my_model.php";
class Applicants_Family extends My_Model {

	const DB_TABLE = 'applicants_family';
	const DB_TABLE_PK = 'id';

	public $id;
	public $applicant_id;
	public $relationship;
	public $fname;
	public $mname;
	public $surname;
	public $suffix;
	public $maidenname;
	public $birthdate;
	public $occupation;
	public $empl_biz_name;
	public $address;
	public $tel_no;
}