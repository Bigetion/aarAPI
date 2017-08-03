<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class image extends Main {
    function __construct() {
        
    }

    private function decodeBase64Image($base64){
		$a = str_replace(';base64,', '',strstr($base64, ';base64,'));
		$b = base64_decode($a);
		return $b;
	}

    private function getExtension ($mime_type){
        $extensions = array(
                        'image/jpeg' => 'jpeg',
                        'image/gif' => 'gif',
                        'image/png' => 'png',
                        'image/bmp' => 'bmp'
                    );
        return $extensions[$mime_type];
    }

    function upload(){
        $post_data = $this->render->json_post();
        $path = $post_data['path'];
        $images = $post_data['images'];
        $this->dir->create_dir('application/images/'.$path);

        $type = array();
        foreach($images as $image){
            $imgdata = $this->decodeBase64Image($image['src']);
            $f = finfo_open();
            $mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
            $extension = $this->getExtension($mime_type);
            file_put_contents('application/images/'.$path.'/'.$image['id'].'.'.$extension, $imgdata);
        }
        $this->render->json($type);
    }

    function get(){
        $id_image = subsegment(-1);
        $path = subsegment(4,-1);
        $path = 'application/images/'.$path;
        $fileOut = "application/images/default.png";
        if (is_dir($path)){
            $images = load_recursive($path, 0, array('jpg','jpeg','gif','png'));
            foreach($images as $image){
                $path_info = pathinfo($image);
                $basename = $path_info['basename'];
                $filename = $path_info['filename'];
                if($filename == $id_image){
                    $fileOut = $image;
                }
            }
        } 
        $this->render->image($fileOut);
    }
}    
?>