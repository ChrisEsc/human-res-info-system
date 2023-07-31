<?php

require_once "my_model.php";
class Ssltable extends My_Model {

	const DB_TABLE = 'ssltable';
	const DB_TABLE_PK = 'id';

	public $id;
	public $sal_grade;
	public $step_1;
	public $step_2;
	public $step_3;
	public $step_4;
	public $step_5;
	public $step_6;
	public $step_7;
	public $step_8;
	public $year;
	public $ssl_status;
}