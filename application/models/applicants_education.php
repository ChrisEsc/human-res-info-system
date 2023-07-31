<?php

require_once "my_model.php";
class Applicants_Education extends My_Model {

	const DB_TABLE = 'applicants_education';
	const DB_TABLE_PK = 'id';

	public $id;
	public $applicant_id;
	public $educ_level;
	public $school;
	public $course;
	public $from_year;
	public $to_year;
	public $highest_level;
	public $units_earned;
	public $year_grad;
	public $acad_honor;
}