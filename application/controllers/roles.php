<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Roles extends Main {
    function __construct() {
        $this->auth->permission();
    }

    function getData(){
        $data['data'] = $this->db->select("roles","*", [
            "ORDER" => ["id_role" => "ASC"],
        ]);
        $this->render->json($data);
    }

    function submitAdd(){
        $post_data = $this->render->json_post();
        $data = array(
            'role_name'     => $post_data['roleName'],
            'description'   => $post_data['description'],
            'permission'   => ' ',
        );
        if($this->db->insert("roles", $data)){
            $id = $this->db->id();
            $this->set->success_message(true, array('id'=>$id));
        }
        $this->set->error_message(true, $this->db->log());
    }

    function submitEdit(){
        $post_data = $this->render->json_post();
        $data = array(
            'role_name'     => $post_data['roleName'],
            'description'   => $post_data['description'],
            'permission'   => ' ',
        );
        if($this->db->update("roles", $data, ["id_role" => $post_data['idRole']])){
            $this->set->success_message(true);
        }
        $this->set->error_message(true, $this->db->log());
    }

    function submitDelete(){
        $post_data = $this->render->json_post();
        if($this->db->delete("roles", ["id_role" => $post_data['idRole']])){
            $this->set->success_message(true);
        }
        $this->set->error_message(true, $this->db->log());
    }
}    
?>