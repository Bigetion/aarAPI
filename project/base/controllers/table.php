<?php if (!defined('INDEX')) {
    exit('No direct script access allowed');
}

class table extends Controller
{

    public function getTableViewOptions()
    {
        $post_data = $this->render->json_post();
        $name = $post_data['name'];
        $data = null;
        if (file_exists('project/base/config/table-view/' . id_role . '/' . $name . '.json')) {
            $data = json_decode(file_get_contents('project/base/config/table-view/' . id_role . '/' . $name . '.json'), true);
        } else if (file_exists('project/base/config/table-view/' . $name . '.json')) {
            $data = json_decode(file_get_contents('project/base/config/table-view/' . $name . '.json'), true);
        }
        $this->render->json($data);
    }

    public function getData()
    {
        $post_data = $this->render->json_post();
        $name = $post_data['name'];
        $json_data = null;
        if (file_exists('project/base/config/table-view/' . id_role . '/' . $name . '.json')) {
            $json_data = json_decode(file_get_contents('project/base/config/table-view/' . id_role . '/' . $name . '.json'), true);
        } else if (file_exists('project/base/config/table-view/' . $name . '.json')) {
            $json_data = json_decode(file_get_contents('project/base/config/table-view/' . $name . '.json'), true);
        }
        if (is_string($json_data['query'])) {
            $query = $json_data['query'];
            $total_rows_query = $json_data['total_rows_query'];
            $where = "";
            if (isset($post_data['where'])) {
                $where = $post_data['where'];
            }

            if (is_array($where)) {
                $where_key = array();
                $where_value = array();
                foreach ($where as $wk => $wv) {
                    $where_key[] = '$' . $wk;
                    $where_value[] = $wv;
                }
                $query = str_replace($where_key, $where_value, $query);
                $total_rows_query = str_replace($where_key, $where_value, $total_rows_query);
                $where = "";
            }
            $data['total_rows'] = 0;
            $data['data'] = $this->db->query($query . " " . $where);
            if ($data['data']) {
                $data['data'] = $data['data']->fetchAll(PDO::FETCH_ASSOC);
                $data['total_rows'] = $this->db->query($total_rows_query . " " . $where);
                $data['total_rows'] = $data['total_rows']->fetchAll(PDO::FETCH_ASSOC);
                if (count($data['total_rows']) > 0) {
                    if (strpos(strtolower($total_rows_query), 'group by') !== false) {
                        $data['total_rows'] = count($data['total_rows']);
                    } else {
                        if (isset($data['total_rows'][0]['total_rows'])) {
                            $data['total_rows'] = (int) $data['total_rows'][0]['total_rows'];
                        }
                    }
                }
            } else {
                $data['data'] = array();
            }

        } else {
            $json_data = $json_data['query'];
            $table = $json_data['table'];
            $column = $json_data['column'];
            $where = array();
            if (isset($post_data['where'])) {
                $where = $post_data['where'];
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
