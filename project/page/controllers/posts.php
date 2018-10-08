<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class posts extends Controller {

	function getData(){
		$post_data = $this->render->json_post();
		$this->sleekdb->setStore('posts');
		$data['posts'] = $this->sleekdb->store->fetch();

		$data['totalRows'] = count($data['posts']);
		if(isset($post_data['where'])) {
			$where = $post_data['where'];
			if(isset($where['LIMIT'])) {
				$limit = $where['LIMIT'];
				if(is_array($limit)) {
					$data['posts'] = $this->sleekdb->store->skip($limit[0])->limit($limit[1])->fetch();
				} else {
					$data['posts'] = $this->sleekdb->store->limit($limit)->fetch();
				}
			}
		}
		$this->render->json($data);
	}

	function submitAdd() {
		$post_data = $this->render->json_post();
		$this->sleekdb->setStore('posts');
		$data = array_unique(array_merge(array("featuredImage" => base_url."image/get/featured"),$post_data['data']));
		if($this->sleekdb->store->insert($data)) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitEdit() {
		$post_data = $this->render->json_post();
		$this->sleekdb->setStore('posts');
		if($this->sleekdb->store->where( '_id', '=', $post_data['id'] )->update($post_data['data'])) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitDelete() {
		$post_data = $this->render->json_post();
		$this->sleekdb->setStore('posts');
		if($this->sleekdb->store->where( '_id', '=', $post_data['id'] )->delete()) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}
}
?>