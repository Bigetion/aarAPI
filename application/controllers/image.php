<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class image extends Main {
    function getAll(){
        $this->auth->permission();
        $post_data = $this->render->json_post();
        $image_path = '';
        $path = 'application/images/featured';
        if(isset($post_data['path'])){
            $image_path = $post_data['path'].'/';
            $path = 'application/images/'.$post_data['path'];
            $this->dir->create_dir($path);
        }
        $images = load_recursive($path, 0, array('jpg','jpeg','png'));

        $data['images'] = array();
        foreach($images as $image){
			$image = pathinfo($image);
            $data['images'][] = array(
                "name" => $image['basename'],
                "url" => base_url.'image/get/'.$image_path.$image['basename']
            );
        }
        $this->render->json($data);
    }

    function uploadImage(){
        $this->auth->permission();
		$allowedExts = array("jpeg", "jpg", "png");
		$temp = explode(".", $_FILES["image"]["name"]);
		
		$extension = end($temp);
        $path = 'application/images/featured';
        
        if(isset($post_data['path'])){
            $path = $post_data['path'];
        }
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $_FILES["image"]["tmp_name"]);
        $this->dir->create_dir($path);

		if ((($mime == "image/gif")
			|| ($mime == "image/jpeg")
			|| ($mime == "image/pjpeg")
			|| ($mime == "image/x-png")
			|| ($mime == "image/png"))
			&& in_array($extension, $allowedExts)) {
			$name = sha1(microtime()) . "." . $extension;
			if(move_uploaded_file($_FILES["image"]["tmp_name"], $path . '/' . $name)){
                $this->set->success_message(true);
            }
            $this->set->error_message(true);
		}
	}

    function deleteImage(){
        $this->auth->permission();
        $post_data = $this->render->json_post();
        $path = 'application/images/featured';
        
        if(isset($post_data['path'])){
            $path = $post_data['path'];
        }

        if(isset($post_data['path']) && isset($post_data['img'])){
            $path = 'application/images/'.$post_data['path'];
            $img = $post_data['img'];
            if(unlink($path.'/'.$img)) {
                $this->set->success_message(true);
            }
        }
        $this->set->error_message(true);
    }
    
    function get(){
        $id_image = subsegment(-1);
        $base_path = explode('/', str_replace('://','',base_url));
        $path = subsegment(count($base_path)+1,-1);
        $path = 'application/images/'.$path;
        if (file_exists($path."/default.png")) {
            $fileOut = $path."/default.png";
        }else{
            $fileOut = "application/images/default.png";
        }
        if (is_dir($path)){
            $images = load_recursive($path, 0, array('jpg','jpeg','gif','png'));
            foreach($images as $image){
                $path_info = pathinfo($image);
                $basename = $path_info['basename'];
                $filename = $path_info['filename'];
                if($filename == $id_image || $basename == $id_image){
                    $fileOut = $image;
                }
            }
        } 
        if(isset($_GET['w']) && isset($_GET['h'])){
            $this->imageresize->fromFile($fileOut)->resize($_GET['w'],$_GET['h'])->toScreen();
        }else if(isset($_GET['w'])){
            $this->imageresize->fromFile($fileOut)->resize($_GET['w'])->toScreen();
        }else if(isset($_GET['h'])){
            $this->imageresize->fromFile($fileOut)->resize(false,$_GET['h'])->toScreen();
        }else{
            $this->render->image($fileOut);
        }
    }

    function getBase64(){
        $id_image = subsegment(-1);
        $base_path = explode('/', str_replace('://','',base_url));
        $path = subsegment(count($base_path)+1,-1);
        $path = 'application/images/'.$path;
        if (file_exists($path."/default.png")) {
            $fileOut = $path."/default.png";
        }else{
            $fileOut = "application/images/default.png";
        }
        if (is_dir($path)){
            $images = load_recursive($path, 0, array('jpg','jpeg','gif','png'));
            foreach($images as $image){
                $path_info = pathinfo($image);
                $basename = $path_info['basename'];
                $filename = $path_info['filename'];
                if($filename == $id_image || $basename == $id_image){
                    $fileOut = $image;
                }
            }
        }
        $type = pathinfo($fileOut, PATHINFO_EXTENSION);
        $img = file_get_contents($fileOut);
        $data['base64'] = 'data:image/' . $type . ';base64,' . base64_encode($img);
        $this->render->json($data);
    }
}    
?>