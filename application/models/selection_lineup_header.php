<?php

require_once "my_model.php";
class Selection_Lineup_Header extends My_Model {

	const DB_TABLE = 'selection_lineup_header';
	const DB_TABLE_PK = 'id';

	public $id;
	public $item_details;
	public $date_opened;
}