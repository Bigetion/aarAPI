<?php if (!defined('INDEX')) {
    exit('No direct script access allowed');
}

class Login extends Main
{

    public function index()
    {
        $post_data = $this->render->json_post();
        $this->gump->validation_rules(array(
            'username' => 'required|alpha_numeric',
            'password' => 'required',
        ));

        $this->gump->filter_rules(array(
            'username' => 'trim|sanitize_string',
            'password' => 'trim',
        ));
        $this->gump->run_validation($post_data);

        $username = $post_data['username'];
        $password = $post_data['password'];

        function random_string($length = 10)
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        if (empty($username) || empty($password)) {
            $this->set->error_message(true);
        }

        $dataUser = $this->db->select("users", [
            "[>]roles" => "id_role",
        ], [
            "users.id_user", "users.id_role", "users.username", "users.name", "users.password", "roles.role_name",
        ], [
            "username" => $username,
        ]);

        if (count($dataUser) == 0) {
            $this->set->error_message(true);
        } else {
            if (!password_verify($password, $dataUser[0]["password"])) {
                $this->set->error_message(true);
            } else if ($dataUser[0]["id_role"] == 2) {
                $this->set->error_message(true);
            } else {
                try {
                    $payload = array(
                        'jti' => random_string(),
                        'iat' => time(),
                        'nbf' => time() + 10,
                        'exp' => time() + 7210,
                        'iss' => get_header('origin'),
                        'data' => array(
                            'user' => $username,
                        ),
                    );
                    $jwtTokenEncode = $this->jwt->encode($payload, base64_decode(secret_key));

                    $data = array();
                    $data['jwt'] = $jwtTokenEncode;
                    $data['user']['idRole'] = $dataUser[0]['id_role'];
                    $data['user']['idUser'] = $dataUser[0]['id_user'];
                    $data['user']['username'] = $dataUser[0]['username'];
                    $data['user']['name'] = $dataUser[0]['name'];
                    $data['user']['roleName'] = $dataUser[0]['role_name'];
                    $data['user']['externalInfo'] = array();

                    $this->set->success_message(true, $data);
                } catch (Exception $ex) {
                    $this->set->error_message(true, $ex);
                }
            }
        }
    }
}
