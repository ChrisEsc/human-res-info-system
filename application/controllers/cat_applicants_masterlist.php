<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cat_Applicants_Masterlist extends CI_Controller {
	/**
	*/ 
	private function modulename($type) {		
		if($type == 'link')
			return 'cat_applicants_masterlist';
		else 
			return 'Applicants Masterlist';
	}

	public function index() {
		$this->load->model('Page');		
        $this->Page->set_page($this->modulename('link'));
	}

	public function applicants_list() {
		try {
			$query = $this->esc_str($_GET['query']);
			die(json_encode($this->generateapplicants_list($query, 'Grid')));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();	
		}
	}
 	
	public function generateapplicants_list($query, $transaction_type) {
		try {
			$this->load->library('session');

			$limitQuery = "";
			if($transaction_type == 'Grid') {
				$limit = $_GET['limit'];
				$start = $_GET['start'];
				$limitQuery = " LIMIT $start, $limit";
			}

			$commandText = "SELECT 
								a.id,
								CONCAT(a.fname, ' ', IF(a.mname IS NULL, '', CONCAT(a.mname, ' ')), ' ', a.lname, IF(a.suffix IS NULL, '', CONCAT(', ', a.suffix))) AS applicant_name,
								a.phone_no,
								IF(a.email_add IS NULL, '', a.email_add) AS email_add,
								IF(b.position_applied IS NULL, '', b.position_applied) AS position_applied,
								IF(b.notes IS NULL, '', b.notes) AS notes,
								IF(b.date_application_received IS NULL, '', DATE_FORMAT(b.date_application_received, '%m/%d/%Y')) AS date_application_received,
								IF(b.applic_type IS NULL, '', b.applic_type) AS applic_type
							FROM applicants a
								LEFT JOIN applicants_applications b ON b.applicant_id = a.id
							WHERE (
									CONCAT(a.fname, ' ', IF(a.mname IS NULL, '', CONCAT(a.mname, ' ')), a.lname) LIKE '%$query%'
									OR a.phone_no LIKE '%$query%'
									OR a.email_add LIKE '%$query%'
								)
								AND a.active = 1
							ORDER BY id DESC
							$limitQuery";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$commandText = "SELECT count(*) as count
							FROM applicants a
							WHERE (
									CONCAT(a.fname, ' ', IF(a.mname IS NULL, '', CONCAT(a.mname, ' ')), a.lname) LIKE '%$query%'
								)
								AND a.active = 1";
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
				$applicant_id = $value->id;

				// education
				$commandText2 = "SELECT a.course,
									a.school
								FROM applicants_education a
									LEFT JOIN applicants b ON b.id = a.applicant_id
								WHERE b.id = $applicant_id
								ORDER BY a.educ_level DESC
								LIMIT 1";
				$result2 = $this->db->query($commandText2);
				$query_result_education = $result2->result();

				$education = "";
				if(count($query_result_education) > 0) {
					$education = $query_result_education[0]->course . " (" . $query_result_education[0]->school . ")";
				}

				// eligibility
				$commandText3 = "SELECT a.id,
									GROUP_CONCAT(DISTINCT a.title SEPARATOR '<br>') AS eligibility
								FROM applicants_eligibility a 
									LEFT JOIN applicants b ON b.id = a.applicant_id
								WHERE b.id = $applicant_id
								GROUP BY b.id";
				$result3 = $this->db->query($commandText3);
				$query_result_eligibility = $result3->result();

				$eligibility = "";
				if(count($query_result_eligibility) > 0 ) $eligibility = $query_result_eligibility[0]->eligibility;

				// experience
				$commandText4 = "SELECT a.position,
									a.agency_company
				 				FROM applicants_experience a
				 					LEFT JOIN applicants b ON b.id = a.applicant_id
				 				WHERE b.id = $applicant_id";
				$result4 = $this->db->query($commandText4);
				$query_result_experience = $result4->result();

				$experience = "";
				foreach($query_result_experience as $key4 => $value4) {
					$experience .= $value4->position . " - " . $value4->agency_company . "<br>";
				}

				$data['data'][] = array(
					'id' 						=> $applicant_id,
					'applicant_name'			=> $value->applicant_name,
					'phone_no' 					=> $value->phone_no,
					'email_add'					=> $value->email_add,
					'position_applied' 			=> $value->position_applied,
					'notes'						=> $value->notes,
					'educ_highest' 				=> $education,
					'eligibility' 				=> $eligibility,
					'experience' 				=> $experience,
					'date_application_received' => $value->date_application_received,
					'applic_type' 				=> $value->applic_type
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
			#update session
			$this->load->model('Session');$this->Session->Validate();

			#header
			$commandText = "SELECT 
								id,
								CONCAT(fname, ' ', IF(mname IS NULL, '', CONCAT(mname, ' ')), ' ', lname, IF(suffix IS NULL, '', CONCAT(', ', suffix))) AS applicant_name,
								IF(phone_no IS NULL, '', phone_no) AS phone_no,
								IF(email_add IS NULL, '', email_add) AS email_add
							FROM applicants
							WHERE id = $id AND active = 1";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			foreach($query_result as $key => $value) {
				$data['header_details'][] = array(
					'id'				=> $value->id,
					'applicant_name' 	=> $value->applicant_name,
					// 'age' 				=> $value->age,
					'phone_no' 			=> $value->phone_no,
					'email_add' 		=> $value->email_add
				);
			}

			#education
			$commandText = "SELECT a.*,
								c.description AS educ_level_desc
							FROM applicants_education a
								LEFT JOIN applicants b ON b.id = a.applicant_id
								LEFT JOIN education_levels c ON c.level = a.educ_level
							WHERE a.applicant_id = $id
							ORDER BY educ_level DESC";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$data['education_details'] = []; // prevents throwing an error if no education details
			foreach($query_result as $key => $value) {
				$data['education_details'][] = array(
					'id' 				=> $value->id,
					'educ_level'		=> $value->educ_level,
					'educ_level_desc' 	=> $value->educ_level_desc,
					'school' 			=> $value->school,
					'course'			=> $value->course,
					'from_year' 		=> is_null($value->from_year) ? '': $value->from_year,
					'to_year'			=> is_null($value->to_year) ? '': $value->to_year,
					'units_earned' 		=> is_null($value->units_earned) ? '': $value->units_earned,
					'year_grad'			=> is_null($value->year_grad) ? '': $value->year_grad,
					'acad_honor' 		=> is_null($value->acad_honor) ? '': $value->acad_honor
				);
			}

			#eligibility
			$commandText = "SELECT a.*
							FROM applicants_eligibility a
								LEFT JOIN applicants b ON b.id = a.applicant_id
							WHERE a.applicant_id = $id
							ORDER BY id ASC";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$data['eligibility_details'] = []; // prevents throwing an error if no eligibility details
			foreach($query_result as $key => $value) {
				$data['eligibility_details'][] = array(
					'id' 				=> $value->id,
					'title'				=> $value->title,
					'rating' 			=> is_null($value->rating) ? '': $value->rating,
					'exam_date'			=> is_null($value->exam_date) ? '': date('M j, Y', strtotime($value->exam_date)),
					'exam_place' 		=> is_null($value->exam_place) ? '': $value->exam_place,
					'license_no'		=> is_null($value->license_no) ? '': $value->license_no,
					'date_validity' 	=> is_null($value->date_validity) ? '': date('M j, Y', strtotime($value->date_validity))
				);
			}

			#experience
			$commandText = "SELECT a.*,
								c.description AS employment_status_desc
							FROM applicants_experience a
								LEFT JOIN applicants b ON b.id = a.applicant_id
								LEFT JOIN employment_statuses c ON c.id = a.employment_status_id
							WHERE a.applicant_id = $id
							ORDER BY a.from_date DESC";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$data['experience_details'] = []; // prevents throwing an error if no experience details
			foreach($query_result as $key => $value) {
				$data['experience_details'][] = array(
					'id' 				 	 	=> $value->id,
					'employment_status_id' 	 	=> is_null($value->employment_status_id) ? '': $value->employment_status_id,
					'employment_status_desc'  	=> is_null($value->employment_status_desc) ? '': $value->employment_status_desc,
					// 'from_date'				 	=> date('M j, Y', strtotime($value->from_date)),
					// 'to_date' 				 	=> is_null($value->to_date) ? 'Present': date('M j, Y', strtotime($value->to_date)),
					'from_date'				 	=> is_null($value->from_date) ? '': $value->from_date, 							// replace with above after initial import
					'to_date' 				 	=> is_null($value->to_date) ? '': date('M j, Y', strtotime($value->to_date)), 	// replace with above after initial import
					'position'					=> $value->position,
					'agency_company' 			=> $value->agency_company,
					'monthly_salary'			=> is_null($value->monthly_salary) ? '': number_format($value->monthly_salary, 2, '.', ','),
					'salary_grade' 				=> is_null($value->salary_grade) ? '': $value->salary_grade,
					'government_service'		=> is_null($value->government_service) ? '': $value->government_service
				);
			}

			#training
			$commandText = "SELECT a.*,
								c.description AS training_type_desc
							FROM applicants_training a
								LEFT JOIN applicants b ON b.id = a.applicant_id
								LEFT JOIN training_types c ON c.id = a.training_type_id
							WHERE a.applicant_id = $id
							ORDER BY a.from_date DESC";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$data['training_details'] = [];  // prevents throwing an error if no training details
			foreach($query_result as $key => $value) {
				$data['training_details'][] = array(
					'id' 				 	=> $value->id,
					'training_type_id' 	 	=> is_null($value->training_type_id) ? '': $value->training_type_id,
					'training_type_desc'	=> is_null($value->training_type_desc) ? '': $value->training_type_desc,
					'title'					=> $value->title,
					'duration' 				=> number_format($value->duration, 1, '.', ''),
					'from_date'				 	=> is_null($value->from_date) ? '': date('M j, Y', strtotime($value->from_date)),
					'to_date' 					=> is_null($value->to_date) ? '': date('M j, Y', strtotime($value->to_date)),
					'conducted_by' 			=> is_null($value->conducted_by) ? '': $value->conducted_by
				);
			}

			$data["success"] = true;			
			$data["education_count"] = count($data['education_details']);
			$data["eligibility_count"] = count($data['eligibility_details']);
			$data["experience_count"] = count($data['experience_details']);
			$data["training_count"] = count($data['training_details']);
			return $data;
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();	
		}
	}

	public function applicationsview() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();
			
			$id = $_GET['id'];;

			$commandText = "SELECT a.id AS lineup_applicant_id,
								d.item_code,
							    d.item_desc,
							    d.item_desc_detail,
							    d.posgrade,
							    d.depcode,
							    IF(c.date_psb IS NULL, 'Ongoing', 'Completed') AS psb_status,
								a.date_lineup,
							    a.date_hr_test,
							    a.remarks_hr_test,
							    a.date_interview,
							    a.remarks_interview,
							    a.is_done_bi,
							    a.is_done_paf,
							    a.is_done_nir,
							    c.date_psb,
							    IF(c.date_psb IS NULL, NULL, IF(is_selected IS NULL, 'Not Selected', 'Selected')) AS psb_result
							FROM selection_lineup_applicants a
								LEFT JOIN applicants b ON b.id = a.applicant_id
							    LEFT JOIN selection_lineup_vacancies c ON c.id = a.lineup_vacancy_id
							    LEFT JOIN vacancies d ON d.id = c.vacancy_id
							WHERE b.id = $id";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$countCommandText = "SELECT count(a.id) AS count
							FROM selection_lineup_applicants a
								LEFT JOIN applicants b ON b.id = a.applicant_id
							    LEFT JOIN selection_lineup_vacancies c ON c.id = a.lineup_vacancy_id
							    LEFT JOIN vacancies d ON d.id = c.vacancy_id
							WHERE b.id = $id";
			$count_result = $this->db->query($countCommandText);
			$query_count = $count_result->result();
					
			if(count($query_result) == 0) {
				$data["totalCount"] = 0;
				$data["data"] 		= array();
				die(json_encode($data));
			}

			$data = array();
			$record = array();

			foreach($query_result as $key => $value) {
				$data['data'][] = array(
					'lineup_applicant_id' 	=> $value->lineup_applicant_id,
					'item_code' 			=> $value->item_code,
					'item_desc' 			=> $value->item_desc,
					'item_desc_detail' 		=> $value->item_desc_detail,
					'posgrade' 				=> $value->posgrade,
					'depcode' 				=> $value->depcode,
					'psb_status' 			=> $value->psb_status,
					'date_lineup' 			=> is_null($value->date_lineup) ? '': date('M j, Y', strtotime($value->date_lineup)),
					'date_hr_test' 			=> is_null($value->date_hr_test) ? '': date('M j, Y', strtotime($value->date_hr_test)),
					'remarks_hr_test' 		=> $value->remarks_hr_test,
					'date_interview' 		=> is_null($value->date_interview) ? '': date('M j, Y', strtotime($value->date_interview)),
					'remarks_interview' 	=> $value->remarks_interview,
					'is_done_bi' 			=> $value->is_done_bi == 1 ? 'Done': '',
					'is_done_paf' 			=> $value->is_done_paf == 1 ? 'Done': '',
					'is_done_nir' 			=> $value->is_done_nir == 1 ? 'Done': '',
					'date_psb' 				=> is_null($value->date_psb) ? '': date('M j, Y', strtotime($value->date_psb)),
					'psb_result' 			=> $value->psb_result
				);
			}

			$data['totalCount'] = $query_count[0]->count;
			die(json_encode($data));
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

			$id				= $this->input->post('id');
			$fname			= $this->esc_str(strtoupper($this->input->post('fname')));
			$mname 			= strtoupper($this->input->post('mname'));
			$mname			= isset($mname) && $mname != "" ? $this->esc_str(strtoupper($mname)) : null;
			$lname			= $this->esc_str(strtoupper($this->input->post('lname')));
			$suffix 		= $this->esc_str(strtoupper($this->input->post('suffix')));
			$suffix		 	= isset($suffix) && $suffix != "" ? $suffix : null;
			$phone_no	 	= $this->esc_str($this->input->post('phone_no'));
			$email_add 		= $this->esc_str($this->input->post('email_add'));
			$email_add		= isset($email_add) && $email_add != "" ? $email_add : null;
			$type 			= $this->input->post('type');
			$mname_update_query = " mname = '$mname'";
			$suffix_update_query = " suffix = '$suffix'";
			$suffix_select_query = " suffix LIKE '%$suffix%'";
			$email_add_update_query = " email_add = '$email_add'";

			if(is_null($mname)) 	$mname_update_query = " mname = NULL";
			if(is_null($suffix)) 	{$suffix_update_query = " suffix = NULL"; $suffix_select_query = " suffix IS NULL";}
			if(is_null($email_add)) $email_add_update_query = " email_add = NULL";

			$this->load->model('Access'); $this->Access->rights($this->modulename('link'), $type, null);

			if($type == "Delete") {
				$commandText = "UPDATE applicants SET active = 0 WHERE id = $id";
				$result = $this->db->query($commandText);

				$this->load->model('Logs'); $this->Logs->audit_logs($id, 'applicants', $type, $this->modulename('label'));
			}
			else {
				// both for Add and Edit
				$commandText = "SELECT * FROM applicants WHERE fname = '$fname' AND lname = '$lname' AND $suffix_select_query AND id <> $id AND active = 1";
				$result = $this->db->query($commandText);
				$query_result = $result->result();

				if(count($query_result) > 0) {
					$data = array("success"=> false, "data"=>"Applicant already exists!");
					die(json_encode($data));
				}

				$commandText = "UPDATE applicants SET fname = '$fname', $mname_update_query, lname = '$lname', $suffix_update_query, phone_no = '$phone_no', $email_add_update_query, active = 1 WHERE id = $id";
				$result = $this->db->query($commandText);

				$this->load->model('Logs'); $this->Logs->audit_logs($id, 'applicants', $type, $this->modulename('label'));
			}
			
			$arr = array();  
			$arr['success'] = true;
			if ($type == "Add" || $type == "Edit")
				$arr['data'] = "Successfully Saved";
			if ($type == "Delete")
				$arr['data'] = "Successfully Deleted";
			die(json_encode($arr));
		}
		catch(Exception $e) {
			$data = array("success"=> false, "data"=>$e->getMessage());
			die(json_encode($data));
		}
	}

	public function details_list() {
		try {
			$applicant_id = $_GET['applicantID'];
			$detail_type = $_GET['detailType'];
			$query = $this->esc_str($_GET['query']);
			die(json_encode($this->generatedetails_list($applicant_id, $detail_type, $query, 'Grid')));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();	
		}
	}

	public function generatedetails_list($applicant_id, $detail_type, $query, $transaction_type) {
		try {
			$this->load->library('session');

			$limitQuery = "";
			if($transaction_type == 'Grid') {
				$limit = $_GET['limit'];
				$start = $_GET['start'];
				$limitQuery = " LIMIT $start, $limit";
			}

			if($detail_type == "Education") {
				$commandText = "SELECT a.*,
									c.description AS educ_level_desc
								FROM applicants_education a
									LEFT JOIN applicants b ON b.id = a.applicant_id
									LEFT JOIN education_levels c ON c.level = a.educ_level
								WHERE (
										a.school LIKE '%$query%'
										OR a.course LIKE '%$query%'
										OR a.acad_honor LIKE '%$query%'
									) AND a.applicant_id = $applicant_id
								ORDER BY educ_level DESC";
				$countCommandText = "SELECT COUNT(*) AS count
									FROM applicants_education a
										LEFT JOIN applicants b ON b.id = a.applicant_id
										LEFT JOIN education_levels c ON c.level = a.educ_level
									WHERE (
											a.school LIKE '%$query%'
											OR a.course LIKE '%$query%'
											OR a.acad_honor LIKE '%$query%'
										) AND a.applicant_id = $applicant_id";
			}
			else if($detail_type == "Eligibility") {
				$commandText = "SELECT a.*
								FROM applicants_eligibility a
									LEFT JOIN applicants b ON b.id = a.applicant_id
								WHERE (
										a.title LIKE '%$query%'
										OR a.rating LIKE '%$query%'
										OR a.exam_date LIKE '%$query%'
										OR a.exam_place LIKE '%$query%'
										OR a.license_no LIKE '%$query%'
										OR a.date_validity LIKE '%$query%'
									) AND a.applicant_id = $applicant_id
								ORDER BY id ASC";
				$countCommandText = "SELECT COUNT(*) AS count
									FROM applicants_eligibility a
										LEFT JOIN applicants b ON b.id = a.applicant_id
									WHERE (
											a.title LIKE '%$query%'
											OR a.rating LIKE '%$query%'
											OR a.exam_date LIKE '%$query%'
											OR a.exam_place LIKE '%$query%'
											OR a.license_no LIKE '%$query%'
											OR a.date_validity LIKE '%$query%'
										) AND a.applicant_id = $applicant_id";
			}
			else if($detail_type == "Experience") {
				$commandText = "SELECT a.*,
									c.description AS employment_status_desc
								FROM applicants_experience a
									LEFT JOIN applicants b ON b.id = a.applicant_id
									LEFT JOIN employment_statuses c ON c.id = a.employment_status_id
								WHERE (
										a.position LIKE '%$query%'
										OR a.agency_company LIKE '%$query%'
									) AND a.applicant_id = $applicant_id
								ORDER BY a.from_date DESC";
				$countCommandText = "SELECT COUNT(*) AS count
									FROM applicants_experience a
										LEFT JOIN applicants b ON b.id = a.applicant_id
										LEFT JOIN employment_statuses c ON c.id = a.employment_status_id
									WHERE (
											a.position LIKE '%$query%'
											OR a.agency_company LIKE '%$query%'
										) AND a.applicant_id = $applicant_id";
			}
			else if($detail_type == "Training") {
				$commandText = "SELECT a.*,
									c.description AS training_type_desc
								FROM applicants_training a
									LEFT JOIN applicants b ON b.id = a.applicant_id
									LEFT JOIN training_types c ON c.id = a.training_type_id
								WHERE (
										a.title LIKE '%$query%'
										OR a.conducted_by LIKE '%$query%'
										OR c.description LIKE '%$query%'
									) AND a.applicant_id = $applicant_id
								ORDER BY a.from_date DESC";
				$countCommandText = "SELECT COUNT(*) AS count
									FROM applicants_training a
										LEFT JOIN applicants b ON b.id = a.applicant_id
										LEFT JOIN training_types c ON c.id = a.training_type_id
									WHERE (
											a.title LIKE '%$query%'
											OR a.conducted_by LIKE '%$query%'
											OR c.description LIKE '%$query%'
										) AND a.applicant_id = $applicant_id";
			}
			$result = $this->db->query($commandText);
			$query_result = $result->result();
			$count_result = $this->db->query($countCommandText);
			$query_count = $count_result->result();

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
				if($detail_type == "Education") {
					$data['data'][] = array(
						'id' 				=> $value->id,
						'educ_level'		=> $value->educ_level,
						'educ_level_desc' 	=> $value->educ_level_desc,
						'school' 			=> $value->school,
						'course'			=> $value->course,
						'from_year' 		=> is_null($value->from_year) ? '': $value->from_year,
						'to_year'			=> is_null($value->to_year) ? '': $value->to_year,
						'units_earned' 		=> is_null($value->units_earned) ? '': $value->units_earned,
						'year_grad'			=> is_null($value->year_grad) ? '': $value->year_grad,
						'acad_honor' 		=> is_null($value->acad_honor) ? '': $value->acad_honor
					);
				}
				else if($detail_type == "Eligibility") {
					$data['data'][] = array(
						'id' 				=> $value->id,
						'title'				=> $value->title,
						'rating' 			=> is_null($value->rating) ? '': $value->rating,
						'exam_date'			=> is_null($value->exam_date) ? '': $value->exam_date,
						'exam_place' 		=> is_null($value->exam_place) ? '': $value->exam_place,
						'license_no'		=> is_null($value->license_no) ? '': $value->license_no,
						'date_validity' 	=> is_null($value->date_validity) ? '': $value->date_validity
					);
				}
				else if($detail_type == "Experience") {
					$data['data'][] = array(
						'id' 				 	 	=> $value->id,
						'employment_status_id' 	 	=> is_null($value->employment_status_id) ? '': $value->employment_status_id,
						'employment_status_desc'  	=> is_null($value->employment_status_desc) ? '': $value->employment_status_desc,
						// 'from_date'				 => $value->from_date,
						// 'to_date' 				 => is_null($value->to_date) ? 'Present': $value->to_date,
						'from_date'				 	=> is_null($value->from_date) ? '': $value->from_date, 	// replace with above after initial import
						'to_date' 				 	=> is_null($value->to_date) ? '': $value->to_date, // replace with above after initial import
						'position'					=> $value->position,
						'agency_company' 			=> $value->agency_company,
						'monthly_salary'			=> is_null($value->monthly_salary) ? '': $value->monthly_salary,
						'salary_grade' 				=> is_null($value->salary_grade) ? '': $value->salary_grade,
						'government_service'		=> is_null($value->government_service) ? '': $value->government_service
					);
				}
				else if($detail_type == "Training") {
					$data['data'][] = array(
						'id' 				 	=> $value->id,
						'training_type_id' 	 	=> is_null($value->training_type_id) ? '': $value->training_type_id,
						'training_type_desc'	=> is_null($value->training_type_desc) ? '': $value->training_type_desc,
						'title'					=> $value->title,
						'duration' 				=> number_format($value->duration, 1, '.', ''),
						'from_date'				=> is_null($value->from_date) ? '': $value->from_date,
						'to_date' 				=> is_null($value->to_date) ? '': $value->to_date,
						'conducted_by' 			=> is_null($value->conducted_by) ? '': $value->conducted_by,
					);
				}
			}

			$data['totalCount'] = $query_count[0]->count;
			return $data;
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();	
		}
	}

	public function detailview() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();
			
			$id = $this->input->post('id');
			$detail_type = $this->input->post('detailType');

			if($detail_type == "Education") {
				$commandText = "SELECT a.*,
									b.description AS educ_level_desc
								FROM applicants_education a
									LEFT JOIN education_levels b ON b.level = a.educ_level
								WHERE a.id = $id";
			}
			else if($detail_type == "Eligibility") {
				$commandText = "SELECT a.*
								FROM applicants_eligibility a
								WHERE a.id = $id";
			}
			else if($detail_type == "Experience") {
				$commandText = "SELECT a.*,
									b.description AS employment_status_desc
								FROM applicants_experience a
									LEFT JOIN employment_statuses b ON b.id = a.employment_status_id
								WHERE a.id = $id";
			}
			else if($detail_type == "Training") {
				$commandText = "SELECT a.*,
									b.description AS training_type_desc
								FROM applicants_training a
									LEFT JOIN training_types b ON b.id = a.training_type_id
								WHERE a.id = $id";
			}

			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$data = array();
			$record = array();

			if($detail_type == "Education") {
				foreach($query_result as $key => $value) {	
					$record['id'] 					= $value->id;
					$record['educ_level']			= $value->educ_level;
					$record['educ_level_desc']		= $value->educ_level_desc;
					$record['school']				= $value->school;
					$record['course']				= $value->course;
					$record['from_year']			= $value->from_year;
					$record['to_year']				= $value->to_year;
					$record['units_earned']			= $value->units_earned;
					$record['year_grad']			= $value->year_grad;
					$record['acad_honor']			= $value->acad_honor;
				}
			}
			else if($detail_type == "Eligibility") {
				foreach($query_result as $key => $value) {	
					$record['id'] 					= $value->id;
					$record['title']				= $value->title;
					$record['rating']				= $value->rating;
					$record['exam_date']			= $value->exam_date;
					$record['exam_place']			= $value->exam_place;
					$record['license_no']			= $value->license_no;
					$record['date_validity']		= $value->date_validity;
				}
			}
			else if($detail_type == "Experience") {
				foreach($query_result as $key => $value) {	
					$record['id'] 						= $value->id;
					$record['employment_status_id'] 	= $value->employment_status_id;
					$record['employment_status_desc'] 	= $value->employment_status_desc;
					$record['from_date']				= $value->from_date;
					$record['to_date']					= $value->to_date;
					$record['position']					= $value->position;
					$record['agency_company']			= $value->agency_company;
					$record['monthly_salary']			= $value->monthly_salary;
					$record['salary_grade']				= $value->salary_grade;
					$record['government_service']		= $value->government_service;
				}
			}
			else if($detail_type == "Training") {
				foreach($query_result as $key => $value) {	
					$record['id'] 					= $value->id;
					$record['training_type_id'] 	= $value->training_type_id;
					$record['training_type_desc'] 	= $value->training_type_desc;
					$record['title']				= $value->title;
					$record['duration']				= $value->duration;
					$record['from_date']			= $value->from_date;
					$record['to_date']				= $value->to_date;
					$record['conducted_by']			= $value->conducted_by;
				}
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

	public function detailscrud() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();

			$id				= $this->input->post('id');
			$applicant_id 	= $this->input->post('applicantID');
			$crud_type 		= $this->input->post('crudType');
			$detail_type 	= $this->input->post('detailType');
			$this->load->model('Access'); $this->Access->rights($this->modulename('link'), $crud_type, null);

			if($crud_type == "Delete") {
				if($detail_type == "Education") $commandText = "DELETE FROM applicants_education WHERE id = $id";
				else if($detail_type == "Eligibility") $commandText = "DELETE FROM applicants_eligibility WHERE id = $id";
				else if($detail_type == "Experience") $commandText = "DELETE FROM applicants_experience WHERE id = $id";
				else if($detail_type == "Training") $commandText = "DELETE FROM applicants_training WHERE id = $id";
				$result = $this->db->query($commandText);
			}
			else {
				if($crud_type == "Add") {
					// no more checking of existing Education, Eligibility, and Experience
					if($detail_type == "Education") $this->load->model('applicants_education');
					else if($detail_type == "Eligibility") $this->load->model('applicants_eligibility');
					else if($detail_type == "Experience") $this->load->model('applicants_experience');
					else if($detail_type == "Training") $this->load->model('applicants_training');
					$id = 0;
				}
				else if($crud_type == "Edit") {
					// no more checking of existing Education, Eligibility, and Experience
					if($detail_type == "Education") {$this->load->model('applicants_education'); $this->applicants_education->id = $id;}
					else if($detail_type == "Eligibility") {$this->load->model('applicants_eligibility'); $this->applicants_eligibility->id = $id;}
					else if($detail_type == "Experience") {$this->load->model('applicants_experience'); $this->applicants_experience->id = $id;}
					else if($detail_type == "Training") {$this->load->model('applicants_training'); $this->applicants_training->id = $id;}
				}

				if($detail_type == "Education") {
					$from_year = $this->input->post('from_year'); $from_year = isset($from_year) && $from_year != "" ? $from_year : null; 
					$to_year = $this->input->post('to_year'); $to_year = isset($to_year) && $to_year != "" ? $to_year : null; 
					$units_earned = $this->input->post('units_earned'); $units_earned = isset($units_earned) && $units_earned != "" ? $units_earned : null; 
					$year_grad = $this->input->post('year_grad'); $year_grad = isset($year_grad) && $year_grad != "" ? $year_grad : null; 
					$acad_honor = $this->esc_str($this->input->post('acad_honor')); $acad_honor = isset($acad_honor) && $acad_honor != "" ? $acad_honor : null; 

					$this->applicants_education->applicant_id  		= $applicant_id;
					$this->applicants_education->educ_level 		= $this->input->post('educ_level_id');
					$this->applicants_education->school 			= $this->esc_str($this->input->post('school'));
					$this->applicants_education->course 			= $this->esc_str($this->input->post('course'));
					$this->applicants_education->from_year 			= $from_year;
					$this->applicants_education->to_year 			= $to_year;
					$this->applicants_education->units_earned 		= $units_earned;
					$this->applicants_education->year_grad 			= $year_grad;
					$this->applicants_education->acad_honor 		= $acad_honor;
					$this->applicants_education->save($id);

					$this->load->model('Logs'); $this->Logs->audit_logs($id, 'applicants_education', $crud_type, $this->modulename('label'));
				}
				else if($detail_type == "Eligibility") {
					$rating = $this->input->post('rating'); $rating = isset($rating) && $rating != "" ? $rating : null;
					$exam_date = $this->input->post('exam_date');
					$exam_date = isset($exam_date) && $exam_date != "" ? date('Y-m-d', strtotime($this->input->post('exam_date'))) : null;
					$exam_place = $this->esc_str($this->input->post('exam_place')); $exam_place = isset($exam_place) && $exam_place != "" ? $exam_place : null;
					$license_no = $this->esc_str($this->input->post('license_no')); $license_no = isset($license_no) && $license_no != "" ? $license_no : null;
					$date_validity = $this->input->post('date_validity'); $date_validity = isset($date_validity) && $date_validity != "" ? date('Y-m-d', strtotime($this->input->post('date_validity'))) : null;
					

					$this->applicants_eligibility->applicant_id  	= $applicant_id;
					$this->applicants_eligibility->title 			= $this->esc_str($this->input->post('title'));
					$this->applicants_eligibility->rating 			= $rating;
					$this->applicants_eligibility->exam_date 		= $exam_date;
					$this->applicants_eligibility->exam_place 		= $exam_place;
					$this->applicants_eligibility->license_no 		= $license_no;
					$this->applicants_eligibility->date_validity 	= $date_validity;
					$this->applicants_eligibility->save($id);

					$this->load->model('Logs'); $this->Logs->audit_logs($id, 'applicants_eligibility', $crud_type, $this->modulename('label'));
				}
				else if($detail_type == "Experience") {
					$from_date = $this->input->post('from_date'); // remove after initial import
					$from_date = isset($from_date) && $from_date != "" ? date('Y-m-d', strtotime($this->input->post('from_date'))) : null; // remove after initial import
					$to_date = $this->input->post('to_date');
					$to_date = isset($to_date) && $to_date != "" ? date('Y-m-d', strtotime($this->input->post('to_date'))) : null;
					$monthly_salary = $this->input->post('monthly_salary'); $monthly_salary = isset($monthly_salary) && $monthly_salary != "" ? $monthly_salary : null;
					$salary_grade = $this->input->post('salary_grade'); $salary_grade = isset($salary_grade) && $salary_grade != "" ? $salary_grade : null;
					$employment_status_id = $this->input->post('employment_status_id'); $employment_status_id = isset($employment_status_id) && $employment_status_id != "" ? $employment_status_id : null;
					$government_service = $this->input->post('government_service'); $government_service = isset($government_service) && $government_service != "" ? $government_service : null;

					$this->applicants_experience->applicant_id  		= $applicant_id;
					$this->applicants_experience->employment_status_id 	= $employment_status_id;
					// $this->applicants_experience->from_date 			= date('Y-m-d', strtotime($this->input->post('from_date')));
					$this->applicants_experience->from_date 			= $from_date; // replace with above after initial import
					$this->applicants_experience->to_date 				= $to_date;
					$this->applicants_experience->position 				= $this->esc_str($this->input->post('position'));
					$this->applicants_experience->agency_company 		= $this->esc_str($this->input->post('agency_company'));
					$this->applicants_experience->monthly_salary 		= $monthly_salary;
					$this->applicants_experience->salary_grade 			= $salary_grade;
					$this->applicants_experience->government_service 	= $government_service;
					$this->applicants_experience->save($id);

					$this->load->model('Logs'); $this->Logs->audit_logs($id, 'applicants_experience', $crud_type, $this->modulename('label'));
				}
				else if($detail_type == "Training") {
					$training_type_id = $this->input->post('training_type_id'); $training_type_id = isset($training_type_id) && $training_type_id != "" ? $training_type_id : null;
					$from_date = $this->input->post('from_date');
					$from_date = isset($from_date) && $from_date != "" ? date('Y-m-d', strtotime($this->input->post('from_date'))) : null;
					$to_date = $this->input->post('to_date');
					$to_date = isset($to_date) && $to_date != "" ? date('Y-m-d', strtotime($this->input->post('to_date'))) : null;

					$this->applicants_training->applicant_id  		= $applicant_id;
					$this->applicants_training->training_type_id 	= $training_type_id;
					$this->applicants_training->title 				= $this->esc_str($this->input->post('title'));
					$this->applicants_training->duration 			= $this->esc_str($this->input->post('duration'));
					$this->applicants_training->from_date 			= $from_date;
					$this->applicants_training->to_date 			= $to_date;
					$this->applicants_training->conducted_by 		= $this->esc_str($this->input->post('conducted_by'));
					$this->applicants_training->save($id);
				}
			}

			$arr = array();  
			$arr['success'] = true;
			if($crud_type == "Add") 
				$arr['data'] = "Successfully Created";
			if($crud_type == "Edit")
				$arr['data'] = "Successfully Updated";
			if($crud_type == "Delete")
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
			
			$id = $this->input->post('id');

			$commandText = "SELECT 
								id,
								fname,
								mname,
								lname,
								suffix,
								phone_no,
								email_add
							FROM applicants
							WHERE id = $id AND active = 1";

			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$data = array();
			$record = array();

			foreach($query_result as $key => $value) {	
				$record['id'] 					= $value->id;
				$record['fname']				= $value->fname;
				$record['mname']				= $value->mname;
				$record['lname']				= $value->lname;
				$record['suffix']				= $value->suffix;
				$record['phone_no']				= $value->phone_no;
				$record['email_add']			= $value->email_add;
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

	public function initialcrud() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();

			$id 		= $this->input->post('id');
			$type 		= $this->input->post('type');

			if($type == "Add") {
				$this->load->model('applicants');
				$this->applicants->active 			= 2;
				$this->applicants->save(0);

				$id = $this->applicants->id;

				$commandText = "DELETE FROM applicants WHERE active = 2 AND id <> $id";
				$result = $this->db->query($commandText);
			}

			$data = array();
			$data['id'] = $id;
			$data['success'] = true;
			die(json_encode($data));
		}
		catch(Exception $e) {
			$data = array("success"=> false, "data"=>$e->getMessage());
			die(json_encode($data));
		}
	}

	public function applicationview() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();

			$applicant_id = $this->input->post('applicant_id');

			$commandText = "SELECT * FROM applicants_applications WHERE applicant_id = $applicant_id AND active = 1";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$data = array();
			$record = array();

			if(count($query_result) == 0) $record['id'] = 0;

			foreach($query_result as $key => $value) {
				$record['id'] 							= $value->id;
				$record['applicant_id'] 				= $value->applicant_id;
				$record['date_application_received'] 	= date('m/d/Y', strtotime($value->date_application_received));
				$record['position_applied'] 			= $value->position_applied;
				$record['notes'] 						= $value->notes;
				$record['applic_type'] 					= $value->applic_type;
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

	public function applicationcrud() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();

			$id 						= $this->input->post("id");
			$applicant_id 				= $this->input->post("applicant_id");
			$date_application_received 	= date('Y-m-d', strtotime($this->input->post('date_application_received')));
			$position_applied 			= $this->esc_str($this->input->post("position_applied"));
			$notes 						= $this->esc_str($this->input->post("notes"));
			$applic_type 				= $this->esc_str($this->input->post("applic_type_desc"));

			$this->load->model('Access'); $this->Access->rights($this->modulename('link'), 'Add', null); // replaced $type with "Add"

			$this->load->model('applicants_applications');
			if($id != 0)
				$this->applicants_applications->id 						= $id;
			$this->applicants_applications->applicant_id 				= $applicant_id;
			$this->applicants_applications->date_application_received 	= $date_application_received;
			$this->applicants_applications->position_applied 			= $position_applied;
			$this->applicants_applications->notes 						= $notes;
			$this->applicants_applications->applic_type 				= $applic_type;
			$this->applicants_applications->active 						= 1;
			$this->applicants_applications->save($id);

			$arr = array("success"=>true, "data"=>"Successfully Updated");
			die(json_encode($arr));
		}
		catch(Exception $e) {
			$data = array("success"=> false, "data"=>$e->getMessage());
			die(json_encode($data));
		}
	}

	public function import_excel() {
		$file = $this->input->post('');
	}

	private function esc_str($x) {
		return mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($x)));
	}
}