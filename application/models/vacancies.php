<?php

require_once "my_model.php";
class Vacancies extends My_Model {

	const DB_TABLE = 'vacancies';
	const DB_TABLE_PK = 'id';

	public $id;
	public $plantilla_item_id;
	public $plantilla_item_no;
	public $item_code;
	public $item_desc;
	public $item_desc_detail;
	public $item_status;
	public $posgrade;
	public $poslevel;
	public $sal_step;
	public $sal_rate;
	public $depcode;
	public $occupant;
	public $occupant_desc;
	public $effectivity; 			// effectivity of the vacancy OR effectivity of the new appointment
	public $appointment_type; 		// appointment data
	public $appointee; 				// appointment data
	public $vice_emp_no; 			// appointment data
	public $vice_name; 				// appointment data
	public $csc_action_date; 		// appointment data
	public $appointment_date_from; 	// appointment data
	public $appointment_status; 	// appointment data
	public $appointment_remarks; 	// appointment data
	public $prev_item_id;			// previous item id of the appointee before promotion
	public $latest_posting; 		// posting data
	public $public_status; 			// posting data
	public $is_vacant;
	public $active;
}