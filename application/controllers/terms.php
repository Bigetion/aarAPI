<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Terms extends Main {
    function __construct() {
        $this->auth->permission();
    }

    function getData(){
        $post_data = $this->render->json_post();
        $type = 'category';
        if(isset($post_data['type'])){
            $type = $post_data['type'];
        }
        $data['data'] = $this->db->select("blog_taxonomy",[
            '[>]blog_terms' => 'term_id'
        ],[
            'blog_terms.term_id(id)',
            'blog_terms.name(text)'
        ],[
            'blog_taxonomy.taxonomy' => $type
        ]);
        $this->render->json($data);
    }

    function submitAdd(){
        $post_data = $this->render->json_post();
        $data = array(
            'id_category'     => $post_data['idCategory'],
            'category_name'   => $post_data['categoryName'],
        );
        if($this->db->insert("blog_categories", $data)){
            $id = $this->db->id();
            $this->set->success_message(true, array('id'=>$id));
        }
    }

    function submitEdit(){
        $post_data = $this->render->json_post();
        $data = array(
            'id_category'     => $post_data['idCategory'],
            'category_name'   => $post_data['categoryName'],
        );
        if($this->db->update("blog_categories", $data, ["id_role" => $post_data['idCategoryOld']])){
            $this->set->success_message(true);
        }
    }

    function submitDelete(){
        $post_data = $this->render->json_post();
        if($this->db->delete("blog_categories", ["id_category" => $post_data['idCategory']])){
            $this->set->success_message(true);
        }
    }
}    
?>