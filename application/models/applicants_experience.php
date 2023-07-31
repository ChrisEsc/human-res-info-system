<?php

require_once "my_model.php";
class Applicants_Experience extends My_Model {

	const DB_TABLE = 'applicants_experience';
	const DB_TABLE_PK = 'id';

	public $id;
	public $applicant_id;
	public $employment_status_id;
	public $from_date;
	public $to_date;
	public $position;
	public $agency_company;
	public $monthly_salary;
	public $salary_grade;
	public $government_service;
}