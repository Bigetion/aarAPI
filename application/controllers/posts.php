<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Posts extends Main {
    function __construct() {
        $this->auth->permission();
    }

    function getData(){
        $data['data'] = $this->db->select("blog_posts","*");
        $this->render->json($data);
    }

    function submitAdd(){
        $post_data = $this->render->json_post();
        $data = array(
            'post_title'     => $post_data['postTitle'],
            'post_content'   => $post_data['postContent'],
            'description'    => $post_data['description'],
        );
        if($this->db->insert("blog_posts", $data)){
            $id = $this->db->id();
            $this->set->success_message(true, array('id'=>$id));
        }
    }

    function submitEdit(){
        $post_data = $this->render->json_post();
        $data = array(
            'post_title'     => $post_data['postTitle'],
            'post_content'   => $post_data['postContent'],
            'description'    => $post_data['description'],
        );
        if($this->db->update("blog_posts", $data, ["id_post" => $post_data['idPost']])){
            $this->set->success_message(true);
        }
    }

    function submitDelete(){
        $post_data = $this->render->json_post();
        if($this->db->delete("blog_posts", ["id_post" => explode(',',$post_data['idPost'])])){
            $this->set->success_message(true);
        }
    }
}    
?>