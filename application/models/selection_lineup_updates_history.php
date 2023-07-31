<?php

require_once "my_model.php";
class Selection_Lineup_Updates_History extends My_Model {

	const DB_TABLE = 'selection_lineup_updates_history';
	const DB_TABLE_PK = 'id';

	public $id;
	public $selection_lineup_applicants_id;
	public $updated_by;
	public $prev_values;
	public $date_logged;
}