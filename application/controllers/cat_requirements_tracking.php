<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cat_Requirements_Tracking extends CI_Controller {
	/**
	*/ 
	private function modulename($type) {		
		if($type == 'link')
			return 'cat_requirements_tracking';
		else 
			return 'Requirements Tracking';
	} 

	public function index() {
		$this->load->model('Page');		
        $this->Page->set_page($this->modulename('link'));
	}

	public function incoming_records_list() {
		try { 
			$query = mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($_GET['query'])));
			die(json_encode($this->generateincoming_records_list($_GET['record_type_filter'], $_GET['priority'], $query, $_GET['status'], 'Grid')));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();	
		}
	}
 	
	public function generateincoming_records_list($record_type_id, $priority, $query, $status, $transaction_type) {
		try {
			$this->load->library('session');
			$this->load->library('callable_functions');
			$this->load->helper('text');

			$user_id 				= $this->session->userdata('user_id');
			$user_division_id 		= $this->session->userdata('division_id');

			$limitQuery = "";
			if($transaction_type == 'Grid') {
				$limit = $_GET['limit'];
				$start = $_GET['start'];
				$limitQuery = " LIMIT $start, $limit";
			}

			// status filter
			if($status == 1) $status = " AND a.status LIKE '%Assigning%'";
			else if($status == 2) $status = " AND a.status LIKE '%Acknowledgement%'";
			else if($status == 3) $status = " AND a.status LIKE '%Action Taken%'";
			else if($status == 4) $status = " AND (a.status LIKE '%Closed%' OR a.status LIKE '%Archived%')";
			else $status = "";

			// per division filter
			$filter = " AND a.division_id = $user_division_id";

			// record type filter
			if($record_type_id == 0) $record_type_filter = "";
			else $record_type_filter = " AND a.record_type_id = $record_type_id";

			// priority filter
			$priority_filter = "AND a.priority = $priority";
			if($priority == 0) $priority_filter = "";
			if($priority == 2) $priority_filter = "AND (a.priority = $priority OR a.priority IS NULL)";
			else if($priority == NULL) $priority_filter = "AND (a.priority = 2 OR a.priority IS NULL)";
			

			$commandText = "SELECT 
								a.fname,
								b.description AS division_desc,
								c.description AS section_desc,
								d.description AS position_desc
							FROM staff a
								LEFT JOIN divisions b ON a.division_id = b.id
								LEFT JOIN sections c ON a.section_id = c.id
								LEFT JOIN positions d ON a.position_id = d.id
							WHERE a.id = $user_id";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$data['department_head'] = false;
			$data['division_assigned'] = false;
			$data['is_admin_division_head'] = false;

			//special case, if maam alelie same function as sir jerric
			$fname = $query_result[0]->fname;
			if ($fname == "John Doe") {
				$data['division_assigned'] = false;	
				$filter = "";
			}
				
			$commandText = "SELECT 
								a.id,
								a.sequence_number,
								a.communication_number,
								a.date_communication,
								b.description AS record_type,
								a.date_logged,
								a.subject,
								c.description AS from_name,
								d.description AS to_name,
								IF(a.priority = 3, 'Urgent', 
									IF(a.priority = 2 OR a.priority IS NULL, 'Normal', 'Low')) AS priority, 
								a.status,
								IF(a.status = 'Pending Division Assigning', '<font color=red><b>PENDING DIVISION ASSIGNING</b></font>',
									IF(a.status = 'Pending Acknowledgement', '<font color=red><b>PENDING ACKNOWLEDGEMENT</b></font>',
										IF(a.status = 'Pending Action Taken', '<font color=red><b>PENDING ACTION TAKEN</b></font>', 
											IF(a.status = 'Archived', '<font color=green><b>ARCHIVED</b></font>', '<font color=green><b>CLOSED</b></font>')))) AS status_style,
								e.description AS division_description,
								e.div_code AS division_code,
								a.side_notes,
								f.action_taken,
								#IF(f.date_action_taken IS NULL, '', DATE_FORMAT(f.date_action_taken, '%m/%d/%Y')) AS date_action_taken,
								IF(f.date_action_taken IS NULL, '', DATE_FORMAT(f.date_action_taken, '%e %b %Y')) AS date_action_taken,
								(TIMESTAMPDIFF(DAY, a.date_logged, f.date_action_taken)) AS duration_action_taken
							FROM adminservices_records_header a
								LEFT JOIN record_types b ON a.record_type_id = b.id
								LEFT JOIN adminservices_records_from_to c ON a.from_id = c.id
								LEFT JOIN adminservices_records_from_to d ON a.to_id = d.id
								LEFT JOIN divisions e ON a.division_id = e.id
								LEFT JOIN adminservices_records_actions_taken f ON a.action_taken_id = f.id
							WHERE (
									b.description LIKE '%$query%'
									OR a.subject LIKE '%$query%'
									OR c.description LIKE '%$query%'
									OR d.description LIKE '%$query%'
									OR e.div_code LIKE '%$query%'
									OR CONCAT(DATE_FORMAT(CURDATE(), '%y'), '-', LPAD(a.sequence_number, 4, '0')) LIKE '%$query%'
									OR f.action_taken LIKE '%$query%'
								)
								$filter
								$status
								$record_type_filter
								$priority_filter
								AND a.communication_type = 'Incoming'
								AND a.active = 1
							ORDER BY a.priority DESC, a.status DESC, a.date_communication DESC
							$limitQuery";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$commandText = "SELECT count(*) as count
							FROM adminservices_records_header a
								LEFT JOIN record_types b ON a.record_type_id = b.id
								LEFT JOIN adminservices_records_from_to c ON a.from_id = c.id
								LEFT JOIN adminservices_records_from_to d ON a.to_id = d.id
								LEFT JOIN divisions e ON a.division_id = e.id
								LEFT JOIN adminservices_records_actions_taken f ON a.action_taken_id = f.id
							WHERE (
									b.description LIKE '%$query%'
									OR a.subject LIKE '%$query%'
									OR c.description LIKE '%$query%'
									OR d.description LIKE '%$query%'
									OR e.div_code LIKE '%$query%'
									OR CONCAT(DATE_FORMAT(CURDATE(), '%y'), '-', LPAD(a.sequence_number, 4, '0')) LIKE '%$query%'
									OR f.action_taken LIKE '%$query%'
								)
								$filter
								$status
								$record_type_filter
								$priority_filter
								AND a.communication_type = 'Incoming'
								AND a.active = 1";
			$result = $this->db->query($commandText);
			$query_count = $result->result();	//LPAD is used to format the sequence number to 3 digits, e.g. '57'->'057'

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
				$control_number = $this->callable_functions->GenerateControlNumber($value->date_communication, $value->date_logged, $value->sequence_number);

				$commandText = "SELECT * FROM adminservices_records_attachments WHERE record_id = $value->id AND active = 1";
				$result = $this->db->query($commandText);
				$attachments_result = $result->result();

				if(count($attachments_result) == 0) 
					$date_uploaded = '';

				//attachments information builder
				$i = 0;
				$attachment_full_names = '';
				$attachment_links = '<ul style="list-style-type:square; padding-left:16px; margin:0"><li>';
				$attachment_descriptions = '<ul style="list-style-type:square; padding-left:16px; margin:0"><li>';

				foreach($attachments_result as $key => $val) {
					if($i > 0) {
						$attachment_full_names .= ', <br>';
						$attachment_links .= '<li>';
						$attachment_descriptions .=  '<li>';
					}

					$attachment_full_name = $val->attachment_name.".".$val->attachment_extension;
					$attachment_full_names .= $attachment_full_name;
					$attachment_path = '../documents/Incoming and Outgoing Communications/'.$attachment_full_name;
					$attachment_links .= '<a href="'.$attachment_path.'" target=_blank>'.$attachment_full_name.'</a></li>';
					$attachment_descriptions .= $val->description.'</li>';
					$date_uploaded = date('j M Y', strtotime($val->date_uploaded));		//only get the latest date uploaded
					$i++;
				}
				$attachment_links .= '<ul>';

				$details = $this->callable_functions->CommunicationDetailsBuilder($value->record_type, $value->communication_number, $value->subject);
				
				//details builder
				// $details = '<b>' . mb_strtoupper($value->record_type) . ':</b><br>' . $value->subject;
				// if ($value->record_type == 'Directive' || $value->record_type == 'Memo' || $value->record_type == 'Ordinance')
				// 	$details = '<b>' . mb_strtoupper($value->record_type) . '#' . $value->communication_number . ':</b><br>' . $value->subject;
				// else if ($value->record_type == 'Endorsement')
				// 	$details = '<b>' . $value->communication_number . ' ' . mb_strtoupper($value->record_type) . ':</b><br>' . $value->subject;

				$data['data'][] = array(
					'id' 						=> $value->id,
					'record_type'				=> $value->record_type,
					'control_number' 			=> $control_number,
					//'date_communication'		=> date('m/d/Y', strtotime($value->date_communication)),
					'date_communication'		=> date('j M Y', strtotime($value->date_communication)),
					//'date_logged'				=> date('m/d/Y - h:i A', strtotime($value->date_logged)),
					'date_logged'				=> date('j M Y - h:i A', strtotime($value->date_logged)),
					'subject' 					=> stripslashes($details),
					'from_name' 				=> $value->from_name,
					'to_name' 					=> $value->to_name,
					'priority'					=> $value->priority,
					'status'					=> $value->status,
					'status_style'				=> $value->status_style,
					'division_description'		=> $value->division_description,
					'division_code'				=> $value->division_code,
					'side_notes'				=> mb_strtoupper($value->side_notes),
					'action_taken'				=> mb_strtoupper($value->action_taken),
					'date_action_taken'			=> $value->date_action_taken,
					'duration_action_taken'		=> $value->duration_action_taken,
					'attachment_full_names' 	=> $attachment_full_names,
					'attachment_links'			=> $attachment_links,
					'date_uploaded'				=> $date_uploaded,
					'attachment_descriptions'	=> $attachment_descriptions
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
			$this->load->library('callable_functions');

			#header details
			$commandText = "SELECT 
								a.date_communication,
								a.date_logged,
								a.sequence_number,
								a.communication_number,
								a.subject,
								b.description AS record_type,
								c.description AS from_name,
								d.description AS to_name
							FROM adminservices_records_header a
								LEFT JOIN record_types b ON a.record_type_id = b.id
								LEFT JOIN adminservices_records_from_to c ON a.from_id = c.id
								LEFT JOIN adminservices_records_from_to d ON a.to_id = d.id
							WHERE a.id = $id AND a.active = 1 AND a.communication_type = 'Incoming'";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			foreach($query_result as $key => $val) {
				$control_number = $this->callable_functions->GenerateControlNumber($val->date_communication, $val->date_logged, $val->sequence_number);
				$details = $this->callable_functions->CommunicationDetailsBuilder($val->record_type, $val->communication_number, $val->subject);

				$data['header_details'][] = array(
					'control_number'		=> $control_number,
					'date_communication' 	=> date('m/d/Y', strtotime($val->date_communication)),
					'details' 				=> stripslashes($details),
					'from_name' 			=> $val->from_name,
					'to_name' 				=> $val->to_name
				);
			}

			#tracking details
			$commandText = "SELECT 
								a.date_logged,
								a.status,
								IF(b.description IS NULL, '', b.description) AS assigned_division_name,
								a.side_notes,
								IF(c.action_taken IS NULL, '', c.action_taken) AS action_taken,
								IF(c.date_action_taken IS NULL, '', DATE_FORMAT(c.date_action_taken, '%m/%d/%Y')) AS date_action_taken,
								IF((TIMESTAMPDIFF(DAY, a.date_logged, c.date_action_taken)) IS NULL, '', (TIMESTAMPDIFF(DAY, a.date_logged, c.date_action_taken))) AS duration_action_taken
							FROM adminservices_records_header a
								LEFT JOIN divisions b ON a.division_id = b.id
								LEFT JOIN adminservices_records_actions_taken c ON a.action_taken_id = c.id
							WHERE a.id = $id AND a.active = 1";
			$result = $this->db->query($commandText);
			$tracking_result = $result->result();

			foreach($tracking_result as $key => $val) {
				$data['tracking_details'][] = array(
					'date_logged'				=> date('m/d/Y - h:i A', strtotime($val->date_logged)),
					'status'					=> $val->status,
					'assigned_division_name'	=> $val->assigned_division_name,
					'side_notes'				=> mb_strtoupper($val->side_notes),
					'action_taken'				=> $val->action_taken,
					'action_taken_date'			=> $val->date_action_taken,
					'duration_action_taken'		=> $val->duration_action_taken
				);
			}

			#filed copy details
			$commandText = "SELECT * 
							FROM adminservices_records_attachments 
							WHERE record_id = $id 
								AND active = 1";
			$result = $this->db->query($commandText);
			$attachments_result = $result->result();

			foreach($attachments_result as $key => $val) {
				$attachment_full_name = $val->attachment_name.".".$val->attachment_extension;
				$attachment_path = getenv('DOCUMENTS_DIR').'Incoming and Outgoing Communications/'.$attachment_full_name;

				$data['attachments'][] = array(
					'attachment_name' 		=> $val->attachment_name,
					'attachment_full_name' 	=> $attachment_full_name,
					'attachment_path'		=> $attachment_path,
					'date_uploaded'			=> $val->date_uploaded,
					'description'			=> $val->description
				);
			}

			$data["success"] = true;			
			$data["accounts_count"] = count($query_result);
			$data["history_count"] = count($tracking_result);
			$data["attachments_count"] = count($attachments_result);

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
					//$commandText = "SELECT * FROM adminservices_records_header WHERE id <> $id AND active = 1 AND (sequence_number LIKE '%$sequence_number%' AND YEAR(date_communication) = '%date('Y', $date_communication)%') OR subject LIKE '%$subject%')  AND communication_type = 'Incoming'";
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
				// else if ($type == "Edit")
				// 	$this->adminservices_records_header->update($id);

				$this->load->model('Logs'); $this->Logs->audit_logs($id, 'adminservices_records_header', $type, $this->modulename('label'));
			}
			
			$arr = array();  
			$arr['success'] = true;
			if ($type == "Add")
				$arr['data'] = "Successfully Created";
			if ($type == "Edit")
				$arr['data'] = "Successfully Updated";
			if ($type == "Delete")
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
}