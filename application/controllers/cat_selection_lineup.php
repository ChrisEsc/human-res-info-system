<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cat_Selection_Lineup extends CI_Controller {
	/**
	*/ 
	private function modulename($type) {		
		if($type == 'link')
			return 'cat_selection_lineup';
		else 
			return 'Selection Lineup';
	} 

	public function index() {
		$this->load->model('Page');		
        $this->Page->set_page($this->modulename('link'));
	}

	public function selection_lineup_list() {
		try {
			$query = $this->esc_str($_GET['query']);
			$lineup_status = $_GET['lineup_status'];
			die(json_encode($this->generateselection_lineup_list($query, $lineup_status, 'Grid')));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();
		}
	}
 	
	public function generateselection_lineup_list($query, $lineup_status, $transaction_type) {
		try {
			$this->load->library('session');

			$limitQuery = "";
			if($transaction_type == 'Grid') {
				$limit = $_GET['limit'];
				$start = $_GET['start'];
				$limitQuery = " LIMIT $start, $limit";
			}

			// 0-pending (NULL), 1-completed, 2-all
			$lineup_status_filter = "1 = 1"; $status_desc = "All";
			if($lineup_status == 0) {$lineup_status_filter = " b.is_locked IS NULL"; $status_desc = "Pending";}
			else if($lineup_status == 1) {$lineup_status_filter = " b.is_locked = 1"; $status_desc = "Completed";}

			$commandText = "SELECT a.id AS lineup_header_id,
								b.id AS lineup_vacancy_id,
								c.id AS lineup_applicant_id,
								e.id AS applicant_id,
							    a.item_details,
							    d.item_code,
							    CONCAT(e.lname, ', ', e.fname, IF(e.mname IS NULL, '', CONCAT(' ', e.mname)), IF(e.suffix IS NULL, '', CONCAT(' ', e.suffix))) AS applicant_name,
							    e.phone_no,
							    e.email_add,
							    IF(a.date_opened IS NULL, '', DATE_FORMAT(a.date_opened, '%m/%d/%Y')) AS date_opened,
							    d.plantilla_item_no,
							    d.posgrade,
							    d.depcode,
							    IF(d.latest_posting IS NULL, 'Unpublished', DATE_FORMAT(d.latest_posting, '%m/%d/%Y')) AS latest_posting,
							    IF(c.date_lineup IS NULL, '', DATE_FORMAT(c.date_lineup, '%m/%d/%Y %H:%i:%s')) AS date_lineup,
							    IF(c.status_hr_test IS NULL, '', c.status_hr_test) AS status_hr_test,
							    IF(c.date_hr_test IS NULL, '', DATE_FORMAT(c.date_hr_test, '%m/%d/%Y')) AS date_hr_test,
							    IF(c.remarks_hr_test IS NULL, '', c.remarks_hr_test) AS remarks_hr_test,
							    IF(c.status_interview IS NULL, '', c.status_interview) AS status_interview,
							    IF(c.date_interview IS NULL, '', DATE_FORMAT(c.date_interview, '%m/%d/%Y')) AS date_interview,
							    IF(c.remarks_interview IS NULL, '', c.remarks_interview) AS remarks_interview,
							    c.is_done_bi,
							    c.is_done_paf,
							    c.is_done_nir,
							    c.remarks,
							    IF(c.status_psb IS NULL, '', c.status_psb) AS status_psb,
							    c.is_selected,
							    b.is_locked,
							    IF(b.date_psb IS NULL, '', DATE_FORMAT(b.date_psb, '%m/%d/%Y')) AS date_psb
							FROM selection_lineup_header a
								LEFT JOIN selection_lineup_vacancies b ON b.header_id = a.id
							    LEFT JOIN selection_lineup_applicants c ON c.lineup_vacancy_id = b.id
								LEFT JOIN vacancies d ON d.id = b.vacancy_id
							    LEFT JOIN applicants e ON e.id = c.applicant_id
							WHERE (
									a.item_details LIKE '%$query%'
									OR CONCAT(e.lname, ', ', e.fname, IF(e.mname IS NULL, '', CONCAT(' ', e.mname)), IF(e.suffix IS NULL, '', CONCAT(' ', e.suffix))) LIKE '%$query%'
									OR e.phone_no LIKE '%$query%'
									OR e.email_add LIKE '%$query%'
									OR d.depcode LIKE '%$query%'
								) AND $lineup_status_filter
							ORDER BY a.id DESC, d.plantilla_item_no ASC";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$commandText = "SELECT count(*) as count
							FROM selection_lineup_header a
								LEFT JOIN selection_lineup_vacancies b ON b.header_id = a.id
							    LEFT JOIN selection_lineup_applicants c ON c.lineup_vacancy_id = b.id
								LEFT JOIN vacancies d ON d.id = b.vacancy_id
							    LEFT JOIN applicants e ON e.id = c.applicant_id
							WHERE (
									a.item_details LIKE '%$query%'
									OR CONCAT(e.lname, ', ', e.fname, IF(e.mname IS NULL, '', CONCAT(' ', e.mname)), IF(e.suffix IS NULL, '', CONCAT(' ', e.suffix))) LIKE '%$query%'
									OR e.phone_no LIKE '%$query%'
									OR e.email_add LIKE '%$query%'
									OR d.depcode LIKE '%$query%'
								) AND $lineup_status_filter";
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

			foreach($query_result as $key => $value) {	
				$data['data'][] = array(
					'lineup_header_id' 			=> $value->lineup_header_id,
					'lineup_vacancy_id'			=> $value->lineup_vacancy_id,
					'lineup_applicant_id' 		=> $value->lineup_applicant_id,
					'applicant_id' 				=> $value->applicant_id,
					'item_details' 				=> $value->item_details,
					// 'item_code' 				=> $value->item_code,
					'applicant_name' 			=> $value->applicant_name,
					'phone_no' 					=> $value->phone_no,
					'email_add' 				=> $value->email_add,
					'date_lineup_opened'		=> $value->date_opened,
					'plantilla_item_no' 		=> $value->plantilla_item_no,
					'posgrade' 					=> $value->posgrade,
					'depcode' 					=> $value->depcode,
					'latest_posting' 			=> $value->latest_posting,
					'date_lineup' 				=> $value->date_lineup,
					'status_hr_test' 			=> $value->status_hr_test,
					'date_hr_test' 				=> $value->date_hr_test,
					'remarks_hr_test' 			=> $value->remarks_hr_test,
					'status_interview' 			=> $value->status_interview,
					'date_interview' 			=> $value->date_interview,
					'remarks_interview' 		=> $value->remarks_interview,
					'is_done_bi' 				=> (is_null($value->is_done_bi) || $value->is_done_bi == "0") ? false: true,
					'is_done_paf' 				=> (is_null($value->is_done_paf) || $value->is_done_paf == "0") ? false: true,
					'is_done_nir' 				=> (is_null($value->is_done_nir) || $value->is_done_nir == "0") ? false: true,
					'remarks' 					=> $value->remarks,
					'status_psb' 				=> $value->status_psb,
					'is_selected' 				=> $value->is_selected,
					'is_locked' 				=> $value->is_locked,
					'date_psb' 					=> $value->date_psb
				);
			}

			$data['totalCount'] = $query_count[0]->count;
			return $data;

		}
		catch(Exception $e) {
			print $e->getMessage();
			die();	
		}
	}

	// public function view() {
	// 	try {
	// 		die(json_encode($this->generateview($this->input->post('id'))));
	// 	}
	// 	catch(Exception $e) {
	// 		print $e->getMessage();
	// 		die();
	// 	}
	// }

	// public function generateview($id) {
	// 	try {
	// 		#update session
	// 		$this->load->model('Session');$this->Session->Validate();
	// 		$this->load->library('callable_functions');

	// 		#header details
	// 		$commandText = "SELECT 
	// 							a.date_communication,
	// 							a.date_logged,
	// 							a.sequence_number,
	// 							a.communication_number,
	// 							a.subject,
	// 							b.description AS record_type,
	// 							c.description AS from_name,
	// 							d.description AS to_name
	// 						FROM adminservices_records_header a
	// 							LEFT JOIN record_types b ON a.record_type_id = b.id
	// 							LEFT JOIN adminservices_records_from_to c ON a.from_id = c.id
	// 							LEFT JOIN adminservices_records_from_to d ON a.to_id = d.id
	// 						WHERE a.id = $id AND a.active = 1 AND a.communication_type = 'Incoming'";
	// 		$result = $this->db->query($commandText);
	// 		$query_result = $result->result();

	// 		foreach($query_result as $key => $val) {
	// 			$control_number = $this->callable_functions->GenerateControlNumber($val->date_communication, $val->date_logged, $val->sequence_number);
	// 			$details = $this->callable_functions->CommunicationDetailsBuilder($val->record_type, $val->communication_number, $val->subject);

	// 			$data['header_details'][] = array(
	// 				'control_number'		=> $control_number,
	// 				'date_communication' 	=> date('m/d/Y', strtotime($val->date_communication)),
	// 				'details' 				=> stripslashes($details),
	// 				'from_name' 			=> $val->from_name,
	// 				'to_name' 				=> $val->to_name
	// 			);
	// 		}

	// 		#tracking details
	// 		$commandText = "SELECT 
	// 							a.date_logged,
	// 							a.status,
	// 							IF(b.description IS NULL, '', b.description) AS assigned_division_name,
	// 							a.side_notes,
	// 							IF(c.action_taken IS NULL, '', c.action_taken) AS action_taken,
	// 							IF(c.date_action_taken IS NULL, '', DATE_FORMAT(c.date_action_taken, '%m/%d/%Y')) AS date_action_taken,
	// 							IF((TIMESTAMPDIFF(DAY, a.date_logged, c.date_action_taken)) IS NULL, '', (TIMESTAMPDIFF(DAY, a.date_logged, c.date_action_taken))) AS duration_action_taken
	// 						FROM adminservices_records_header a
	// 							LEFT JOIN divisions b ON a.division_id = b.id
	// 							LEFT JOIN adminservices_records_actions_taken c ON a.action_taken_id = c.id
	// 						WHERE a.id = $id AND a.active = 1";
	// 		$result = $this->db->query($commandText);
	// 		$tracking_result = $result->result();

	// 		foreach($tracking_result as $key => $val) {
	// 			$data['tracking_details'][] = array(
	// 				'date_logged'				=> date('m/d/Y - h:i A', strtotime($val->date_logged)),
	// 				'status'					=> $val->status,
	// 				'assigned_division_name'	=> $val->assigned_division_name,
	// 				'side_notes'				=> mb_strtoupper($val->side_notes),
	// 				'action_taken'				=> $val->action_taken,
	// 				'action_taken_date'			=> $val->date_action_taken,
	// 				'duration_action_taken'		=> $val->duration_action_taken
	// 			);
	// 		}

	// 		#filed copy details
	// 		$commandText = "SELECT * 
	// 						FROM adminservices_records_attachments 
	// 						WHERE record_id = $id 
	// 							AND active = 1";
	// 		$result = $this->db->query($commandText);
	// 		$attachments_result = $result->result();

	// 		foreach($attachments_result as $key => $val) {
	// 			$attachment_full_name = $val->attachment_name.".".$val->attachment_extension;
	// 			$attachment_path = '../documents/Incoming and Outgoing Communications/'.$attachment_full_name;

	// 			$data['attachments'][] = array(
	// 				'attachment_name' 		=> $val->attachment_name,
	// 				'attachment_full_name' 	=> $attachment_full_name,
	// 				'attachment_path'		=> $attachment_path,
	// 				'date_uploaded'			=> $val->date_uploaded,
	// 				'description'			=> $val->description
	// 			);
	// 		}

	// 		$data["success"] = true;			
	// 		$data["accounts_count"] = count($query_result);
	// 		$data["history_count"] = count($tracking_result);
	// 		$data["attachments_count"] = count($attachments_result);

	// 		return $data;
	// 	}
	// 	catch(Exception $e) {
	// 		print $e->getMessage();
	// 		die();
	// 	}
	// }

	public function crud() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();

			$type = $this->input->post('type');

			$this->load->model('Access'); $this->Access->rights($this->modulename('link'), $type, null);

			if($type == "Delete") {
				$lineup_header_id 	 	= $this->input->post('lineup_header_id');
				$lineup_vacancy_id 	 	= $this->input->post('lineup_vacancy_id');
				$lineup_applicant_id  	= $this->input->post('lineup_applicant_id');
				$applicant_id 	 	 	= $this->input->post('applicant_id');

				// check first if vacancy has existing applicant, if there is, do not proceed and prompt error
				if(isset($applicant_id) && $applicant_id != "0") {
					$data = array("success"=> false, "data"=>"Vacancy has an existing applicant. Remove applicant first before deleting this vacancy.");
					die(json_encode($data));
				}

				// if there are no other vacancies from the same header, delete the header
				$commandText1 = "SELECT * FROM selection_lineup_vacancies WHERE header_id = $lineup_header_id AND id <> $lineup_vacancy_id";
				$result1 = $this->db->query($commandText1);
				$query_result1 = $result1->result();

				if(count($query_result1) == 0) { 
					$commandText2 = "DELETE FROM selection_lineup_header WHERE id = $lineup_header_id";
					$result2 = $this->db->query($commandText2);

					$this->load->model('Logs'); $this->Logs->audit_logs($lineup_header_id, 'selection_lineup_header', $type, $this->modulename('label'));
				}

				// if there are no other applicant from the same vacancy, delete the vacancy
				$commandText3 = "SELECT * FROM selection_lineup_applicants WHERE lineup_vacancy_id = $lineup_vacancy_id AND applicant_id IS NOT NULL";
				$result3 = $this->db->query($commandText3);
				$query_result3 = $result3->result();

				if(count($query_result3) == 0) { 
					// delete the vacancy record
					$commandText4 = "DELETE FROM selection_lineup_vacancies WHERE id = $lineup_vacancy_id";
					$result4 = $this->db->query($commandText4);

					$this->load->model('Logs'); $this->Logs->audit_logs($lineup_vacancy_id, 'selection_lineup_vacancies', $type, $this->modulename('label'));
				}

				// delete the (supposed empty) applicant record bound to the vacancy
				$commandText5 = "DELETE FROM selection_lineup_applicants WHERE lineup_vacancy_id = $lineup_vacancy_id AND id = $lineup_applicant_id";
				$result5 = $this->db->query($commandText5);

				$this->load->model('Logs'); $this->Logs->audit_logs($lineup_applicant_id, 'selection_lineup_applicants', $type, $this->modulename('label'));
			}
			else {
				if($type == "Add") {
					$vacancies_ids 			= explode(',', $this->input->post('vacancies_ids'));
					$item_codes 			= explode(',', $this->input->post('item_codes'));
					$item_descs 			= explode(',', $this->input->post('item_descs'));
					$item_desc_details		= explode(',', $this->input->post('item_desc_details'));
					
					$i = 0; // this loop skips if only 1 vacancy is selected
					foreach($vacancies_ids as $key => $value) {
						if($i > 0) {
							if($item_codes[0] != $item_codes[$i] || $item_descs[0] != $item_descs[$i] ||  $item_desc_details[0] != $item_desc_details[$i]) {
								// check if different item_desc_details
								$data = array("success"=> false, "data"=>"Cannot select items with different details at the same time.");
								die(json_encode($data));
							}
						}
						$i++;
					}

					$i = 0;
					$lineup_header_id = null;
					foreach($vacancies_ids as $key => $value) {
						$vacancy_id = $vacancies_ids[$i];
						// check first if vacancy already exists in the selection lineup , if exists and not locked, do not save and prompt error
						$commandText = "SELECT * FROM selection_lineup_vacancies WHERE vacancy_id = $vacancy_id AND is_locked IS NULL";
						$result = $this->db->query($commandText);
						$query_result = $result->result();

						if(count($query_result) > 0) {
							$data = array("success"=> false, "data"=>"Vacancy already exists.");
							die(json_encode($data));
						}

						// transaction start here
						$this->db->trans_start();
						if(!isset($lineup_header_id)) {
							$item_details = $item_descs[$i];
							if(isset($item_desc_details[$i]) && $item_desc_details[$i] != "") {$item_details .= " " . $item_desc_details[$i];}

							$this->load->model('selection_lineup_header');
							$this->selection_lineup_header->item_details 	= $item_details;
							$this->selection_lineup_header->date_opened = date('Y-m-d H:i:s');
							$this->selection_lineup_header->save(0);
							$lineup_header_id = $this->selection_lineup_header->id;
						}

						$this->load->model('selection_lineup_vacancies');
						$this->selection_lineup_vacancies->header_id 	= $lineup_header_id;
						$this->selection_lineup_vacancies->vacancy_id 	= $vacancy_id;
						$this->selection_lineup_vacancies->date_opened 	= date('Y-m-d H:i:s');
						$this->selection_lineup_vacancies->save(0);

						$this->load->model('selection_lineup_applicants');
						$this->selection_lineup_applicants->lineup_vacancy_id 	= $this->selection_lineup_vacancies->id;
						$this->selection_lineup_applicants->save(0);
						//transaction ends here
						$this->db->trans_complete();

						$this->load->model('Logs'); $this->Logs->audit_logs($this->selection_lineup_vacancies->id, 'selection_lineup_vacancies', $type, $this->modulename('label'));
						$this->load->model('Logs'); $this->Logs->audit_logs($this->selection_lineup_applicants->id, 'selection_lineup_applicants', $type, $this->modulename('label'));
						$i++;
					}

					$this->load->model('Logs'); $this->Logs->audit_logs($lineup_header_id, 'selection_lineup_header', $type, $this->modulename('label'));
				}
				else if($type == "Edit") {
					$lineup_applicant_ids 	= explode(',', $this->input->post('lineup_applicant_ids'));
					$lineup_vacancy_ids 	= explode(',', $this->input->post('lineup_vacancy_ids'));
					$applicant_ids 			= explode(',', $this->input->post('applicant_ids'));
					$dates_lineup 			= explode(',', $this->input->post('dates_lineup'));
					$statuses_hr_test 		= explode(',', $this->input->post('statuses_hr_test'));
					$dates_hr_test 			= explode(',', $this->input->post('dates_hr_test'));
					$remarks_hr_test 		= explode(',', $this->input->post('remarks_hr_test'));
					$statuses_interview 	= explode(',', $this->input->post('statuses_interview'));
					$dates_interview 		= explode(',', $this->input->post('dates_interview'));
					$remarks_interview 		= explode(',', $this->input->post('remarks_interview'));
					$are_done_bi 			= explode(',', $this->input->post('are_done_bi'));
					$are_done_paf 			= explode(',', $this->input->post('are_done_paf'));
					$are_done_nir 			= explode(',', $this->input->post('are_done_nir'));
					$remarks 				= explode(',', $this->input->post('remarks'));
					$statuses_psb 			= explode(',', $this->input->post('statuses_psb'));
					$are_selected 			= explode(',', $this->input->post('are_selected'));
					$prev_values 			= explode(',', $this->input->post('prev_values'));

					$i = 0;
					foreach($lineup_applicant_ids as $key => $value) {
						if($applicant_ids[$i] == "") {
							$data = array("success"=> false, "data"=>"Please select an applicant first!");
							die(json_encode($data));
						}
						$date_lineup  		= isset($dates_lineup[$i]) && $dates_lineup[$i] != "" ? date('Y-m-d H:i:s', strtotime($dates_lineup[$i])) : null;
						$status_hr_test 	= isset($statuses_hr_test[$i]) && $statuses_hr_test[$i] != "" ? $statuses_hr_test[$i] : null;
						$date_hr_test  		= isset($dates_hr_test[$i]) && $dates_hr_test[$i] != "" ? date('Y-m-d', strtotime($dates_hr_test[$i])) : null;
						$remarks_hr_test  	= isset($remarks_hr_test[$i]) && $remarks_hr_test[$i] != "" ? $remarks_hr_test[$i] : null;
						$status_interview 	= isset($statuses_interview[$i]) && $statuses_interview[$i] != "" ? $statuses_interview[$i] : null;
						$date_interview  	= isset($dates_interview[$i]) && $dates_interview[$i] != "" ? date('Y-m-d', strtotime($dates_interview[$i])) : null;
						$remarks_interview  = isset($remarks_interview[$i]) && $remarks_interview[$i] != "" ? $remarks_interview[$i] : null;
						$remarks  			= isset($remarks[$i]) && $remarks[$i] != "" ? $remarks[$i] : null;
						$status_psb 		= isset($statuses_psb[$i]) && $statuses_psb[$i] != "" ? $statuses_psb[$i] : null;
						$is_selected 		= isset($are_selected[$i]) && $are_selected[$i] != "" ? $are_selected[$i] : null;

						$this->load->model('selection_lineup_applicants');
						$this->selection_lineup_applicants->id 					= $lineup_applicant_ids[$i];
						$this->selection_lineup_applicants->lineup_vacancy_id 	= $lineup_vacancy_ids[$i];
						$this->selection_lineup_applicants->applicant_id 		= $applicant_ids[$i];
						$this->selection_lineup_applicants->date_lineup 		= $date_lineup;
						$this->selection_lineup_applicants->date_hr_test 		= $date_hr_test;
						$this->selection_lineup_applicants->status_hr_test 		= $status_hr_test;
						$this->selection_lineup_applicants->remarks_hr_test 	= $remarks_hr_test;
						$this->selection_lineup_applicants->status_interview 	= $status_interview;
						$this->selection_lineup_applicants->date_interview 		= $date_interview;
						$this->selection_lineup_applicants->remarks_interview 	= $remarks_interview;
						$this->selection_lineup_applicants->is_done_bi 			= $are_done_bi[$i] == "false" ? 0: 1;
						$this->selection_lineup_applicants->is_done_paf 		= $are_done_paf[$i] == "false" ? 0: 1;
						$this->selection_lineup_applicants->is_done_nir 		= $are_done_nir[$i] == "false" ? 0: 1;
						$this->selection_lineup_applicants->remarks 			= $remarks[$i];
						$this->selection_lineup_applicants->status_psb 			= $status_psb;
						$this->selection_lineup_applicants->is_selected 		= $is_selected;
						$this->selection_lineup_applicants->save($lineup_applicant_ids[$i]);
						
						$this->load->model('Logs'); $this->Logs->audit_logs($this->selection_lineup_applicants->id, 'selection_lineup_applicants', $type, $this->modulename('label'));

						// echo $prev_values[$i];
						// echo "space";

            			// log updates to history table
						$this->load->model('selection_lineup_updates_history');
						$this->selection_lineup_updates_history->selection_lineup_applicants_id  = $lineup_applicant_ids[$i];
						$this->selection_lineup_updates_history->updated_by     				= $this->session->userdata('id');
						$this->selection_lineup_updates_history->prev_values   					= str_replace("-", ",", $prev_values[$i]);
						$this->selection_lineup_updates_history->date_logged 					= date('Y-m-d H:i:s');
						$this->selection_lineup_updates_history->save(0);

						$i++;
					}
				}	
			}
			
			$arr = array();  
			$arr['success'] = true;
			if ($type == "Add")
				$arr['data'] = "Successfully Added to Selection Lineup";
			if ($type == "Edit")
				$arr['data'] = "Successfully Updated the Selection Lineup";
			if ($type == "Delete")
				$arr['data'] = "Successfully Deleted from Selection Lineup";
			die(json_encode($arr));
		}
		catch(Exception $e) {
			$data = array("success"=> false, "data"=>$e->getMessage());
			die(json_encode($data));
		}
	}

	// no more outer function because it doesnt need reports generation
	public function departments_list() {
		try {
			$query = mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($_GET['query'])));
			$commandText = "SELECT * FROM departments WHERE depcode LIKE '%$query%' OR description LIKE '%$query%' ORDER BY id ASC";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			if(count($query_result) == 0) {
				$data['totalCount']  = 0;
				$data['data'] 	= array();
				die(json_encode($data));
			}

			foreach($query_result as $key => $value) {
				$data['data'][] = array(
					'id' 			=> $value->id,
					'depcode' 	=> $value->depcode,
					'description' 	=> $value->description);
			}
			$data['totalCount'] = count($query_result);
			die(json_encode($data));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();
		}
	}

	// no more outer function because it doesnt need reports generation
	public function vacancies_list() {
		try {
			$query = mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($_GET['query'])));
			$depcode = $_GET['depcode'];

			$commandText = "SELECT *,
								IF(latest_posting IS NULL, 'Unpublished', DATE_FORMAT(latest_posting, '%m/%d/%Y')) AS latest_posting
							FROM vacancies 
							WHERE is_vacant = 1
								AND depcode = '$depcode'
							ORDER BY depcode ASC, posgrade DESC";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			if(count($query_result) == 0) {
				$data['totalCount'] = 0;
				$data['data'] = array();
				die(json_encode($data));
			}

			foreach($query_result as $key => $value) {
				$item_details = $value->item_desc;
				if(isset($value->item_desc_detail) && $value->item_desc_detail != "")
					$item_details .= " " . $value->item_desc_detail;

				$data['data'][] = array(
					'id' 					=> $value->id,
					'plantilla_item_no' 	=> $value->plantilla_item_no,
					'item_code' 			=> $value->item_code,
					'item_desc' 			=> $value->item_desc,
					'item_desc_detail' 		=> $value->item_desc_detail,
					'posgrade' 				=> $value->posgrade,
					'latest_posting' 		=> $value->latest_posting,
					'item_details' 			=> $item_details,
					'latest_posting' 		=> $value->latest_posting,
					'is_selected' 			=> false);
			}
			$data['totalCount'] = count($query_result);
			die(json_encode($data));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();
		}
	}

	public function applicant_crud() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();

			$type = $this->input->post('type');
			$id = $this->input->post('id'); // lineup_applicant_id


			$this->load->model('Access'); $this->Access->rights($this->modulename('link'), $type, null);

			if($type == "Delete") {
				$commandText = "UPDATE selection_lineup_applicants SET applicant_id = NULL, date_lineup = NULL, date_hr_test = NULL, remarks_hr_test = NULL, date_interview = NULL, remarks_interview = NULL, is_done_bi = NULL, is_done_paf = NULL, is_done_nir = NULL, remarks = NULL, is_selected = NULL WHERE id = $id";
				$result = $this->db->query($commandText);
			}
			else {
				if($type == "Add") {
					$applicant_id = $this->input->post('applicant_id');

					// query to check if applicant to place already exists on the same header
					$commandText = "SELECT *
									FROM selection_lineup_applicants a
										LEFT JOIN selection_lineup_vacancies b ON b.id = a.lineup_vacancy_id
									    LEFT JOIN selection_lineup_header c ON c.id = b.header_id
									WHERE a.applicant_id = $applicant_id
										AND c.id = (
											SELECT a.id AS header_id
											FROM selection_lineup_header a
												LEFT JOIN selection_lineup_vacancies b ON b.header_id = a.id
												LEFT JOIN selection_lineup_applicants c ON c.lineup_vacancy_id = b.id
											WHERE c.id = $id
										)";
					$result = $this->db->query($commandText);
					$query_result = $result->result();

					if(count($query_result) > 0) {
						$data = array("success"=> false, "data"=>"Cannot place already existing applicant.");
						die(json_encode($data));
					}

					$commandText = "SELECT * FROM selection_lineup_applicants WHERE id = $id";
					$result = $this->db->query($commandText);
					$query_result = $result->result();

					// if there is no existing applicant on the clicked row, proceed as normal
					if($query_result[0]->applicant_id == NULL) {
						$date_lineup = date('Y-m-d H:i:s');
						$commandText = "UPDATE selection_lineup_applicants SET applicant_id = $applicant_id, date_lineup = '$date_lineup' WHERE id = $id";
						$result = $this->db->query($commandText);
					}
					else {
						$this->load->model("selection_lineup_applicants");
						$this->selection_lineup_applicants->lineup_vacancy_id  	= $query_result[0]->lineup_vacancy_id;
						$this->selection_lineup_applicants->applicant_id  		= $applicant_id;
						$this->selection_lineup_applicants->date_lineup  		= date('Y-m-d H:i:s');
						$this->selection_lineup_applicants->save(0);
					}
				}
				else if($type == "Edit") {
					// empty for now
				}
			}

			$this->load->model('Logs'); $this->Logs->audit_logs($id, 'selection_lineup_applicants', $type, $this->modulename('label'));

			$arr = array();  
			$arr['success'] = true;
			if ($type == "Add")
				$arr['data'] = "Successfully Placed to Selection Lineup";
			if ($type == "Edit")
				$arr['data'] = "Successfully Updated the Selection Lineup";
			if ($type == "Delete")
				$arr['data'] = "Successfully Removed from Selection Lineup";
			die(json_encode($arr));
		}
		catch(Exception $e) {
			$data = array("success"=> false, "data"=>$e->getMessage());
			die(json_encode($data));
		}
	}

	// public function psb_list() {
	// 	try {
	// 		$query = $this->esc_str($_GET['query']);
	// 		die(json_encode($this->generatepsb_list($query, 'Grid')));
	// 	}
	// 	catch(Exception $e) {
	// 		print $e->getMessage();
	// 		die();	
	// 	}
	// }
 	
	// public function generatepsb_list($query, $transaction_type) {
	// 	try {
	// 		$this->load->library('session');

	// 		$limitQuery = "";
	// 		if($transaction_type == 'Grid') {
	// 			$limit = $_GET['limit'];
	// 			$start = $_GET['start'];
	// 			$limitQuery = " LIMIT $start, $limit";
	// 		}

	// 		$commandText = "SELECT a.id AS lineup_vacancy_id,
	// 							a.header_id AS lineup_header_id,
	// 							a.selected_lineup_applicant_id,
	// 						    c.item_desc, 
	// 						    c.plantilla_item_no,
	// 						    c.posgrade,
	// 						    c.depcode,
	// 						    DATE_FORMAT(c.latest_posting, '%m/%d/%Y') AS latest_posting,
	// 						    CONCAT(d.lname, ', ', d.fname, IF(d.mname IS NULL, '', CONCAT(' ', d.mname)), IF(d.suffix IS NULL, '', CONCAT(' ', d.suffix))) AS selected_applicant_name,
	// 						    DATE_FORMAT(a.date_psb, '%m/%d/%Y') AS date_psb
	// 						FROM selection_lineup_vacancies a
	// 							LEFT JOIN selection_lineup_header b ON b.id = a.header_id
	// 							LEFT JOIN vacancies c ON c.id = a.vacancy_id
	// 							LEFT JOIN applicants d ON d.id = a.selected_lineup_applicant_id
	// 						WHERE a.is_locked IS NULL AND a.date_psb IS NULL";
	// 		$result = $this->db->query($commandText);
	// 		$query_result = $result->result();

	// 		$commandText = "SELECT count(*) AS count
	// 						FROM selection_lineup_vacancies a
	// 							LEFT JOIN selection_lineup_header b ON b.id = a.header_id
	// 							LEFT JOIN vacancies c ON c.id = a.vacancy_id
	// 							LEFT JOIN applicants d ON d.id = a.selected_lineup_applicant_id
	// 						WHERE a.is_locked IS NULL AND a.date_psb IS NULL";
	// 		$result = $this->db->query($commandText);
	// 		$query_count = $result->result();

	// 		if(count($query_result) == 0 & $transaction_type == 'Report') {
	// 			$data = array("success"=> false, "data"=>'No records found!');
	// 			die(json_encode($data));
	// 		}

	// 		if(count($query_result) == 0 & $transaction_type == 'Grid') {
	// 			$data["totalCount"] = 0;
	// 			$data["data"] 		= array();
	// 			die(json_encode($data));
	// 		}

	// 		foreach($query_result as $key => $value) {	
	// 			$data['data'][] = array(
	// 				'lineup_vacancy_id'				=> $value->lineup_vacancy_id,
	// 				'lineup_header_id' 				=> $value->lineup_header_id,
	// 				'selected_lineup_applicant_id' 	=> $value->selected_lineup_applicant_id,
	// 				'item_desc' 					=> $value->item_desc,
	// 				'plantilla_item_no' 			=> $value->plantilla_item_no,
	// 				'posgrade' 						=> $value->posgrade,
	// 				'depcode' 						=> $value->depcode,
	// 				'latest_posting' 				=> $value->latest_posting,
	// 				'selected_applicant_name' 		=> $value->selected_applicant_name,
	// 				'date_psb' 						=> $value->date_psb
	// 				// 'remarks' 					=> $value->remarks
	// 			);
	// 		}

	// 		$data['totalCount'] = $query_count[0]->count;
	// 		return $data;
	// 	}
	// 	catch(Exception $e) {
	// 		print $e->getMessage();
	// 		die();	
	// 	}
	// }

	private function esc_str($x) {
		return mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($x)));
	}
}