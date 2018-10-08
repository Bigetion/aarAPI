<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class categories extends Controller {

	function getData(){
		$this->sleekdb->setStore('categories');
		$data['categories'] = $this->sleekdb->store->fetch();
		$this->render->json($data);
	}

	function submitAdd() {
		$post_data = $this->render->json_post();
		$this->sleekdb->setStore('categories');
		if($this->sleekdb->store->insert($post_data['data'])) {
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitEdit() {
		$post_data = $this->render->json_post();
		$this->sleekdb->setStore('categories');
		if($this->sleekdb->store->where( '_id', '=', $post_data['id'] )->update($post_data['data'])) {
			$this->sleekdb->store->deleteAllCache();
			$this->set->success_message(true);
		}
		$this->set->error_message(true);
	}

	function submitDelete() {
        $post_data = $this->render->json_post();
        $this->sleekdb->setStore('categories');
        $array = $this->sleekdb->store;
        foreach($post_data['id'] as $id){
            $array = $array->where( '_id', '==', $id );
        }
        $array->delete();
        $this->sleekdb->store->deleteAllCache();
        $this->set->error_message(true);
	}
}
?>