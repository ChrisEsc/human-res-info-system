<?php

require_once "my_model.php";
class Applicants extends My_Model {

	const DB_TABLE = 'applicants';
	const DB_TABLE_PK = 'id';

	public $id;
	public $fname;
	public $mname;
	public $lname;
	public $suffix;
	public $birthdate;
	public $birthplace;
	public $sex;
	public $civil_status;
	public $height;
	public $weight;
	public $blood_type;
	public $gsis_id_no;
	public $gsis_bp_no;
	public $pagibig_id_no;
	public $philhealth_no;
	public $sss_no;
	public $tin_no;
	public $agency_emp_no;
	public $citizenship;
	public $phone_no;
	public $tel_no;
	public $email_add;
	public $active;
}