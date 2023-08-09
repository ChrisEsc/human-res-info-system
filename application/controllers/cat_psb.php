<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cat_PSB extends CI_Controller {
	/**
	*/ 
	private function modulename($type) {		
		if($type == 'link')
			return 'cat_psb';
		else 
			return 'HRMPSB';
	} 

	public function index() {
		$this->load->model('Page');
        $this->Page->set_page($this->modulename('link'));
	}

	public function psb_list() {
		try {
			$query = $this->esc_str($_GET['query']);
			$psb_status = $_GET['psb_status'];
			die(json_encode($this->generatepsb_list($query, $psb_status, 'Grid')));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();	
		}
	}

	public function generatepsb_list($query, $psb_status, $transaction_type) {
		try {
			$this->load->library('session');

			$limitQuery = "";
			if($transaction_type == 'Grid') {
				$limit = $_GET['limit'];
				$start = $_GET['start'];
				$limitQuery = " LIMIT $start, $limit";
			}

			// 0-pending, 1-completed, 2-all
			$psb_status_filter = "1 = 1"; $status_desc = "All";
			if($psb_status == 0) {$psb_status_filter = " a.is_locked IS NULL"; $status_desc = "Pending";}
			else if($psb_status == 1) {$psb_status_filter = " a.is_locked = 1"; $status_desc = "Completed";}

			$commandText = "SELECT a.id AS lineup_vacancy_id,
								a.header_id AS lineup_header_id,
								a.selected_lineup_applicant_id,
							    b.item_details,
							    d.item_code,
							    d.plantilla_item_no,
							    d.posgrade,
							    d.depcode,
							    IF(d.latest_posting IS NULL, 'Unpublished', DATE_FORMAT(d.latest_posting, '%m/%d/%Y')) AS latest_posting,
							    CONCAT(e.lname, ', ', e.fname, IF(e.mname IS NULL, '', CONCAT(' ', e.mname)), IF(e.suffix IS NULL, '', CONCAT(' ', e.suffix))) AS selected_applicant_name,
							    DATE_FORMAT(a.date_psb, '%m/%d/%Y') AS date_psb,
							    c.remarks,
							    a.is_locked
							FROM selection_lineup_vacancies a
								LEFT JOIN selection_lineup_header b ON b.id = a.header_id
								LEFT JOIN selection_lineup_applicants c ON c.id = a.selected_lineup_applicant_id
								LEFT JOIN vacancies d ON d.id = a.vacancy_id
								LEFT JOIN applicants e ON e.id = c.applicant_id
							WHERE $psb_status_filter
							ORDER BY b.id DESC, d.plantilla_item_no ASC";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$commandText = "SELECT count(*) AS count
							FROM selection_lineup_vacancies a
								LEFT JOIN selection_lineup_header b ON b.id = a.header_id
								LEFT JOIN selection_lineup_applicants c ON c.id = a.selected_lineup_applicant_id
								LEFT JOIN vacancies d ON d.id = a.vacancy_id
								LEFT JOIN applicants e ON e.id = c.applicant_id
							WHERE $psb_status_filter";
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
					'lineup_vacancy_id'				=> $value->lineup_vacancy_id,
					'lineup_header_id' 				=> $value->lineup_header_id,
					'selected_lineup_applicant_id' 	=> $value->selected_lineup_applicant_id,
					'item_details' 					=> $value->item_details,
					'item_code' 					=> $value->item_code,
					'plantilla_item_no' 			=> $value->plantilla_item_no,
					'posgrade' 						=> $value->posgrade,
					'depcode' 						=> $value->depcode,
					'latest_posting' 				=> $value->latest_posting,
					'selected_applicant_name' 		=> $value->selected_applicant_name,
					'date_psb' 						=> $value->date_psb,
					'remarks' 						=> $value->remarks,
					'is_locked' 					=> $value->is_locked
				);
			}

			$data['totalCount'] = $query_count[0]->count;
			$data['status_desc'] = $status_desc;
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

			$lineup_header_id  				= $this->input->post('lineup_header_id');
			$lineup_vacancy_id  			= $this->input->post('lineup_vacancy_id');
			$selected_lineup_applicant_id  	= $this->input->post('selected_lineup_applicant_id');
			$date_psb 						= date('Y-m-d', strtotime($this->input->post('date_psb')));
			$remarks 						= $this->esc_str($this->input->post('remarks'));

			$remarks_query = " remarks = NULL";
			if(isset($remarks) && $remarks != "") $remarks_query = " remarks = '$remarks'";
			$this->load->model('Access'); $this->Access->rights($this->modulename('link'), 'Add', null); // replaced $type with "Add"

			// should be implemented as a transaction, transaction starts here
			$commandText = "UPDATE selection_lineup_vacancies SET selected_lineup_applicant_id = $selected_lineup_applicant_id, date_psb = '$date_psb' WHERE id = $lineup_vacancy_id";
			$result = $this->db->query($commandText);
			$this->load->model('Logs'); $this->Logs->audit_logs($lineup_vacancy_id, 'selection_lineup_vacancies', 'Update', $this->modulename('label'));

			$commandText = "UPDATE selection_lineup_applicants SET is_selected = 1, $remarks_query WHERE id = $selected_lineup_applicant_id";
			$result = $this->db->query($commandText);
			$this->load->model('Logs'); $this->Logs->audit_logs($selected_lineup_applicant_id, 'selection_lineup_applicants', 'Update', $this->modulename('label'));

			// check if all vacancies for the same header are all filled up
			$commandText = "SELECT * FROM selection_lineup_vacancies WHERE selected_lineup_applicant_id IS NULL AND header_id = $lineup_header_id";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			// if there are no more vacant items for the same header, lock all of them
			if(count($query_result) == 0) {
				$commandText = "UPDATE selection_lineup_vacancies SET is_locked = 1 WHERE header_id = $lineup_header_id";
				$result = $this->db->query($commandText);
				$this->load->model('Logs'); $this->Logs->audit_logs($lineup_vacancy_id, 'selection_lineup_vacancies', 'Lock vacancies', $this->modulename('label'));

				// after locking, update "status_psb" on selection_lineup_applicants table
				$commandText = "UPDATE selection_lineup_applicants SET status_psb = 'Done' WHERE lineup_vacancy_id IN (SELECT id FROM selection_lineup_vacancies WHERE header_id = $lineup_header_id)";
				$result = $this->db->query($commandText);
				$this->load->model('Logs'); $this->Logs->audit_logs($lineup_vacancy_id, 'selection_lineup_applicants', 'Status PSB Set to Done', $this->modulename('label'));

			}
			// transaction ends here

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

	public function psbview() {
		try {
			#update session
			$this->load->model('Session');$this->Session->Validate();
			
			$id = $this->input->post('id');

			$commandText = "SELECT 
								a.id,
								a.selected_lineup_applicant_id,
								CONCAT(c.lname, ', ', c.fname, IF(c.mname IS NULL, '', CONCAT(' ', c.mname)), IF(c.suffix IS NULL, '', CONCAT(' ', c.suffix))) AS selected_applicant_name,
								a.date_psb,
								b.remarks
							FROM selection_lineup_vacancies a
								LEFT JOIN selection_lineup_applicants b ON b.id = a.selected_lineup_applicant_id
								LEFT JOIN applicants c ON c.id = b.applicant_id
							WHERE a.id = $id";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$data = array();
			$record = array();

			foreach($query_result as $key => $value) {	
				$record['id'] 							= $value->id;
				$record['selected_lineup_applicant_id']	= $value->selected_lineup_applicant_id;
				$record['selected_applicant_name']		= $value->selected_applicant_name;
				$record['date_psb']						= is_null($value->date_psb) ? '': date('m/d/Y', strtotime($value->date_psb));
				$record['remarks']						= $value->remarks;
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

	public function lineupapplicants_list() {
		try {
			$lineup_header_id = $_GET['lineup_header_id'];

			$commandText = "SELECT a.id,
								CONCAT(d.lname, ', ', d.fname, IF(d.mname IS NULL, '', CONCAT(' ', d.mname)), IF(d.suffix IS NULL, '', CONCAT(' ', d.suffix))) AS applicant_name,
								a.is_selected
							FROM selection_lineup_applicants a
								LEFT JOIN selection_lineup_vacancies b ON b.id = a.lineup_vacancy_id
							    LEFT JOIN selection_lineup_header c ON c.id = b.header_id
							    LEFT JOIN applicants d ON d.id = a.applicant_id
							WHERE c.id = $lineup_header_id";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			if(count($query_result) == 0) {
				$data["count"] = 0;
				$data["data"] = array();
				die(json_encode($data));
			}

			foreach($query_result as $key => $value) {
				$data['data'][] = array(
					'id' 			=> $value->id,						
					'description' 	=> $value->applicant_name,
					'is_selected' 	=> $value->is_selected
				);
			}
			
			die(json_encode($data));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();
		}
	}

	public function exportdocument() {
		$query = $this->esc_str($this->input->post('query'));
		$status = $this->input->post('status');
		$type = $this->input->post('filetype');

		$response = array();
		$response['success'] = true;
		if($type == 'Excel')
			$response['filename'] = $this->export_excelpsb_list($this->generatepsb_list($query, $status, 'Report'));
		$this->load->model('Logs'); $this->Logs->audit_logs(0, 'selection_lineup_vacancies', 'Report-'.$type, 'PSB List');        	
		die(json_encode($response));
	}

	public function export_excelpsb_list($data) {
		try {
			$this->load->library('PHPExcel');

			$path 			= "documents/";
			$type  		 	= 'Excel5';
			$name 			= "Template - PSB List.xls";
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
			$filename = "PSB List (" . $data['status_desc'] . ") " . $fDate . ".xls";

			$objPHPExcel->getProperties()->setCreator(getenv('REPORT_CREATOR'))
						->setLastModifiedBy(getenv('REPORT_AUTHOR'))
						->setTitle("PSB List")
						->setSubject("Report")
						->setDescription("Generating PSB List")
						->setKeywords(getenv('REPORT_KEYWORDS'))
						->setCategory("Reports");

			#Dimensions
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(35);	

			#Font & Alignment
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('A3:H3')->getFont()->setBold(true);
			// $objPHPExcel->getActiveSheet()->getStyle('B3:J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('D3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('D4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			#Duplicate Cell Styles
			$objPHPExcel->getActiveSheet()->duplicateStyle( $objPHPExcel->getActiveSheet()->getStyle('A4'), 'A5:A'.($data['totalCount']+3));
			$objPHPExcel->getActiveSheet()->duplicateStyle( $objPHPExcel->getActiveSheet()->getStyle('D4'), 'D5:D'.($data['totalCount']+3));

			### Title
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1", "PSB List (" . $data['status_desc'] . ")");
			
			###DATE
			$objPHPExcel->setActiveSheetIndex(0)
					      	->setCellValue("A3", "No.")		
					      	->setCellValue("B3", "Item Name")
					      	->setCellValue("C3", "Item Code")
					      	->setCellValue("D3", "Item No.")
					      	->setCellValue("E3", "Latest Posting")
					      	->setCellValue("F3", "Selected Applicant")
					      	->setCellValue("G3", "PSB Date")
					      	->setCellValue("H3", "Remarks");

			for($i=0; $i<$data['totalCount']; $i++) {
				$objPHPExcel->setActiveSheetIndex(0)
						   		->setCellValue("A".($i+4), $i+1)
						      	->setCellValue("B".($i+4), $data['data'][$i]['item_details'])
						      	->setCellValue("C".($i+4), $data['data'][$i]['item_code'])
						      	->setCellValue("D".($i+4), $data['data'][$i]['plantilla_item_no'])
						      	->setCellValue("E".($i+4), $data['data'][$i]['latest_posting'])
						      	->setCellValue("F".($i+4), $data['data'][$i]['selected_applicant_name'])
						      	->setCellValue("G".($i+4), $data['data'][$i]['date_psb'])
						      	->setCellValue("H".($i+4), $data['data'][$i]['remarks']);
		   	}					      
	      	
			$this->load->library('session');
			// $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".($i+8), 'Printed by: '.$this->session->userdata('name'));
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".($i+9), 'Date Printed: '.date('m/d/Y h:i:sa'));
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".($i+10), 'Print Code: '.$filename);
			
			$objPHPExcel->setActiveSheetIndex(0);				
			$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
			$path = "documents";
			$objWriter->save("$path/$filename");
			return "$path/$filename";
		}
		catch(Exception $e) {
			die(json_encode($e->getMessage()));	
		}
	}

	private function esc_str($x) {
		return mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($x)));
	}
}