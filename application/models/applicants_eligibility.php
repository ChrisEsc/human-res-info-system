<?php

require_once "my_model.php";
class Applicants_Eligibility extends My_Model {

	const DB_TABLE = 'applicants_eligibility';
	const DB_TABLE_PK = 'id';

	public $id;
	public $applicant_id;
	public $title;
	public $rating;
	public $exam_date;
	public $exam_place;
	public $license_no;
	public $date_validity;
}