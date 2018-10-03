<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class posts extends Controller {

	function getData(){
		$this->sleekdb->setStore('posts');
		$data['posts'] = $this->sleekdb->store->fetch();
		$this->render->json($data);
	}

	function submitAdd() {
		$post_data = $this->render->json_post();
		$this->sleekdb->setStore('posts');
		if($this->sleekdb->store->insert($post_data['data'])) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitDelete() {
		$post_data = $this->render->json_post();
		$this->sleekdb->setStore('posts');
		if($this->sleekdb->store->where( '_id', '=', $post_data['id'] )->delete()) {
			$this->sleekdb->store->deleteAllCache();
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}
}
?>