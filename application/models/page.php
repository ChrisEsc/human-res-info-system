<?php

require_once "my_model.php";
class Page extends My_Model {
    /**
    */
    function set_page($page) {
        try {
            $this->load->library('session');
            $id                 = $this->session->userdata('id');
            $user_id            = $this->session->userdata('user_id');
            $user_division_id   = $this->session->userdata('division_id');

            if(!$id) {
                header("Location:".base_url().'index.php/login');
                exit();
            }

            #module
            $commandText = "SELECT module_name FROM modules WHERE link = '$page'";
            $result = $this->db->query($commandText);
            $query_result = $result->result(); 
            $module_name = $query_result[0]->module_name;

            #access right to Maintenance(...) ADD / EDIT / DELETE
            $commandText = "SELECT * FROM modules_users WHERE module_id = (SELECT id FROM modules WHERE module_name = 'Maintenance Module') AND user_id = $id AND (uadd = 1 OR uedit = 1 OR udelete = 1)";
            $result = $this->db->query($commandText);
            $query_result = $result->result(); 

            #generating menu         
            $this->load->model('Menu');
            $data['menu']       = $this->Menu->set_userid($id);
            $data['username']   = $this->session->userdata('name');
            $data['admin']      = $this->session->userdata('admin');
            $data['user_type']  = $this->session->userdata('type');
            $user_type = $data['user_type'];
            $data['department'] = $this->session->userdata('department_description'). ' Department';
            $data['department_id'] = $this->session->userdata('department_id');
            $data['division'] = $this->session->userdata('division_description');
            $data['division_id'] = $this->session->userdata('division_id');
            $data['module_name'] = $module_name;
            if(count($query_result) > 0) $data['boolMaintenance'] = 1;
            else $data['boolMaintenance'] = 0;

            $user_division_id = $this->session->userdata('division_id');
			$user_section_id = $this->session->userdata('section_id');
			$user_section_head = $this->session->userdata('section_head');
            $filter = " AND a.division_id = $user_division_id";


            if($page == 'thumbnailmenu') {     
                $data['notification_name'] = "";

                if($user_type == 'Staff') {
                    #pending incoming communications actions taken
                    // $commandText = "SELECT count(*) as count
                    //                 FROM adminservices_records_header a
                    //                 LEFT JOIN record_types b ON a.record_type_id = b.id
                    //                 LEFT JOIN adminservices_records_from_to c ON a.from_id = c.id
                    //                 LEFT JOIN adminservices_records_from_to d ON a.to_id = d.id
                    //                 LEFT JOIN adminservices_records_actions_taken f ON a.action_taken_id = f.id
                    //                 WHERE a.status = 'Pending Action Taken'                                       
                    //                     AND a.communication_type = 'Incoming'
                    //                     AND a.active = 1";
                    // $commandText .= $filter;
                    // $result = $this->db->query($commandText);
                    // $query_result = $result->result(); 
                    // $pending_incoming_actionstaken_count = $query_result[0]->count;
                    

                    // if(isset($pending_incoming_actionstaken_count) && $pending_incoming_actionstaken_count > 0)
                    //     $data['notification_name'] .= '<a href="./adminservices_incoming_records?status=3" style="text-decoration:none;color:red"><b>('.$pending_incoming_actionstaken_count.') Pending Incoming Communications Action/s Taken.</b></a><br>';
                }

                $data['notification_name'] .= 'We highly appreciate ideas that will enhance our system, click ICT Development Team at the footer of this page and contact us.<br><br><br>';
            }

            $module = array('module' => $page);
            $this->session->set_userdata($module);
            #validating user access to module
            $this->load->model('Module_Validation');
            $this->Module_Validation->module_name($id, $page);


            $this->load->view('templates/header', $data);
            $this->load->view($page.'/index');
            $this->load->view('templates/footer');
            $this->load->helper('common_helper');
        }
        catch(Exception $e) {
            print $e->getMessage();
            die();  
        }
    }
}