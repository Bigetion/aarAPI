<?php if (!defined('INDEX')) exit('No direct script access allowed');

class Render {
    function json($data) {
        $header_with_payload = get_header('Access-Control-Request-Method');
        header('Content-Type: application/json');
        if(!$header_with_payload){
            echo json_encode($data);
        }
    }

	function json_post(){
		return json_decode(file_get_contents('php://input'), true);
	}

}
?>
