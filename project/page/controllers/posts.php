<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class posts extends Controller {

	function getData(){
		$post_data = $this->render->json_post();
		$data['posts'] = $this->sleekdb->select('posts');
		if(isset($post_data['where'])) {
			$where = $post_data['where'];
			if(isset($where['LIMIT'])) {
				$limit = $where['LIMIT'];
				$data['posts'] = $this->sleekdb->select('posts', [], [[
					"limit" => $limit
				]]);
			}
		}
		$data['totalRows'] = $this->sleekdb->totalRows();
		$this->render->json($data);
	}

	function getById() {
		$post_data = $this->render->json_post();
		$data = $this->sleekdb->select('posts', [], [[
			"condition" => ["_id","=", $post_data['id']]
		]]);
		$this->render->json($data);
	}

	function getByTitleSlug() {
		$post_data = $this->render->json_post();
		$data = $this->sleekdb->select('posts', [], [[
			"condition" => ["titleSlug","=", $post_data['slug']]
		]]);
		$this->render->json($data);
	}

	function submitAdd() {
		$post_data = $this->render->json_post();
		$data = array_merge(array("featuredImage" => base_url."image/get/featured"),$post_data['data']);
		if($this->sleekdb->insert('posts',$data)) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitEdit() {
		$post_data = $this->render->json_post();
		if($this->sleekdb->update('posts',$post_data['data'], [[
			"condition" => ["_id","=",$post_data['id']]
		]])) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitDelete() {
		$post_data = $this->render->json_post();
		if($this->sleekdb->delete('posts',[[
			"condition" => ["_id","=",$post_data['id']]
		]])) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}
}
?>