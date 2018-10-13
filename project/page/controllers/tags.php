<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class tags extends Controller {

	function getData(){
        $post_data = $this->render->json_post();
		$data['tags'] = $this->sleekdb->select('tags');
		if(isset($post_data['where'])) {
			$where = $post_data['where'];
			if(isset($where['LIMIT'])) {
				$limit = $where['LIMIT'];
				$data['tags'] = $this->sleekdb->select('tags', [], [[
					"limit" => $limit
				]]);
			}
		}
		$data['totalRows'] = $this->sleekdb->totalRows();
		$this->render->json($data);
	}

	function submitAdd() {
		$post_data = $this->render->json_post();
		if($this->sleekdb->insert('tags', $post_data['data'])) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitEdit() {
		$post_data = $this->render->json_post();
		if($this->sleekdb->update('tags', $post_data['data'], [[
			"condition"=>["_id","=",$post_data['id']]
		]])) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitDelete() {
		$post_data = $this->render->json_post();
		$this->sleekdb->delete('tags', [[
			"condition" => ["_id","in",$post_data['id']]
		]]);
		$this->set->success_message(true);
	}
}
?>