<?php

require_once "my_model.php";
class Selection_Lineup_Vacancies extends My_Model {

	const DB_TABLE = 'selection_lineup_vacancies';
	const DB_TABLE_PK = 'id';

	public $id;
	public $header_id;
	public $vacancy_id;
	public $selected_lineup_applicant_id;
	public $date_opened;
	public $date_psb;
	public $is_locked;
}