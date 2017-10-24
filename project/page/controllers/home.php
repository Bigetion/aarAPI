<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class home extends Controller {

	function index(){
		$post_data = $this->render->json_post();
		$this->gump->validation_rules(array(
			'username'    => 'required|alpha_numeric|max_len,100|min_len,6',
			'password'    => 'required|max_len,100|min_len,6',
			'email'       => 'required|valid_email',
			'gender'      => 'required|exact_len,1|contains,m f',
			'credit_card' => 'required|valid_cc'
		));

		$this->gump->filter_rules(array(
			'username' => 'trim|sanitize_string',
			'password' => 'trim',
			'email'    => 'trim|sanitize_email',
			'gender'   => 'trim',
			'bio'	   => 'noise_words'
		));

		$this->gump->run_validation($post_data);
	}
}
?>