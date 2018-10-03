<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class sleek extends Controller {
	function getData(){
		$post_data = $this->render->json_post();
		$name = $post_data['name'];
		$json_data = null;
		if(file_exists('project/base/config/sleek/query/'.id_role.'/'.$name.'.json')){
			$json_data = json_decode(file_get_contents('project/base/config/sleek/query/'.id_role.'/'.$name.'.json'), true);
		} else if(file_exists('project/base/config/sleek/query/'.$name.'.json')){
			$json_data = json_decode(file_get_contents('project/base/config/sleek/query/'.$name.'.json'), true);
		}
		$data= array();
		if(!empty($json_data)) {
			if(is_array($json_data['query'])){
				$array_keys = array_keys($json_data['query']);
				if($array_keys[0] === 0){
					$query = $json_data['query'];
					foreach($query as $key=>$q){
						$store = $q['store'];
						$keys = $q['keys'];
						$this->sleekdb->setStore($store);
						$data['data'][] =  $this->sleekdb->store->fetch($keys);
					}
				} else {
					$query = $json_data['query'];
					$store = $query['store'];
					$keys = $query['keys'];
					$this->sleekdb->setStore($store);
					$data['data'] =  $this->sleekdb->store->fetch($keys);
				}
			}
		}
		$this->render->json($data);
	}
}
?>