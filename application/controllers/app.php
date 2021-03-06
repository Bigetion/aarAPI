<?php if (!defined('INDEX')) {
    exit('No direct script access allowed');
}

class App extends Main
{
    public function getModules()
    {
        $this->auth->permission();
        $a = load_file('project');

        foreach ($a as $value) {
            if ($value != '.' && $value != '..') {
                // $data['project'][] = $value;
                $b = load_file('project/' . $value);
                if (in_array('controllers', $b)) {
                    $b = load_recursive('project/' . $value . '/controllers');
                    foreach ($b as $value2) {
                        $data[$value][] = substr(basename($value2), 0, -4);
                    }
                } else {
                    $data[$value][] = ' -----  ';
                }

            }
        }
        if (empty($data)) {
            $data[] = '-----';
        }

        $this->render->json($data);
    }

    public function changePassword()
    {
        $this->auth->permission();
        $post_data = $this->render->json_post();
        $user = $this->db->select("users", "*", ["id_user" => id_user]);
        if (password_verify($post_data['passwordOld'], $user[0]['password'])) {
            $data = array(
                "password" => password_hash($post_data['passwordNew'], 1),
            );
            if ($this->db->update("users", $data, ["id_user" => id_user])) {
                $this->set->success_message(true);
            } else {
                $this->set->error_message(true);
            }
        } else {
            $this->set->error_message(true);
        }
    }

    public function getUserInfo()
    {
        $data['idRole'] = id_role;
        $data['idUser'] = id_user;

        $dataUser = $this->db->select("users", [
            "[>]roles" => "id_role",
        ], [
            "users.id_user", "users.id_role", "users.username", "users.name", "roles.role_name",
        ], [
            "users.id_user" => id_user,
        ]);

        if (count($dataUser) > 0) {
            $data['username'] = $dataUser[0]['username'];
            $data['name'] = $dataUser[0]['name'];
            $data['roleName'] = $dataUser[0]['role_name'];
            $data['externalInfo'] = array();
        }
        $this->render->json($data);
    }

    public function getLocationInfo()
    {
        header('Content-Type: application/json');
        echo file_get_contents("https://ipinfo.io/");
    }
}
