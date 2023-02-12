<?php if (!defined('INDEX')) {
    exit('No direct script access allowed');
}

class select extends Controller
{

    public function getSelectViewOptions()
    {
        $post_data = $this->render->json_post();
        $name = $post_data['name'];
        if (file_exists('project/base/config/select-view/' . id_role . '/' . $name . '.json')) {
            $data = json_decode(file_get_contents('project/base/config/select-view/' . id_role . '/' . $name . '.json'), true);
        } else if (file_exists('project/base/config/select-view/' . $name . '.json')) {
            $data = json_decode(file_get_contents('project/base/config/select-view/' . $name . '.json'), true);
        }
        $this->render->json($data);
    }

    public function getData()
    {
        $post_data = $this->render->json_post();
        $name = $post_data['name'];
        if (file_exists('project/base/config/select-view/' . id_role . '/' . $name . '.json')) {
            $json_data = json_decode(file_get_contents('project/base/config/select-view/' . id_role . '/' . $name . '.json'), true);
        } else if (file_exists('project/base/config/select-view/' . $name . '.json')) {
            $json_data = json_decode(file_get_contents('project/base/config/select-view/' . $name . '.json'), true);
        }
        if (is_string($json_data['query'])) {
            $query = $json_data['query'];
            $data['total_rows'] = 0;
            $data['data'] = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $json_data = $json_data['query'];
            $table = $json_data['table'];
            $column = $json_data['column'];
            $where = array();
            if (isset($post_data['where'])) {
                $where = $post_data['where'];
            }
            if (isset($json_data['default_order']) && !isset($where["ORDER"])) {
                $where["ORDER"] = $json_data['default_order'];
            }

            if (isset($json_data['join'])) {
                $join = $json_data['join'];
                $count_where = $where;
                unset($count_where["ORDER"]);
                unset($count_where["LIMIT"]);
                $data['total_rows'] = $this->db->count($table, $join, $column[0], $count_where);
                if (isset($where['GROUP'])) {
                    $data['total_rows'] = count($this->db->select($table, $join, $column, $count_where));
                }
                $data['data'] = $this->db->select($table, $join, $column, $where);
            } else {
                $count_where = $where;
                unset($count_where["ORDER"]);
                unset($count_where["LIMIT"]);
                $data['total_rows'] = $this->db->count($table, $column[0], $count_where);
                if (isset($where['GROUP'])) {
                    $data['total_rows'] = count($this->db->select($table, $column, $count_where));
                }
                $data['data'] = $this->db->select($table, $column, $where);
            }
        }
        if ($data['data'] === false) {
            $data['data'] = array();
        }
        $data['error'] = $this->db->error();
        $data['log'] = $this->db->log();
        $this->render->json($data);
    }
}
