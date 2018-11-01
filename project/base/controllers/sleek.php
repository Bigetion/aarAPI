<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class sleek extends Controller {
	function getData(){
		$post_data = $this->render->json_post();
		$name = $post_data['name'];
		$json_data = null;
		if(file_exists('project/base/config/sleek-query/'.id_role.'/'.$name.'.json')){
			$json_data = json_decode(file_get_contents('project/base/config/sleek-query/'.id_role.'/'.$name.'.json'), true);
		} else if(file_exists('project/base/config/sleek-query/'.$name.'.json')){
			$json_data = json_decode(file_get_contents('project/base/config/sleek-query/'.$name.'.json'), true);
		}
		$data= array();
		
		if(!empty($json_data)) {
			if(isset($json_data['query'])){
				$query = $json_data['query'];
				foreach($query as $key=>$q){
					$store = $q['store'];
					$keys = $q['keys'];
					$where = array();
					
					$tmpData = $this->sleekdb->select($store, $keys, $where);
					$tmpTotalRows = $this->sleekdb->totalRows();
					
					if(isset($post_data['options'])){
						if(isset($post_data['options'][$key])){
							$options = $post_data['options'][$key];
							if(isset($options['where'])) {
								$where = $options['where'];
							}
							$tmpData = $this->sleekdb->select($store, $keys, $where);
							$tmpTotalRows = $this->sleekdb->totalRows();
							if(isset($options['limit'])) {
								$limit = $options['limit'];
								$where[] = ["limit" => $limit];
							}
							if(isset($options['sortBy'])) {
								$sortBy = $options['sortBy'];
								$where[] = ["sortBy" => $sortBy];
							}
							$tmpData = $this->sleekdb->select($store, $keys, $where);
						};
					}
					$data['data'][] = array("rows" => $tmpData, "total" => $tmpTotalRows);
				}
			}
		}
		$this->render->json($data);
	}
}
?>