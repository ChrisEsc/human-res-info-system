<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cat_Vacancies_Masterlist extends CI_Controller {
	/**
	*/ 
	private function modulename($type) {		
		if($type == 'link')
			return 'cat_vacancies_masterlist';
		else 
			return 'Vacancies Masterlist';
	}

	public function index() {
		// update from Plantilla DB first before loading Page model
		// $this->update_from_plantilla();
		$this->load->model('Page');
        $this->Page->set_page($this->modulename('link'));
	}

	public function vacancies_list() {
		try { 
			$query = $this->esc_str($_GET['query']);
			$appointment_status = $_GET['appointment_status'];
			$publication_status = $_GET['publication_status'];
			$show_all_items = $_GET['show_all_items'];
			die(json_encode($this->generatevacancies_list($query, $appointment_status, $publication_status, $show_all_items, 'Grid')));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();	
		}
	}
 	
	public function generatevacancies_list($query, $appointment_status, $publication_status, $show_all_items, $transaction_type) {
		try {
			$this->load->library('session');

			$limitQuery = "";
			if($transaction_type == 'Grid') {
				$limit = $_GET['limit'];
				$start = $_GET['start'];
				$limitQuery = " LIMIT $start, $limit";
			}

			// appointments => 1-approved, 2-pending, 3-cancelled, 4-disapproved, 5-all
			// publications => 1-published, 2-unpublished, 3-expiring, 4-expired, 5-all
			$appointment_status_desc = ""; $appointment_status_filter = "";
			if($appointment_status == 1) {$appointment_status_desc = 'Approved'; $appointment_status_filter = "AND appointment_status = 'Approved'";}
			else if($appointment_status == 2) {$appointment_status_desc = 'Pending'; $appointment_status_filter = "AND appointment_status = 'Pending'";}
			else if($appointment_status == 3) {$appointment_status_desc = 'Cancelled'; $appointment_status_filter = "AND appointment_status = 'Cancelled'";}
			else if($appointment_status == 4) {$appointment_status_desc = 'Disapproved'; $appointment_status_filter = "AND appointment_status = 'Disapproved'";}

			$publication_status_desc = ""; $publication_status_filter = "";
			if($publication_status == 1) {$publication_status_desc = 'Published'; $publication_status_filter = "AND (latest_posting > CURDATE() OR CURDATE() <= DATE_ADD(latest_posting, INTERVAL 8 MONTH))";}
			else if($publication_status == 2) {$publication_status_desc = 'Unpublished'; $publication_status_filter = "AND latest_posting IS NULL";}
			else if($publication_status == 3) {$publication_status_desc = 'Expiring'; $publication_status_filter = "AND (CURDATE() > DATE_ADD(latest_posting, INTERVAL 8 MONTH) AND CURDATE() <= DATE_ADD(latest_posting, INTERVAL 9 MONTH))";}
			else if($publication_status == 4) {$publication_status_desc = 'Expired'; $publication_status_filter = "AND CURDATE() > DATE_ADD(latest_posting, INTERVAL 9 MONTH)";}

			$show_all_items_filter = ($show_all_items == 1) ? "": "AND is_vacant = 1";
			$show_all_items_desc = ($show_all_items == 1) ? "Yes": "No";

			$commandText = "SELECT
								id,
								plantilla_item_no,
								item_code,
								item_desc,
								IF(item_desc_detail IS NULL, '', item_desc_detail) AS item_desc_detail,
								posgrade,
								depcode,
								occupant_desc,
								IF(appointment_remarks IS NULL, '', appointment_remarks) AS appointment_remarks,
								IF(latest_posting IS NULL, '', DATE_FORMAT(latest_posting, '%e %b %Y')) AS latest_posting,
								IF(public_status IS NULL, 'Unpublished', public_status) AS public_status,
								IF(latest_posting > CURDATE() OR CURDATE() <= DATE_ADD(latest_posting, INTERVAL 8 MONTH), 'Published',
									IF(latest_posting IS NULL, 'Unpublished', 
										IF(CURDATE() > DATE_ADD(latest_posting, INTERVAL 8 MONTH) AND CURDATE() <= DATE_ADD(latest_posting, INTERVAL 9 MONTH), 'Expiring',
											IF(CURDATE() > DATE_ADD(latest_posting, INTERVAL 9 MONTH), 'Expired', '')))) AS public_remarks,
								is_vacant
							FROM vacancies
							WHERE  (
									item_desc LIKE '%$query%'
									OR item_code LIKE '%$query%'
									OR item_desc_detail LIKE '%$query%'
									OR depcode LIKE '%$query%'
									OR appointment_remarks LIKE '%$query%'
									OR public_status LIKE '%$query%'
								)
								AND item_status <> 'Abolished'
								AND active = 1
								$appointment_status_filter
								$publication_status_filter
								$show_all_items_filter
							ORDER BY depcode ASC, plantilla_item_no ASC
							$limitQuery";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$commandText = "SELECT COUNT(id) AS count
							FROM vacancies
							WHERE  (
									item_desc LIKE '%$query%'
									OR item_code LIKE '%$query%'
									OR item_desc_detail LIKE '%$query%'
									OR depcode LIKE '%$query%'
									OR appointment_remarks LIKE '%$query%'
									OR public_status LIKE '%$query%'
								)
								AND item_status <> 'Abolished'
								AND active = 1
								$appointment_status_filter
								$publication_status_filter
								$show_all_items_filter";
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
				$public_remarks_style = "";
				if($val->public_remarks == "Published") $public_remarks_style = "<font color=green><b>Published</b></font>";
				else if($val->public_remarks == "Unpublished") $public_remarks_style = "Unpublished";
				else if($val->public_remarks == "Expiring") $public_remarks_style = "<font color=red>Expiring</font>";
				else if($val->public_remarks == "Expired") $public_remarks_style = "<font color=red><b>Expired</b></font>";

				$data['data'][] = array(
					'id' 						=> $val->id,
					'plantilla_item_no' 		=> $val->plantilla_item_no,
					'item_code' 				=> $val->item_code,
					'item_desc' 				=> $val->item_desc,
					'item_desc_detail' 			=> $val->item_desc_detail,
					'posgrade' 					=> $val->posgrade,
					'depcode' 					=> $val->depcode,
					'occupant_desc'				=> $val->occupant_desc,
					'appointment_remarks' 		=> $val->appointment_remarks,
					'public_status' 			=> $val->public_status,
					'public_remarks' 			=> $val->public_remarks,
					'public_remarks_style' 		=> $public_remarks_style,
					'latest_posting' 			=> $val->latest_posting,
					'is_vacant' 				=> ($val->is_vacant == 0) ? false: true
				);
			}

			$data['totalCount'] = $query_count[0]->count;
			$data['appointment_status_desc'] = $appointment_status_desc;
			$data['publication_status_desc'] = $publication_status_desc;
			$data['show_all_items_desc'] = $show_all_items_desc;
			return $data;
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();
		}
	}

	public function view() {
		try {
			die(json_encode($this->generateview($this->input->post('id'))));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();
		}
	}

	public function generateview($id) {
		try {
			$commandText = "SELECT
								id,
								item_code,
								item_desc,
								item_desc_detail,
								posgrade,
								depcode,
								remarks
							FROM vacancies
							WHERE id = $id";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			foreach($query_result as $key => $val) {
				$data['data'][] = array(
					'id' 				=> $val->id,
					'item_code'			=> $val->item_code,
					'item_desc'			=> $val->item_desc,
					'item_desc_detail'	=> $val->item_desc_detail,
					'posgrade'			=> $val->posgrade,
					'depcode'			=> $val->depcode,
					'remarks'			=> $val->remarks
				);
			}

			$data["success"] = true;			
			$data["totalCount"] = count($query_result);
			return $data;
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();
		}
	}

	public function crud() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();

			$id						= $this->input->post('id');
			$record_type_id			= $this->input->post('record_type_id');
			$from_id				= $this->input->post('from_id');
			$to_id					= $this->input->post('to_id');
			$sequence_number		= $this->input->post('sequence_number');
			$communication_number	= mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($this->input->post('communication_number'))));
			$date_communication 	= date('Y-m-d',strtotime($this->input->post('date_communication')));
			$subject				= mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($this->input->post('subject'))));
			$from_name				= mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($this->input->post('from_name'))));
			$to_name				= mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($this->input->post('to_name'))));
			$type 					= $this->input->post('type');

			$this->load->model('Access'); $this->Access->rights($this->modulename('link'), $type, null);

			if($type == "Delete") {
				$commandText = "UPDATE adminservices_records_header SET active = 0 WHERE id = $id";
				$result = $this->db->query($commandText);

				$this->load->model('Logs'); $this->Logs->audit_logs($id, 'adminservices_records_header', $type, $this->modulename('label'));
			}
			else {
				if($type == "Add") {
					$commandText = "SELECT * FROM adminservices_records_header WHERE active = 1 AND ((sequence_number LIKE '%$sequence_number%' AND YEAR(date_communication) = YEAR(CURDATE())) OR subject LIKE '%$subject%') AND communication_type = 'Incoming'";
					//$commandText = "SELECT * FROM adminservices_records_header WHERE active = 1 AND (sequence_number LIKE '%$sequence_number%' OR subject LIKE '%$subject%') AND communication_type = 'Incoming'";
					$result = $this->db->query($commandText);
					$query_result = $result->result();

					$this->load->model('adminservices_records_header');
					$this->adminservices_records_header->date_logged		= date('Y-m-d H:i:s');
					$this->adminservices_records_header->status 			= 'Pending Division Assigning';
					$id = 0;
				}
				else if($type == "Edit") {
					$division_id 		= $this->input->post('division_id');
					$action_taken_id 	= $this->input->post('action_taken_id');
					$side_notes 		= mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($this->input->post('side_notes'))));
					$status 			= mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($this->input->post('status'))));

					$commandText = "SELECT * FROM adminservices_records_header WHERE id <> $id AND active = 1 AND ((sequence_number LIKE '%$sequence_number%' AND YEAR(date_communication) = YEAR(CURDATE())) OR subject LIKE '%$subject%')  AND communication_type = 'Incoming'";
					$result = $this->db->query($commandText);
					$query_result = $result->result();

					$commandText = "SELECT date_logged FROM adminservices_records_header WHERE id = $id";
					$result = $this->db->query($commandText);
					$query_result2 = $result->result();

					$this->load->model('adminservices_records_header');
					$this->adminservices_records_header->id 				= $id;
					$this->adminservices_records_header->division_id		= $division_id;
					$this->adminservices_records_header->action_taken_id	= $action_taken_id;
					$this->adminservices_records_header->date_logged		= $query_result2[0]->date_logged;
					$this->adminservices_records_header->side_notes			= $side_notes;
					$this->adminservices_records_header->status 			= $status;
				}

				if(count($query_result) > 0) {
					$data = array("success"=> false, "data"=>"Incoming record already exists! Check sequence # and subject.");
					die(json_encode($data));
				}

				$from_id = $this->SaveRetrieveRecordAddressID($from_id, $from_name);
				$to_id = $this->SaveRetrieveRecordAddressID($to_id, $to_name);

				$this->adminservices_records_header->sequence_number 		= $sequence_number;
				$this->adminservices_records_header->communication_number 	= $communication_number;
				$this->adminservices_records_header->record_type_id 		= $record_type_id;
				$this->adminservices_records_header->subject 				= $subject;
				$this->adminservices_records_header->from_id 				= $from_id;
				$this->adminservices_records_header->to_id 					= $to_id;
				$this->adminservices_records_header->date_communication		= $date_communication;
				$this->adminservices_records_header->communication_type		= 'Incoming';
				$this->adminservices_records_header->active 				= 1;
				$this->adminservices_records_header->save($id);

				#if type is add, prepare the actions taken table for the certain record id
				if($type == "Add") {
					$record_id = $this->adminservices_records_header->id;
					$this->load->model('adminservices_records_actions_taken');
					$this->adminservices_records_actions_taken->record_id 	= $record_id;
					$this->adminservices_records_actions_taken->save(0);

					$action_taken_id = $this->adminservices_records_actions_taken->id;

					$commandText = "UPDATE adminservices_records_header SET action_taken_id = $action_taken_id WHERE id = $record_id";
					$result = $this->db->query($commandText);
				}

				$this->load->model('Logs'); $this->Logs->audit_logs($id, 'adminservices_records_header', $type, $this->modulename('label'));
			}
			
			$arr = array();  
			$arr['success'] = true;
			if($type == "Add")
				$arr['data'] = "Successfully Created";
			if($type == "Edit")
				$arr['data'] = "Successfully Updated";
			if($type == "Delete")
				$arr['data'] = "Successfully Deleted";
			die(json_encode($arr));
		}
		catch(Exception $e) {
			$data = array("success"=> false, "data"=>$e->getMessage());
			die(json_encode($data));
		}
	}

	public function headerview() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();
			$this->load->library('callable_functions');
			
			$id = $this->input->post('id');

			$commandText = "SELECT 
								a.*,
								b.description AS record_type,
								c.description AS from_name,
								d.description AS to_name
							FROM adminservices_records_header a
								LEFT JOIN record_types b ON a.record_type_id = b.id
								LEFT JOIN adminservices_records_from_to c ON a.from_id = c.id
								LEFT JOIN adminservices_records_from_to d ON a.to_id = d.id
							WHERE a.id = $id AND a.active = 1";

			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$data = array();
			$record = array();

			// $details = $this->callable_functions->CommunicationDetailsBuilder($value->record_type, $value->communication_number, $value->subject);

			foreach($query_result as $key => $value) {	
				// $control_number = $this->callable_functions->GenerateControlNumber($value->date_communication, $value->date_logged, $value->sequence_number);

				$record['id'] 					= $value->id;
				$record['record_type_id']		= $value->record_type_id;
				$record['from_id']				= $value->from_id;
				$record['to_id']				= $value->to_id;
				$record['division_id']			= $value->division_id;
				$record['action_taken_id']		= $value->action_taken_id;
				$record['sequence_number']		= $value->sequence_number;
				$record['communication_number']	= $value->communication_number;
				// $record['control_number']		= $value->control_number;
				$record['date_communication']	= date('m/d/Y', strtotime($value->date_communication));
				$record['date_logged']			= date('m/d/Y - h:i A', strtotime($value->date_logged));
				// $record['details']				= stripslashes($value->details);
				$record['subject']				= stripslashes($value->subject);
				$record['side_notes']			= stripslashes($value->side_notes);
				$record['status']				= $value->status;
				$record['record_type']			= $value->record_type;
				$record['from_name']			= $value->from_name;
				$record['to_name']				= $value->to_name;
			}

			$data['data'] = $record;
			$data['success'] = true;
			die(json_encode($data));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();	
		}
	}

	public function update_vacancy_status() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();

			$id						= $this->input->post('id');
			$is_vacant_inv 			= $this->input->post('is_vacant') == "true" ? 0: 1;

			$commandText = "UPDATE vacancies SET is_vacant = $is_vacant_inv WHERE id = $id";
			$result = $this->db->query($commandText);

			$this->load->model('Logs'); $this->Logs->audit_logs($id, 'vacancies', 'Update Vacancy Status', $this->modulename('label'));
			
			$arr = array();  
			$arr['success'] = true;
			$arr['data'] = "Successfully Updated";
			die(json_encode($arr));
		}
		catch(Exception $e) {
			$data = array("success"=> false, "data"=>$e->getMessage());
			die(json_encode($data));
		}
	}

	public function update_from_plantilla() {
		$host = getenv('FIREBIRD_CONNECTION_STRING');
		$username = getenv('FIREBIRD_USERNAME');
		$password = getenv('FIREBIRD_PASSWORD');

		try {
			$type = $this->input->post('type');
			$this->benchmark->mark('code_start'); $benchmark_string = ''; // benchmarking purposes

			$arr = array();  
			$arr['success'] = false;
			$arr['data'] = "Vacancies are up to date with Plantilla.";

			// Connect to Plantilla database
			$dbh = new \PDO($host, $username, $password,
			[\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

			if($type == "Publications") {
				// query publications table and update vacancies' latest status and posting
				$sql = "SELECT a.PUBLICATION_NO, a.ITEM_ID, a.STATUS, a.DATE_CSC_POSTED
						FROM PUBLISH_DETAILS a
						INNER JOIN (
							SELECT ITEM_ID, MAX(PUBLICATION_NO) AS LATEST_PUB_NO, MAX(DATE_CSC_POSTED) AS LATEST_POSTING
							FROM PUBLISH_DETAILS
							GROUP BY ITEM_ID
						) b ON b.ITEM_ID = a.ITEM_ID AND b.LATEST_PUB_NO = a.PUBLICATION_NO AND b.LATEST_POSTING = a.DATE_CSC_POSTED";
				$query = $dbh->query($sql);

				$publications = array();
				// Get the result row by row as object
				while ($row = $query->fetch(\PDO::FETCH_OBJ)) {
					$publications[] = array(
						'item_id' 			=> $row->ITEM_ID,
						'public_status' 	=> $row->STATUS,
						'latest_posting' 	=> $row->DATE_CSC_POSTED
					);
				}
				$query->closeCursor();

				foreach($publications as $key => $value) {
					$item_id 			= $value['item_id'];
					$public_status 		= $value['public_status'];
					$latest_posting 	= $value['latest_posting'];

					$commandText = "SELECT id, 
										IF(effectivity IS NULL, NULL, effectivity) AS effectivity,
										-- IF(effectivity IS NULL, NULL, DATE_FORMAT(effectivity, '%m/%d/%Y')) AS effectivity,
										occupant_desc,
										public_status, 
										IF(latest_posting IS NULL, NULL, latest_posting) AS latest_posting
									FROM vacancies 
									WHERE plantilla_item_id = $item_id";
					$result = $this->db->query($commandText);
					$query_result = $result->result();
					if(count($query_result) > 0) {
						// for testing purposes
						// echo "Vacancy effectivity: " . date('m/d/Y', strtotime($query_result[0]->effectivity));
						// echo "Latest posting date: " . date('m/d/Y', strtotime($latest_posting));
						// echo "Vacancy effectivity: " . strtotime($query_result[0]->effectivity);
						// echo "Latest posting date: " . strtotime($latest_posting);

						if($query_result[0]->effectivity == NULL || strtotime($query_result[0]->effectivity . ' - 30 days') < strtotime($latest_posting)) { // proceed only if publication date is greater than vacancy effectivity date less 30 days
							$changes = array();

							// if there are changes in the publication status
							if($query_result[0]->public_status != $public_status) {
								$changes[] = array('public_status', $query_result[0]->public_status, $public_status);
							}

							// if there are changes in latest_posting
							if($query_result[0]->latest_posting != $latest_posting) {
								$changes[] = array('latest_posting', $query_result[0]->latest_posting, $latest_posting);
							}

							if(count($changes) > 0) {
								$id = $query_result[0]->id;
								$commandText2 = "UPDATE vacancies SET latest_posting = '$latest_posting', public_status = '$public_status' WHERE id = $id";	
								$result2 = $this->db->query($commandText2);

								// log update to history table
								$this->load->model('plantilla_updates_history_model');
								$this->plantilla_updates_history_model->table_id  = $id;
								$this->plantilla_updates_history_model->table     = 'vacancies';
								$this->plantilla_updates_history_model->changes   = json_encode($changes);
								$this->plantilla_updates_history_model->date_updated = date('Y-m-d H:i:s');
								$this->plantilla_updates_history_model->save(0);
							}
						}
					}
				}

				$this->benchmark->mark('code_end'); // benchmarking purposes
				$benchmark_string .= 'Publications Query Time: ' . $this->benchmark->elapsed_time('code_start', 'code_end');

				$arr['success'] = true;
				$arr['data'] = "Publications are up to date with Plantilla. " . $benchmark_string;
			}
			else if($type == "Appointments") {
				// query appointments table for all appointments from January 1, 2022 onwards
				$sql = "SELECT a.APPMNT_NO, a.ITEM_ID, a.EMP_NO, a.FR_DATE, a.NAME, a.APPMNT_TYPE, a.APPMNT_STATUS, a.VICE_EMP_NO, a.VICE_NAME, a.CSC_ACTION_DATE
						FROM PLANTILLA_APPMNTS AS a
							INNER JOIN (
								SELECT ITEM_ID, MAX(APPMNT_NO) AS LATEST_APPMNT_NO, MAX(FR_DATE) AS LATEST_START_DATE
								FROM PLANTILLA_APPMNTS
								GROUP BY ITEM_ID
							) AS b ON b.ITEM_ID = a.ITEM_ID AND b.LATEST_APPMNT_NO = a.APPMNT_NO AND b.LATEST_START_DATE = a.FR_DATE
						WHERE FR_DATE >= '2022-01-01 00:00:00'";
				$query = $dbh->query($sql);

				$appointments = array();
				// Get the result row by row as object
				while($row = $query->fetch(\PDO::FETCH_OBJ)) {
					$prev_item_id = NULL;
					// query the latest item of the employee (before the promotion) if appmnt status is promotion
					if($row->APPMNT_TYPE == "Promotion") {
						$sql2 = "SELECT a.ITEM_ID, a.FR_DATE, a.TO_DATE, a.POSDESC
								FROM SR_OLD AS a
									INNER JOIN(
										SELECT EMP_NO, MAX(FR_DATE) AS LATEST_START_DATE
										FROM SR_OLD
										WHERE ITEM_ID <> $row->ITEM_ID 	
										-- condition ITEM_ID <> $row->ITEM_ID is needed so that it will select the last item BEFORE promotion
										GROUP BY EMP_NO
									) AS b ON b.EMP_NO = a.EMP_NO AND b.LATEST_START_DATE = a.FR_DATE
								WHERE a.EMP_NO = $row->EMP_NO";
						$query2 = $dbh->query($sql2);

						while($row2 = $query2->fetch(\PDO::FETCH_OBJ)) {
							$prev_item_id = $row2->ITEM_ID;
						}
					}

					$appointments[] = array(
						'appmnt_no' 			=> $row->APPMNT_NO,
						'item_id' 				=> $row->ITEM_ID,
						'emp_no' 				=> $row->EMP_NO,
						'fr_date' 				=> substr($row->FR_DATE, 0, 10),
						'name' 					=> utf8_encode($row->NAME),
						'appointment_type' 		=> $row->APPMNT_TYPE, // new
						'appointment_status' 	=> $row->APPMNT_STATUS,
						'vice_emp_no' 			=> $row->VICE_EMP_NO, // new
						'vice_name' 			=> utf8_encode($row->VICE_NAME), // new
						'csc_action_date' 		=> substr($row->CSC_ACTION_DATE, 0, 10), // new,
						'prev_item_id' 			=> $prev_item_id
					);
				}
				$query->closeCursor();

				foreach($appointments as $key => $value) {
					$appmnt_no			= $value['appmnt_no'];
					$item_id 			= $value['item_id'];
					$emp_no 			= $value['emp_no'];
					$fr_date 			= $value['fr_date'];
					$name 				= $value['name'];
					$appointment_type 	= $value['appointment_type'];
					$appointment_status = $value['appointment_status'];
					$vice_emp_no  		= $value['vice_emp_no'];
					$vice_name  		= $value['vice_name'];
					$csc_action_date  	= $value['csc_action_date'];
					$prev_item_id 		= is_null($value['prev_item_id']) ? NULL: $value['prev_item_id'];

					// query the id first
					$commandText = "SELECT id, appointment_status, appointment_remarks, is_vacant FROM vacancies WHERE plantilla_item_id = $item_id";
					$result = $this->db->query($commandText);
					$query_result = $result->result();

					$appointment_remarks = $appointment_status . " appointment of " . $name . " effective " . $fr_date. " with Appmnt. No: " . $appmnt_no . ".";
					if(count($query_result) > 0) {
						if($query_result[0]->appointment_status != $appointment_status) { // only update if the appointment status has changed
							$id = $query_result[0]->id;

							if($appointment_status == "Pending") $is_vacant = 1;
							else if($appointment_status == "Approved") $is_vacant = 0;

							$commandText2 = "UPDATE vacancies SET appointment_status = '$appointment_status', appointment_remarks = '$appointment_remarks', is_vacant = $is_vacant WHERE id = $id";
							$result2 = $this->db->query($commandText2);

							// new fields that are needed to be synced, changes not yet logged
							$commandText3 = "UPDATE vacancies SET appointment_type = '$appointment_type', appointee = '$name', vice_emp_no = '$vice_emp_no', vice_name = '$vice_name', csc_action_date = '$csc_action_date', appointment_date_from = '$fr_date', prev_item_id = '$prev_item_id' WHERE id = $id";
							$result3 = $this->db->query($commandText3);

							$changes = array(
								array('appointment_status', $query_result[0]->appointment_status, $appointment_status), 
								array('appointment_remarks', $query_result[0]->appointment_remarks, $appointment_remarks),
								array('is_vacant', $query_result[0]->is_vacant, $is_vacant)
							);

							// log updates to history table
							$this->load->model('plantilla_updates_history_model');
							$this->plantilla_updates_history_model->table_id  = $id;
							$this->plantilla_updates_history_model->table     = 'vacancies';
							$this->plantilla_updates_history_model->changes   = json_encode($changes);
							$this->plantilla_updates_history_model->date_updated = date('Y-m-d H:i:s');
							$this->plantilla_updates_history_model->save(0);
						}
					}
				}

				$this->benchmark->mark('code_end'); // benchmarking purposes
				$benchmark_string .= 'Plantilla Appointments Query Time: ' . $this->benchmark->elapsed_time('code_start', 'code_end');

				$arr['success'] = true;
				$arr['data'] = "Appointments are up to date with Plantilla. " . $benchmark_string;
			}
			else if($type == "Details") {
				// $sql = "SELECT * FROM PLANTILLA WHERE ITEM_STATUS <> 'Abolished' AND PARENT_ID > 0"; // replace query
				$sql = "SELECT * FROM PLANTILLA WHERE PARENT_ID > 0";
				$query = $dbh->query($sql); // Execute query

				$details = array();
				// Get the result row by row as object
				while ($row = $query->fetch(\PDO::FETCH_OBJ)) {
					$details[] = array(
						'item_id' 			=> $row->ITEM_ID,
						'item_no' 			=> $row->ITEM_NO,
						'item_code' 		=> $row->ITEM_CODE,
						'item_desc' 		=> $row->ITEM_DESC,
						'item_desc_detail' 	=> $row->ITEM_DESC_DETAIL,
						'item_status' 		=> $row->ITEM_STATUS,
						'posgrade' 			=> $row->POSGRADE,
						'poslevel' 			=> $row->POSLEVEL,
						'sal_step' 			=> $row->SAL_STEP,
						'sal_rate' 			=> $row->SAL_RATE,
						'depcode' 			=> $row->DEPCODE,
						'occupant' 			=> $row->OCCUPANT,
						'occupant_desc' 	=> utf8_encode($row->OCCUPANT_DESC),
						'effectivity' 		=> $row->EFFECTIVITY
					);
				}
				$query->closeCursor();

				foreach($details as $key => $value) {
					$item_id 			= $value['item_id'];
					$item_no 			= $value['item_no'];
					$item_code 			= $value['item_code'];
					$item_desc 			= $value['item_desc'];
					$item_desc_detail 	= $value['item_desc_detail'];
					$item_status 		= $value['item_status'];
					$posgrade 			= $value['posgrade'];
					$poslevel 			= $value['poslevel'];
					$sal_step 			= $value['sal_step'];
					$sal_rate 			= doubleval($value['sal_rate']);
					$depcode 			= $value['depcode'];
					$occupant 			= $value['occupant'];
					$occupant_desc 		= $value['occupant_desc'];
					$effectivity 		= $value['effectivity'];
					$active 			= 1;

					$is_existing = $this->check_if_existing($item_id); // $is_existing value is id if true, false if not
					// $benchmark_string .= '\nIs existing: ' . $is_existing;
					if($is_existing != false OR $is_existing != 0) {
						$needs_updating = $this->check_ifneeds_updating($item_id, $item_no, $item_code, $item_desc, $item_desc_detail, $item_status, $posgrade, $poslevel, $sal_step, $sal_rate, $depcode, $occupant, $occupant_desc, $effectivity); // $result value is array of data changes if needs updating, else false
						if($needs_updating != false OR $needs_updating != 0) {
							// if item status changes to "Abolished", update active to 0
							if($this->in_2d_array("item_status", $needs_updating) && $item_status == "Abolished") {
								$active = 0;
							}

							// if occupant changes from an emp. id to 0 meaning the item is now vacant, reset the below fields to null and set is_vacant = 1
							if($this->in_2d_array("occupant", $needs_updating) && $occupant == 0) {
								$appointment_type = $appointee = $vice_emp_no = $vice_name = $csc_action_date = $appointment_date_from = $appointment_status = $appointment_remarks = $prev_item_id = $latest_posting = $public_status = $is_vacant = NULL;
								$is_vacant = 1;
							}
							// else if occupant value is a valid employee id or 0 (but no changes in occupant), retrieve values from current record
							else {
								$commandText = "SELECT * FROM vacancies WHERE plantilla_item_id = $item_id";
								$result = $this->db->query($commandText);
								$query_result = $result->result();

								$appointment_type 		= $query_result[0]->appointment_type;
								$appointee 				= $query_result[0]->appointee;
								$vice_emp_no 			= $query_result[0]->vice_emp_no;
								$vice_name 				= $query_result[0]->vice_name;
								$csc_action_date 		= $query_result[0]->csc_action_date;
								$appointment_date_from 	= $query_result[0]->appointment_date_from;
								$appointment_status 	= $query_result[0]->appointment_status;
								$appointment_remarks 	= $query_result[0]->appointment_remarks;
								$prev_item_id 			= $query_result[0]->prev_item_id;
								$latest_posting 		= $query_result[0]->latest_posting;
								$public_status 			= $query_result[0]->public_status;
								$is_vacant 				= $query_result[0]->is_vacant;
							}

							// update record of db 
							$this->load->model('vacancies');
							$this->vacancies->id 					= $is_existing; 			// updated via Sync Details
							$this->vacancies->plantilla_item_id 	= $item_id; 				// updated via Sync Details
							$this->vacancies->plantilla_item_no 	= $item_no; 				// updated via Sync Details
							$this->vacancies->item_code 			= $item_code; 				// updated via Sync Details
							$this->vacancies->item_desc 			= $item_desc; 				// updated via Sync Details
							$this->vacancies->item_desc_detail 		= $item_desc_detail; 		// updated via Sync Details
							$this->vacancies->item_status 			= $item_status; 			// updated via Sync Details
							$this->vacancies->posgrade 				= $posgrade; 				// updated via Sync Details
							$this->vacancies->poslevel 				= $poslevel; 				// updated via Sync Details
							$this->vacancies->sal_step 				= $sal_step; 				// updated via Sync Details
							$this->vacancies->sal_rate 				= $sal_rate; 				// updated via Sync Details
							$this->vacancies->depcode 				= $depcode; 				// updated via Sync Details
							$this->vacancies->occupant 				= $occupant; 				// updated via Sync Details
							$this->vacancies->occupant_desc 		= $occupant_desc; 			// updated via Sync Details
							$this->vacancies->effectivity 			= $effectivity; 			// updated via Sync Details
							$this->vacancies->appointment_type 		= $appointment_type; 		// updated via Sync Appointments
							$this->vacancies->appointee 			= $appointee; 				// updated via Sync Appointments
							$this->vacancies->vice_emp_no 			= $vice_emp_no; 			// updated via Sync Appointments
							$this->vacancies->vice_name 			= $vice_name; 				// updated via Sync Appointments
							$this->vacancies->csc_action_date 		= $csc_action_date; 		// updated via Sync Appointments
							$this->vacancies->appointment_date_from = $appointment_date_from; 	// updated via Sync Appointments
							$this->vacancies->appointment_status 	= $appointment_status; 		// updated via Sync Appointments
							$this->vacancies->appointment_remarks 	= $appointment_remarks; 	// updated via Sync Appointments
							$this->vacancies->prev_item_id 			= $prev_item_id; 		 	// updated via Sync Appointments	
							$this->vacancies->latest_posting 		= $latest_posting; 			// updated via Sync Publication
							$this->vacancies->public_status 		= $public_status; 			// updated via Sync Publication
							$this->vacancies->is_vacant 			= $is_vacant; 				// updated via Sync Appointments / Set Vacancy function
							$this->vacancies->active 				= $active;
							$this->vacancies->save($is_existing);

							// log the update
							$this->load->model('plantilla_updates_history_model');
							$this->plantilla_updates_history_model->table_id 	= $is_existing;
							$this->plantilla_updates_history_model->table 		= 'vacancies';
							$this->plantilla_updates_history_model->changes 	= json_encode($needs_updating);
							$this->plantilla_updates_history_model->date_updated = date('Y-m-d H:i:s');
							$this->plantilla_updates_history_model->save(0);

							$arr['data'] = "Vacancies successfully updated";
						}
					}
					else {
						// insert new data to db
						$this->load->model('vacancies');
						$this->vacancies->plantilla_item_id 	= $item_id;
						$this->vacancies->plantilla_item_no 	= $item_no;
						$this->vacancies->item_code 			= $item_code;
						$this->vacancies->item_desc 			= $item_desc;
						$this->vacancies->item_desc_detail 		= $item_desc_detail;
						$this->vacancies->item_status 			= $item_status;
						$this->vacancies->posgrade 				= $posgrade;
						$this->vacancies->poslevel 				= $poslevel;
						$this->vacancies->sal_step 				= $sal_step;
						$this->vacancies->sal_rate 				= $sal_rate;
						$this->vacancies->depcode 				= $depcode;
						$this->vacancies->occupant 				= $occupant;
						$this->vacancies->occupant_desc 		= $occupant_desc;
						$this->vacancies->effectivity 			= $effectivity;
						$this->vacancies->appointment_type 		= NULL; // other fields that will be updated via other functions must be set to NULL to prevent previous loop values from being saved
						$this->vacancies->appointee 			= NULL;
						$this->vacancies->vice_emp_no 			= NULL;
						$this->vacancies->vice_name 			= NULL;
						$this->vacancies->csc_action_date 		= NULL;
						$this->vacancies->appointment_date_from = NULL;
						$this->vacancies->appointment_status 	= NULL;
						$this->vacancies->appointment_remarks 	= NULL;
						$this->vacancies->prev_item_id 			= NULL;
						$this->vacancies->latest_posting 		= NULL;
						$this->vacancies->public_status 		= NULL;
						$this->vacancies->is_vacant 			= 1; // new items are vacant by default
						$this->vacancies->active 				= 1;
						$this->vacancies->save(0);

						$arr['data'] = "Vacancies successfully updated";
					}
				}

				$this->benchmark->mark('code_end'); // benchmarking purposes
				$benchmark_string .= 'Plantilla Query Time: ' . $this->benchmark->elapsed_time('code_start', 'code_end');
				$arr['success'] = true;
				$arr['data'] = "Basic details are up to date with Plantilla. " . $benchmark_string;
			}
			else if($type == "QS") {
				$sql = "SELECT * FROM PLANTILLA_QS";
				$query = $dbh->query($sql); // Execute query

				$qualif_standards = array();
				// Get the result row by row as object
				while ($row = $query->fetch(\PDO::FETCH_OBJ)) {
					$qualif_standards[] = array(
						'item_id' 		=> $row->ITEM_ID,
						'education' 	=> $row->EDUCATION,
						'experience' 	=> $row->EXPERIENCE,
						'training' 		=> $row->TRAINING,
						'eligibility' 	=> $row->ELIGIBILITY,
						'competency' 	=> $row->COMPETENCY
					);
				}

				foreach($qualif_standards as $key => $value) {
					$item_id 		= $value['item_id'];
					$education 		= $value['education'];
					$experience 	= $value['experience'];
					$training 		= $value['training'];
					$eligibility 	= $value['eligibility'];
					$competency 	= $value['competency'];

					$commandText = "SELECT *
								FROM vacancies_qs
								WHERE plantilla_item_id = $item_id";
					$result = $this->db->query($commandText);
					$query_result = $result->result();

					if(count($query_result) > 0) {
						$id = $query_result[0]->id;
						// if there is at least one difference/change, update all
						if(!(($query_result[0]->education == $education) && ($query_result[0]->experience == $experience) && ($query_result[0]->training == $training) && ($query_result[0]->eligibility == $eligibility) && ($query_result[0]->competency == $competency))) {

							$this->load->model('vacancies_qs');
							$this->vacancies_qs->id 				= $id;
							$this->vacancies_qs->plantilla_item_id 	= $item_id;
							$this->vacancies_qs->education 			= $education;
							$this->vacancies_qs->experience 		= $experience;
							$this->vacancies_qs->training 			= $training;
							$this->vacancies_qs->eligibility 		= $eligibility;
							$this->vacancies_qs->competency 		= $competency;
							$this->vacancies_qs->save($id);
						}
					}
					else {
						// insert if does not exist yet
						$this->load->model('vacancies_qs');
						$this->vacancies_qs->plantilla_item_id 	= $item_id;
						$this->vacancies_qs->education 			= $education;
						$this->vacancies_qs->experience 		= $experience;
						$this->vacancies_qs->training 			= $training;
						$this->vacancies_qs->eligibility 		= $eligibility;
						$this->vacancies_qs->competency 		= $competency;
						$this->vacancies_qs->save(0);
					}
				}

				// no more logging of changes
				$query->closeCursor();
				$this->benchmark->mark('code_end'); // benchmarking purposes
				$benchmark_string .= 'QS Query Time: ' . $this->benchmark->elapsed_time('code_start', 'code_end');

				$arr['success'] = true;
				$arr['data'] = "QS are up to date with Plantilla. " . $benchmark_string;
			}

			die(json_encode($arr));
		}
		catch (\PDOException $e) {
			die(json_encode($e->getMessage()));
		}
	}

	function check_if_existing($item_id) {
		$commandText = "SELECT * FROM vacancies WHERE plantilla_item_id = $item_id";
		$result = $this->db->query($commandText);
		$query_result = $result->result();

		if(count($query_result) == 0) {return false;}
		else return $query_result[0]->id;
	}

	function check_ifneeds_updating($item_id, $item_no, $item_code, $item_desc, $item_desc_detail, $item_status, $posgrade, $poslevel, $sal_step, $sal_rate, $depcode, $occupant, $occupant_desc, $effectivity) {
		$commandText = "SELECT * FROM vacancies WHERE plantilla_item_id = $item_id";
		$result = $this->db->query($commandText);
		$query_result = $result->result();

		$row = $query_result[0];
		$data = array();
		if($row->plantilla_item_id != $item_id) 			$data[] = array('item_id', $row->item_id, $item_id); // array(column, from, to)
		if($row->plantilla_item_no != $item_no) 			$data[] = array('item_no', $row->item_no, $item_no);
		if($row->item_code != $item_code) 					$data[] = array('item_code', $row->item_code, $item_code);
		if($row->item_desc != $item_desc)  					$data[] = array('item_desc', $row->item_desc, $item_desc);
		if($row->item_desc_detail != $item_desc_detail) 	$data[] = array('item_desc_detail', $row->item_desc_detail, $item_desc_detail);
		if($row->item_status != $item_status) 				$data[] = array('item_status', $row->item_status, $item_status);
		if($row->posgrade != $posgrade) 					$data[] = array('posgrade', $row->posgrade, $posgrade);
		if($row->poslevel != $poslevel) 					$data[] = array('poslevel', $row->poslevel, $poslevel);
		if($row->sal_step != $sal_step) 					$data[] = array('sal_step', $row->sal_step, $sal_step);
		if($row->sal_rate != $sal_rate) 					$data[] = array('sal_rate', $row->sal_rate, $sal_rate);
		if($row->depcode != $depcode) 						$data[] = array('depcode', $row->depcode, $depcode);
		if($row->occupant != $occupant) 					$data[] = array('occupant', $row->occupant, $occupant);
		if($row->occupant_desc != $occupant_desc) 			$data[] = array('occupant_desc', $row->occupant_desc, $occupant_desc);
		if($row->effectivity != $effectivity) 				$data[] = array('effectivity', $row->effectivity, $effectivity);
		
		if(count($data) == 0) {
			return 0;
		}
		else {
			return $data; // return data array if one or some data are not matching
		}
	}

	public function exportdocument() {
		$query = $this->esc_str($this->input->post('query'));
		$appointment_status = $this->input->post('appointment_status');
		$publication_status = $this->input->post('publication_status');
		$show_all_items = $this->input->post('show_all_items');
		$type = $this->input->post('filetype');

		$response = array();
		$response['success'] = true;
		if($type == 'Excel')
			$response['filename'] = $this->export_excelvacancies_list($this->generatevacancies_list($query, $appointment_status, $publication_status, $show_all_items, 'Report'));
		$this->load->model('Logs'); $this->Logs->audit_logs(0, 'vacancies', 'Report-'.$type, 'Vacancies List');        	
		die(json_encode($response));
	}


	public function export_excelvacancies_list($data) {
		try {
			$this->load->library('PHPExcel');

			$path 			= getenv('DOCUMENTS_DIR');
			$type  		 	= 'Excel5';
			$name 			= "Template - Vacancies List.xls";
			// $objReader 		= PHPExcel_IOFactory::createReader($type);
			$objPHPExcel  	= PHPExcel_IOFactory::load($path.$name);
			// $objPHPExcel 	= $objReader->load($path.$name);
			// $objPHPExcel 	= $objPHPExcel->setActiveSheetIndex(0);

			// $objPHPExcel = new PHPExcel();
			$objPHPExcel->getActiveSheet()->setShowGridlines(true);
			$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('Arial');
			$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
			// $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(3);

			$fDate = date("Ymd_His");
			if($data['appointment_status_desc'] != "" || $data['publication_status_desc'] != "" || $data['show_all_items_desc'] != "No") 
				$filename = "Vacancies List (with filters) ";
			else
				$filename = "Vacancies List ";

			$filename_with_ext = $filename . $fDate . ".xls";

			$objPHPExcel->getProperties()->setCreator(getenv('REPORT_CREATOR'))
						->setLastModifiedBy(getenv('REPORT_AUTHOR'))
						->setTitle("Vacancies List")
						->setSubject("Report")
						->setDescription("Generating Vacancies List")
						->setKeywords(getenv('REPORT_KEYWORDS'))
						->setCategory("Reports");

			#Dimensions
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(60);
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);	

			#Font & Alignment
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('A7:H7')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('A7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('F7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('F8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			#Duplicate Cell Styles
			$objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('A8'), 'A9:A'.($data['totalCount']+7));
			$objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('B8'), 'B9:B'.($data['totalCount']+7));
			$objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('F8'), 'F9:F'.($data['totalCount']+7));

			### Title
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1", $filename);
			
			###DATE
			$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue("A3", "Appointment Status: " . $data['appointment_status_desc'])		
					      	->setCellValue("A4", "Publication Status: " . $data['publication_status_desc'])
					      	->setCellValue("A5", "Show Non-Vacant Items: " . $data['show_all_items_desc'])
					      	->setCellValue("A7", "No.")		
					      	->setCellValue("B7", "Item No.")
					      	->setCellValue("C7", "Item Name")
					      	->setCellValue("D7", "Code")
					      	->setCellValue("E7", "Parenthetical Pos.")
					      	->setCellValue("F7", "SG")
					      	->setCellValue("G7", "Dept.")
					      	->setCellValue("H7", "Appointment Remarks")
					      	->setCellValue("I7", "Latest Posting")
					      	->setCellValue("J7", "Publication Remarks")
					      	->setCellValue("K7", "Is Vacant");


			for($i=0; $i<$data['totalCount']; $i++) {
				$objPHPExcel->setActiveSheetIndex(0)
						   		->setCellValue("A".($i+8), $i+1)
						      	->setCellValue("B".($i+8), $data['data'][$i]['plantilla_item_no'])
						      	->setCellValue("C".($i+8), $data['data'][$i]['item_desc'])
						      	->setCellValue("D".($i+8), $data['data'][$i]['item_code'])
						      	->setCellValue("E".($i+8), $data['data'][$i]['item_desc_detail'])
						      	->setCellValue("F".($i+8), $data['data'][$i]['posgrade'])
						      	->setCellValue("G".($i+8), $data['data'][$i]['depcode'])
						      	->setCellValue("H".($i+8), $data['data'][$i]['appointment_remarks'])
						      	->setCellValue("I".($i+8), $data['data'][$i]['latest_posting'])
						      	->setCellValue("J".($i+8), $data['data'][$i]['public_remarks'])
						      	->setCellValue("K".($i+8), $data['data'][$i]['is_vacant']);
		   	}					      
	      	
			$this->load->library('session');
			// $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".($i+8), 'Printed by: '.$this->session->userdata('name'));
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".($i+9), 'Date Printed: '.date('m/d/Y h:i:sa'));
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".($i+10), 'Print Code: '.$filename_with_ext);
			
			$objPHPExcel->setActiveSheetIndex(0);				
			$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
			$path = "documents";
			$objWriter->save("$path/$filename_with_ext");
			return "$path/$filename_with_ext";
		}
		catch(Exception $e) {
			die(json_encode($e->getMessage()));
		}
	}

	// this function is used to identify if a certain item detail has its value changed or not
	private function in_2d_array($needle, $haystack) {
		foreach($haystack as $element) {
			if(in_array($needle, $element))
				return true;
		}
		return false;
	}

	private function esc_str($x) {
		return mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($x)));
	}
}