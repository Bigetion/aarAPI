<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class tags extends Controller {

	function getData(){
		$this->sleekdb->setStore('tags');
		$data['tags'] = $this->sleekdb->store->fetch();
		$this->render->json($data);
	}

	function submitAdd() {
		$post_data = $this->render->json_post();
		$this->sleekdb->setStore('tags');
		if($this->sleekdb->store->insert($post_data['data'])) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitEdit() {
		$post_data = $this->render->json_post();
		$this->sleekdb->setStore('tags');
		if($this->sleekdb->store->where( '_id', '=', $post_data['id'] )->update($post_data['data'])) {
			$this->sleekdb->store->deleteAllCache();
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitDelete() {
		$post_data = $this->render->json_post();
		$this->sleekdb->setStore('tags');
		foreach($post_data['id'] as $id){
			$this->sleekdb->store->where( '_id', '=', $id )->delete();
		}
		$this->set->success_message(true);
	}
}
?>