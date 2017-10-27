<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class image extends Controller {

	function getAllImage(){
    $post_data = $this->render->json_post();
    $path = 'application/images/editor';
		$this->dir->create_dir($path);
    $images = load_recursive($path, 0, array('jpg','jpeg','png'));

    $data['images'] = array();
    foreach($images as $image){
			$image = pathinfo($image);
      $data['images'][] = array(
				'url'	=> base_url.'image/get/editor/'.$image['basename'],
				'thumb'	=> base_url.'image/get/editor/'.$image['basename'],
				'tag'	=> $image['filename']
			);
    }
    $this->render->json($data['images']);
	}
	
	function uploadImage(){
		$allowedExts = array("jpeg", "jpg", "png");
		$temp = explode(".", $_FILES["image"]["name"]);
		
		$extension = end($temp);
		
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $_FILES["image"]["tmp_name"]);
		
		if ((($mime == "image/gif")
			|| ($mime == "image/jpeg")
			|| ($mime == "image/pjpeg")
			|| ($mime == "image/x-png")
			|| ($mime == "image/png"))
			&& in_array($extension, $allowedExts)) {
			$name = sha1(microtime()) . "." . $extension;
			move_uploaded_file($_FILES["image"]["tmp_name"], "application/images/editor/" . $name);
			$response = new StdClass;
			$response->link = base_url.'image/get/editor/'.$name;
			echo stripslashes(json_encode($response));
		}
	}

	function deleteImage(){
		$image = $_POST['src'];
		$path = 'application/images/editor/'.str_replace(base_url.'image/get/editor','',$image);
		unlink($path);
	}

}
?>