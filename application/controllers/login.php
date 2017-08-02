<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Login extends Main {

    function index(){
        $json_data = $this->render->json_post();
        $user = strtolower($json_data['username']);
        $password = $json_data['password'];

        if (empty($user)|| empty($password))
            $this->set->error_message(false);
            
        $data = $this->db->select("users","*",["username"=>$user]);
        if (count($data) == 0)
            $this->set->error_message(true);
        else {
            if(!password_verify($password,$data[0]["password"])) $this->set->error_message(false);
            else{
                try{
                    $payload = array(
                        'jti'       => bin2hex(random_bytes(5)),
                        'iat'       => time(),
                        'nbf'       => time() + 10,
                        'exp'       => time() + 7210,
                        'iss'       => get_header('origin'),
                        'data'      => array(
                                    'user'  => strtolower($user),
                                    )
                    );
                    $jwtTokenEncode = $this->jwt->encode($payload, base64_decode(secret_key));

                    $token['jwt'] = $jwtTokenEncode;
                    
                    $this->set->success_message(true, $token);
                }
                catch(Exception $ex){
                    $this->set->error_message(true, $ex);
                }
            }
        }
    }
}

?>