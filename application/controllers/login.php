<?php if( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {
	/**
	*/
	private function modulename($type) {		
		if($type == 'link')
			return 'login';
		else 
			return 'Login';
	}

	public function index() {
		$this->load->helper('common_helper');
		$this->load->view($this->modulename('link').'/index');
		$this->load->view('templates/footer');
	}

	public function userauthentication() {
		try {
			$user_name	= mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($this->input->post('user_name'))));
			$password	= mysqli_real_escape_string($this->db->conn_id, strip_tags(trim($this->input->post('password'))));
			$type		= $this->input->post('type');
			
			$this->load->model('Cipher');
			$this->Cipher->secretpassphrase();			
			$encryptedtext = $this->Cipher->encrypt($password);

			$commandText = "SELECT 	
								a.id,
								a.user_id,
								b.department_id,
							    b.division_id,
							    b.section_id,
							    b.employee_id,
								a.admin,
								a.username,	
							    b.division_head,
							    b.section_head,
							    c.description AS department_description,
							    d.description AS division_description,
							    e.description AS position_description,
							    CONCAT(b.fname, ' ', b.mname, ' ', b.lname) AS sname
							FROM users a 
								JOIN staff b ON a.user_id = b.id
							    JOIN departments c ON b.department_id = c.id 
							    JOIN divisions d ON b.division_id = d.id
							    LEFT JOIN positions e ON b.position_id = e.id
							WHERE a.username = '$user_name' 
							    AND a.password = '$encryptedtext'
							    AND a.active = 1
							    AND a.type ='$type'";
			$result = $this->db->query($commandText);
			$query_result = $result->result();

			if(count($query_result) == 0) {
				$this->load->library('session');
				$commandText = "INSERT INTO audit_logs (transaction_id, transaction_type, query_type, date_created, time_created) VALUES (0, 'Failed Attempt! (Username:".mysqli_real_escape_string($this->db->conn_id, $user_name).")', 'Login', '".date('Y-m-d')."', '".date('H:i:s')."')";
				$result = $this->db->query($commandText);
				$data = array("success"=> false, "data"=>"Username/password incorrect. Please contact system administrator.");
				die(json_encode($data));
			}
			#set session
			$this->load->library('session');

			$newdata = array(
				'id'					=> $query_result[0]->id,
				'user_id'				=> $query_result[0]->user_id,
				'department_id'			=> $query_result[0]->department_id,
				'division_id'			=> $query_result[0]->division_id,
				'section_id'			=> $query_result[0]->section_id,
				'employee_id'			=> $query_result[0]->employee_id,
				'admin'					=> $query_result[0]->admin,
				'un'					=> $query_result[0]->username,
				'division_head'			=> $query_result[0]->division_head,
				'section_head' 			=> $query_result[0]->section_head,
				'name'  				=> mb_strtoupper($query_result[0]->sname),
				'department_description'=> $query_result[0]->department_description,
				'division_description'	=> $query_result[0]->division_description,
				'position_description'	=> $query_result[0]->position_description,
				'type'					=> $type,
				'logged_in' 			=> TRUE,
				'time' 					=> date('Y-m-d H:i:s')
			);
			$this->session->set_userdata($newdata);
			$route = "thumbnailmenu";	 
			$this->load->model('Logs'); $this->Logs->audit_logs(0, 'login', 'Login', 'Successfully Login!');
		
			$arr = array();  
			$arr['success'] = true;
			$arr['data'] = $route;
			$arr['name'] = mb_strtoupper($query_result[0]->sname);
			$arr['staffID'] = $query_result[0]->user_id;
			die(json_encode($arr));
		}
		catch(Exception $e) {
			$data = array("success"=> false, "data"=>$e->getMessage());
			die(json_encode($data));
		}
	}
}