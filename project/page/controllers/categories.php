<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class categories extends Controller {

	function getData(){
		$post_data = $this->render->json_post();
		$data['categories'] = $this->sleekdb->select('categories');
		if(isset($post_data['where'])) {
			$where = $post_data['where'];
			if(isset($where['LIMIT'])) {
				$limit = $where['LIMIT'];
				$data['categories'] = $this->sleekdb->select('categories', [], [[
					"limit" => $limit
				]]);
			}
		}
		$data['totalRows'] = $this->sleekdb->totalRows();
		$this->render->json($data);
	}

	function submitAdd() {
		$post_data = $this->render->json_post();
		if($this->sleekdb->insert('categories', $post_data['data'])) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitEdit() {
		$post_data = $this->render->json_post();
		if($this->sleekdb->update('categories', $post_data['data'], [[
			"condition"=>["_id","=",$post_data['id']]
		]])) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitDelete() {
		$post_data = $this->render->json_post();
		$this->sleekdb->delete('categories', [[
			"condition" => ["_id","in",$post_data['id']]
		]]);
		$this->set->success_message(true);
	}
}
?>