<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class categories extends Controller {

	function getData(){
		$data['categories'] = $this->sleekdb->select('categories');
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
		$this->sleekdb->setStore('categories');
		if($this->sleekdb->update($post_data['data'], [[
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