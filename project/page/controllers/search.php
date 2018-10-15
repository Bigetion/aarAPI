<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class search extends Controller {
  function index() {
    $post_data = $this->render->json_post();
		$data['data'] = array();
		$data['totalRows'] = 0;
		$storeName = $post_data['store'];
		if(is_dir('application/db/'.$storeName)) {
      $where = $post_data['where'];
      $data['data'] = $this->sleekdb->select($storeName, [], $where);
      $data['totalRows'] = $this->sleekdb->totalRows();
      if(isset($post_data['limit'])) {
        $limit = $post_data['limit'];
        $where[] = ["limit" => $limit];
        $data['data'] = $this->sleekdb->select($storeName, [], $where);
      }
      if(isset($post_data['sortBy'])) {
        $sortBy = $post_data['sortBy'];
        $where[] = ["sortBy" => $sortBy];
        $data['data'] = $this->sleekdb->select($storeName, [], $where);
      }	
		}
		$this->render->json($data);
  }
}