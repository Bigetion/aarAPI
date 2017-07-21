<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class URI {
	function __construct()
	{	
		$main_config = include('application/config/config.php');
        foreach ($main_config as $key => $value) {
            define($key, $value);
        }
		$database_config = include('application/config/database.php');
        foreach ($database_config as $key => $value) {
            define($key, $value);
        }
	}
	
    function segment($nomor){
		$db = & load_class('DB');		
		$uri_base = explode('?', $_SERVER['REQUEST_URI']);
		$uri_link = explode('/',$uri_base[0]);
		$uri_new = $uri_base[0];
		
		$ext = array(".html", ".aspx", ".asp");
		
		$tabel = $db->get_table();
		if(in_array('short_link', $tabel)){
			for($i=0;$i<count($uri_link);$i++){
				$link = $db->query("select * from short_link where short_link='".str_replace($ext, '', $uri_link[$i])."'")->fetchAll();

				if(count($link)>0) {
					$link = $link[0];
					$uri_new = str_replace($uri_link[$i],$link["link"],$uri_new);
				}
			}
		}
		
		$data = explode('/', $uri_new); $c=4;
		if(strpos(base_url,"http://")===false) $c=2;
		
		if(count(explode('/',base_url))==$c) $nomor = $nomor-1;
		
		if ($nomor > count($data) - 1) return "";
		else return str_replace($ext, '', $data[$nomor]);		
	}
}

?>