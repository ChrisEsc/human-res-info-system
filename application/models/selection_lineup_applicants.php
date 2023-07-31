<?php

require_once "my_model.php";
class Selection_Lineup_Applicants extends My_Model {

	const DB_TABLE = 'selection_lineup_applicants';
	const DB_TABLE_PK = 'id';

	public $id;
	public $lineup_vacancy_id;
	public $applicant_id;
	public $date_lineup;
	public $status_hr_test;
	public $date_hr_test;
	public $remarks_hr_test;
	public $status_interview;
	public $date_interview;
	public $remarks_interview;
	public $is_done_bi;
	public $is_done_paf;
	public $is_done_nir;
	public $remarks;
	public $status_psb;
	public $is_selected;
}