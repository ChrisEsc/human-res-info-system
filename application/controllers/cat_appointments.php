<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cat_Appointments extends CI_Controller {
	/**
	*/
	private function modulename($type) {
		if($type == 'link')
			return 'cat_appointments';
		else
			return 'Appointments';
	}

	public function index() {
		$this->load->model('Page');
		$this->Page->set_page($this->modulename('link'));
	}

	public function appointments_list() {
		try {
			$query = $this->esc_str($_GET['query']);
			$appointment_type = $_GET['appointment_type'];
			$appointment_status = $_GET['appointment_status'];
			die(json_encode($this->generateappointments_list($query, $appointment_type, $appointment_status, 'Grid')));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();
		}
	}

	public function generateappointments_list($query, $appointment_type, $appointment_status, $transaction_type) {
		try {
			$this->load->library('session');

			$limitQuery = "";
			if($transaction_type == 'Grid') {
				$limit = $_GET['limit'];
				$start = $_GET['start'];
				$limitQuery = " LIMIT $start, $limit";
			}

			// type => 1-original, 2-promotion, 3-reappointment, 4-reemploy, 5-transfer, 6-all
			$appointment_type_filter = "";
			if($appointment_type != 6) {
				$appointment_type_filter = "AND a.appointment_type = '$appointment_type'";
			}

			// appointments => 1-approved, 2-pending, 3-cancelled, 4-disapproved, 5-all
			$appointment_status_desc = ""; $appointment_status_filter = " AND a.appointment_date_from IS NOT NULL";
			if($appointment_status == 1) {$appointment_status_desc = 'Approved'; $appointment_status_filter = "AND a.appointment_status = 'Approved'";}
			else if($appointment_status == 2) {$appointment_status_desc = 'Pending'; $appointment_status_filter = "AND a.appointment_status = 'Pending'";}
			else if($appointment_status == 3) {$appointment_status_desc = 'Cancelled'; $appointment_status_filter = "AND a.appointment_status = 'Cancelled'";}
			else if($appointment_status == 4) {$appointment_status_desc = 'Disapproved'; $appointment_status_filter = "AND a.appointment_status = 'Disapproved'";}

			$commandText = "SELECT
								a.id,
								a.appointee,
								a.plantilla_item_no,
								a.item_code,
								a.item_desc,
								IF(a.item_desc_detail IS NULL, '', a.item_desc_detail) AS item_desc_detail,
								IF(a.item_desc_detail IS NULL, a.item_desc, CONCAT(a.item_desc, ' ', a.item_desc_detail)) AS item_desc_full,
								a.depcode,
								a.appointment_type,
								a.appointment_status, 
								IF(a.appointment_date_from IS NULL, '', DATE_FORMAT(a.appointment_date_from, '%e %b %Y')) AS effectivity,
								a.vice_name,
								IF(a.csc_action_date IS NULL OR a.csc_action_date = '0000-00-00 00:00:00', '', DATE_FORMAT(a.csc_action_date, '%e %b %Y')) AS csc_action_date,
								IF(b.plantilla_item_no IS NULL, '', b.plantilla_item_no) AS vacated_item_no, 
								IF(b.item_code IS NULL, '', b.item_code) AS vacated_item_code,
								IF(b.item_desc IS NULL, '', 
									IF(b.item_desc_detail IS NULL, b.item_desc, CONCAT(b.item_desc, ' ', b.item_desc_detail))) AS vacated_item_desc_full,
								-- IF(b.item_desc_detail IS NULL, b.item_desc, CONCAT(b.item_desc, ' ', b.item_desc_detail)) AS vacated_item_desc_full,
								IF(b.depcode IS NULL, '', b.depcode) AS vacated_depcode
							FROM vacancies a
								LEFT JOIN vacancies b ON b.plantilla_item_id = a.prev_item_id
							WHERE  (
									a.item_desc LIKE '%$query%'
									OR a.item_code LIKE '%$query%'
									OR a.item_desc_detail LIKE '%$query%'
									OR a.depcode LIKE '%$query%'
									OR a.appointment_remarks LIKE '%$query%'
									OR a.public_status LIKE '%$query%'
								)
								AND a.active = 1
								$appointment_type_filter
								$appointment_status_filter
							ORDER BY a.appointment_date_from DESC
							$limitQuery";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$commandText = "SELECT COUNT(a.id) AS count
							FROM vacancies a
								LEFT JOIN vacancies b ON b.plantilla_item_id = a.prev_item_id
							WHERE  (
									a.item_desc LIKE '%$query%'
									OR a.item_code LIKE '%$query%'
									OR a.item_desc_detail LIKE '%$query%'
									OR a.depcode LIKE '%$query%'
									OR a.appointment_remarks LIKE '%$query%'
									OR a.public_status LIKE '%$query%'
								)
								AND a.active = 1
								$appointment_type_filter
								$appointment_status_filter";
			$result = $this->db->query($commandText);
			$query_count = $result->result();

			if(count($query_result) == 0 & $transaction_type == 'Report') {
				$data = array("success"=> false, "data"=>'No records found!');
				die(json_encode($data));
			}
			
			if(count($query_result) == 0 & $transaction_type == 'Grid') {
				$data["totalCount"] = 0;
				$data["data"] 		= array();
				die(json_encode($data));
			}

			foreach($query_result as $key => $val) {
				$data['data'][] = array(
					'id' 					=> $val->id,
					'appointee' 			=> $val->appointee,
					'plantilla_item_no' 	=> $val->plantilla_item_no,
					'item_code' 			=> $val->item_code,
					'item_desc' 			=> $val->item_desc,
					'item_desc_detail' 		=> $val->item_desc_detail,
					'item_desc_full' 		=> $val->item_desc_full,
					'depcode' 				=> $val->depcode,
					'appointment_type' 		=> $val->appointment_type,
					'appointment_status' 	=> $val->appointment_status,
					'effectivity'			=> $val->effectivity,
					'vice_name' 			=> $val->vice_name,
					'csc_action_date' 		=> $val->csc_action_date,
					'vacated_item_no' 		=> $val->vacated_item_no,
					'vacated_item_code'		=> $val->vacated_item_code,
					'vacated_item_desc_full'=> $val->vacated_item_desc_full,
					'vacated_depcode' 		=> $val->vacated_depcode
				);
			}

			$data['totalCount'] = $query_count[0]->count;
			$data['appointment_status_desc'] = $appointment_status_desc;
			return $data;
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();
		}
	}

	private function esc_str($x) {
		return mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($x)));
	}
}