<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cat_Generate_Publication extends CI_Controller {
	/**
	*/ 
	private function modulename($type) {		
		if($type == 'link')
			return 'cat_generate_publication';
		else 
			return 'Publication';
	}

	public function index() {
		// update from Plantilla DB first before loading Page model
		// $this->update_from_plantilla();
		$this->load->model('Page');
        $this->Page->set_page($this->modulename('link'));
	}

	public function publication_list() {
		try { 
			$query = $this->esc_str($_GET['query']);
//			$appointment_status = $_GET['appointment_status'];
			$publication_status = $_GET['publication_status'];
			$show_all_items = $_GET['show_all_items'];
			die(json_encode($this->generatevacancies_list($query, $publication_status, $show_all_items, 'Grid')));
		}
		catch(Exception $e) {
			print $e->getMessage();
			die();	
		}
	}
 	
	public function generatevacancies_list($query, $publication_status, $show_all_items, $transaction_type) {
		try {
			$this->load->library('session');

			$limitQuery = "";
			if($transaction_type == 'Grid') {
				$limit = $_GET['limit'];
				$start = $_GET['start'];
				$limitQuery = " LIMIT $start, $limit";
			}
			$publication_status_desc = ""; $publication_status_filter = "";
			if($publication_status == 1) {$publication_status_desc = 'Published'; $publication_status_filter = "AND (latest_posting > CURDATE() OR CURDATE() <= DATE_ADD(latest_posting, INTERVAL 8 MONTH))";}
			else if($publication_status == 2) {$publication_status_desc = 'Unpublished'; $publication_status_filter = "AND latest_posting IS NULL";}
			else if($publication_status == 3) {$publication_status_desc = 'Expiring'; $publication_status_filter = "AND (CURDATE() > DATE_ADD(latest_posting, INTERVAL 8 MONTH) AND CURDATE() <= DATE_ADD(latest_posting, INTERVAL 9 MONTH))";}
			else if($publication_status == 4) {$publication_status_desc = 'Expired'; $publication_status_filter = "AND CURDATE() > DATE_ADD(latest_posting, INTERVAL 9 MONTH)";}

			$show_all_items_filter = ($show_all_items == 1) ? "": "AND is_vacant = 1";
			$show_all_items_desc = ($show_all_items == 1) ? "Yes": "No";

			$commandText = "SELECT
								a.id,
								a.item_desc,
								IF(a.item_desc_detail IS NULL, '', a.item_desc_detail) AS item_desc_detail,
								a.plantilla_item_no,
								a.posgrade,
								d.step_1,
								a.occupant_desc,
								IF(b.education IS NULL, '', b.education) AS education,
								IF(b.experience IS NULL, '', b.experience) AS experience,
								IF(b.training IS NULL, '', b.training) AS training,
								IF(b.eligibility IS NULL, '', b.eligibility) AS eligibility,
								IF(b.competency IS NULL, '', b.competency) AS competency,
								c.description,
								IF(a.latest_posting IS NULL, '', DATE_FORMAT(a.latest_posting, '%Y-%m-%d')) AS latest_posting,
								IF(a.public_status IS NULL, 'Unpublished', a.public_status) AS public_status,
								IF(a.latest_posting > CURDATE() OR CURDATE() <= DATE_ADD(a.latest_posting, INTERVAL 8 MONTH), 'Published',
									IF(a.latest_posting IS NULL, 'Unpublished', 
										IF(CURDATE() > DATE_ADD(a.latest_posting, INTERVAL 8 MONTH) AND CURDATE() <= DATE_ADD(a.latest_posting, INTERVAL 9 MONTH), 'Expiring',
											IF(CURDATE() > DATE_ADD(a.latest_posting, INTERVAL 9 MONTH), 'Expired', '')))) AS public_remarks,
								a.is_vacant

							FROM vacancies a
							LEFT JOIN vacancies_qs b ON b.plantilla_item_id = a.plantilla_item_id
							LEFT JOIN departments c ON c.depcode = a.depcode
							LEFT JOIN ssltable d ON d.sal_grade = a.posgrade AND ssl_year=2022
							WHERE (
									a.item_desc LIKE '%$query%'
									OR a.item_code LIKE '%$query%'
									OR a.item_desc_detail LIKE '%$query%'
									OR a.posgrade LIKE '%$query%'
									OR a.public_status LIKE '%$query%'
									OR a.latest_posting LIKE '%$query%'
									OR c.description LIKE '%$query'
								  )
								AND active = 1
								$publication_status_filter
								$show_all_items_filter
							$limitQuery";	
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			$commandText = "SELECT COUNT(a.id) AS count
							FROM vacancies a
							LEFT JOIN vacancies_qs b ON b.plantilla_item_id = a.plantilla_item_id
							LEFT JOIN departments c ON c.depcode = a.depcode
							LEFT JOIN ssltable d ON d.sal_grade = a.posgrade AND ssl_year=2022
							WHERE  (
									a.item_desc LIKE '%$query%'
									OR a.item_code LIKE '%$query%'
									OR a.item_desc_detail LIKE '%$query%'
									OR a.posgrade LIKE '%$query%'
									OR a.public_status LIKE '%$query%'
									OR a.latest_posting LIKE '%$query%'
									OR c.description LIKE '%$query'
								)
								AND active = 1
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
					'item_desc'					=> $val->item_desc,
					'item_desc_detail' 			=> $val->item_desc_detail,
					'plantilla_item_no' 		=> $val->plantilla_item_no,
					'posgrade' 					=> $val->posgrade,
					'step_1'					=> $val->step_1,
					'occupant_desc'				=> $val->occupant_desc,
					'education' 				=> $val->education,
					'experience' 				=> $val->experience,							
					'training' 					=> $val->training,										
					'eligibility' 				=> $val->eligibility,
					'competency' 				=> $val->competency,
					'description' 				=> $val->description,
					'public_status' 			=> $val->public_status,
					'public_remarks' 			=> $val->public_remarks,
					'public_remarks_style' 		=> $public_remarks_style,
					'latest_posting' 			=> $val->latest_posting,
					'is_vacant' 				=> ($val->is_vacant == 0) ? false: true
				);
			}

			$data['totalCount'] = $query_count[0]->count;
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
								a.item_desc,
								a.item_desc_detail,
								a.plantilla_item_no,
								a.posgrade,
								d.step_1,
   								a.occupant_desc,
    							b.education,
    							b.experience,
    							b.training,
    							b.eligibility,
    							b.competency,
    							c.description
							FROM vacancies a
							LEFT JOIN vacancies_qs b ON b.plantilla_item_id = a.plantilla_item_id
							LEFT JOIN departments c ON c.depcode = a.depcode
							LEFT JOIN ssltable d ON d.sal_grade = a.posgrade AND ssl_year=2022
							WHERE id = $id";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			foreach($query_result as $key => $val) {
				$data['data'][] = array(
					'id' 						=> $val->id,
					'item_desc_detail' 			=> $val->item_desc_detail,
					'plantilla_item_no' 		=> $val->plantilla_item_no,
					'posgrade' 					=> $val->posgrade,
					'step_1'					=> $val->step_1,
					'occupant_desc'				=> $val->occupant_desc,
					'education' 				=> $val->education,
					'experience' 				=> $val->experience,							
					'training' 					=> $val->training,										
					'eligibility' 				=> $val->eligibility,
					'competency' 				=> $val->competency,
					'description' 				=> $val->description,
					'public_status' 			=> $val->public_status,
					'public_remarks' 			=> $val->public_remarks,
					'public_remarks_style' 		=> $public_remarks_style,
					'latest_posting' 			=> $val->latest_posting,
					'is_vacant' 				=> ($val->is_vacant == 0) ? false: true
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

	public function exportdocument() {
		$query = $this->esc_str($this->input->post('query'));
		$publication_status = $this->input->post('publication_status');
		$show_all_items = $this->input->post('show_all_items');
		$type = $this->input->post('filetype');

		$response = array();
		$response['success'] = true;
		if($type == 'Excel')
			$response['filename'] = $this->export_excelvacancies_list($this->generatevacancies_list($query,$publication_status, $show_all_items, 'Report'));
		$this->load->model('Logs'); $this->Logs->audit_logs(0, 'vacancies', 'Report-'.$type, 'Vacancies List');        	
		die(json_encode($response));
	}


	public function export_excelvacancies_list($data) {
		try {
			$this->load->library('PHPExcel');

			$path 			= getenv('DOCUMENTS_DIR');
			$type  		 	= 'Excel5';
			$name 			= "Template - Publication List.xls";
			// $objReader 		= PHPExcel_IOFactory::createReader($type);
			$objPHPExcel  	= PHPExcel_IOFactory::load($path.$name);
			// $objPHPExcel 	= $objReader->load($path.$name);
			// $objPHPExcel 	= $objPHPExcel->setActiveSheetIndex(0);

			// $objPHPExcel = new PHPExcel();
			$objPHPExcel->getActiveSheet()->setShowGridlines(true);
			$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('Arial');
			$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(11);
			// $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(3);

			$fDate = date("Ymd_His");
			if($data['publication_status_desc'] != "" || $data['show_all_items_desc'] != "") 
				$filename = "Publication List (with filters) ";
			else
				$filename = "Publication List ";

			$filename_with_ext = $filename . $fDate . ".xls";

			$objPHPExcel->getProperties()->setCreator(getenv('REPORT_CREATOR'))
						->setLastModifiedBy(getenv('REPORT_AUTHOR'))
						->setTitle("Publication List")
						->setSubject("Report")
						->setDescription("Generating Publication List")
						->setKeywords(getenv('REPORT_KEYWORDS'))
						->setCategory("Reports");

			#Dimensions
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(5);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(35);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(18);
			$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(25);	

			#Font & Alignment
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('A7:L7')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('G8:K8')->getFont()->setBold(true);

			### Title
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1", $filename);
			
			###DATE
			$objPHPExcel->setActiveSheetIndex(0)	
					      	->setCellValue("A3", "Publication Status: " . $data['publication_status_desc'])
					      	->setCellValue("A4", "Show Non-Vacant Items: " . $data['show_all_items_desc'])
					      	->setCellValue("A7", "No.")		
					      	->setCellValue("B7", "Position (Parenthetical Title, if applicable")
					      	->setCellValue("C7", "Item No.")
					      	->setCellValue("D7", "SG")
					      	->setCellValue("E7", "Monthly Salary")
					      	->setCellValue("F8", "Education")
					      	->setCellValue("G8", "Experience")
					      	->setCellValue("H8", "Training")
					      	->setCellValue("I8", "Eligibility")
					      	->setCellValue("J8", "Competency (if applicable)")
					      	->setCellValue("K7", "Place of Assignment");

			for($i=0; $i<$data['totalCount']; $i++) {

				$pos_desc = $data['data'][$i]['item_desc'] . ' ' . $data['data'][$i]['item_desc_detail'];
				$objPHPExcel->setActiveSheetIndex(0)
						   		->setCellValue("A".($i+9), $i+1)
						      	->setCellValue("B".($i+9), $pos_desc)
						      	->setCellValue("C".($i+9), $data['data'][$i]['plantilla_item_no'])
						      	->setCellValue("D".($i+9), $data['data'][$i]['posgrade'])
						      	->setCellValue("E".($i+9), $data['data'][$i]['step_1'])
						      	->setCellValue("F".($i+9), $data['data'][$i]['education'])	
						      	->setCellValue("G".($i+9), $data['data'][$i]['experience'])
						      	->setCellValue("H".($i+9), $data['data'][$i]['training'])
						      	->setCellValue("I".($i+9), $data['data'][$i]['eligibility'])
						      	->setCellValue("J".($i+9), $data['data'][$i]['competency'])
						      	->setCellValue("K".($i+9), $data['data'][$i]['description']);	
		   	}					      
	      	
			$this->load->library('session');
			// $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".($i+8), 'Printed by: '.$this->session->userdata('name'));
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".($i+11), 'Date Printed: '.date('m/d/Y h:i:sa'));
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".($i+12), 'Print Code: '.$filename_with_ext);
			
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

	private function esc_str($x) {
		return mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($x)));
	}
}