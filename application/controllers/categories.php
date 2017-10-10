<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Categories extends Main {
    function __construct() {
        $this->auth->permission();
    }

    function getData(){
        $data['data'] = $this->db->select("blog_taxonomy",[
            '[><]blog_terms' => 'term_id'
        ],[
            'blog_terms.term_id(id)',
            'blog_terms.name(text)'
        ],[
            'blog_taxonomy.taxonomy'=>'category'
        ]);
        $data['log'] = $this->db->log();
        $this->render->json($data);
    }

    function submitAdd(){
        
    }

    function submitEdit(){
        
    }

    function submitDelete(){
       
    }
}    
?>