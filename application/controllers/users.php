<?php if (!defined('INDEX')) {
    exit('No direct script access allowed');
}

class Users extends Main
{
    public function __construct()
    {
        $this->auth->permission();
    }

    public function getData()
    {
        $data['data'] = $this->db->select("users", [
            "[>]roles" => "id_role",
        ], [
            "users.id_user", "users.id_role", "users.username", "users.name", "roles.role_name",
        ], [
            "ORDER" => ["users.id_user" => "ASC"],
        ]);
        $this->render->json($data);
    }

    public function submitAdd()
    {
        $post_data = $this->render->json_post();
        $data = array(
            'username' => $post_data['userName'],
            'name' => $post_data['name'],
            'id_role' => $post_data['idRole'],
            'password' => password_hash($post_data['password'], 1),
        );
        if ($this->db->insert("users", $data)) {
            $id = $this->db->id();
            $this->set->success_message(true, array('id' => $id));
        }
        $this->set->error_message(true, $this->db->log());
    }

    public function submitEdit()
    {
        $post_data = $this->render->json_post();
        $data = array(
            'username' => $post_data['userName'],
            'name' => $post_data['name'],
            'id_role' => $post_data['idRole'],
        );
        if ($this->db->update("users", $data, ["id_user" => $post_data['idUser']])) {
            $this->set->success_message(true);
        }
        $this->set->error_message(true, $this->db->log());
    }

    public function submitDelete()
    {
        $post_data = $this->render->json_post();
        if ($this->db->delete("users", ["id_user" => $post_data['idUser']])) {
            $this->set->success_message(true);
        }
        $this->set->error_message(true, $this->db->log());
    }
}
