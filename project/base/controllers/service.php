<?php if (!defined('INDEX')) {
    exit('No direct script access allowed');
}

class service extends Controller
{

    public $uuid = false;

    public function getQueryServiceOptions()
    {
        $post_data = $this->render->json_post();
        $name = $post_data['name'];

        if (file_exists('project/base/config/query-service/' . id_role . '/' . $name . '.json')) {
            $data = json_decode(file_get_contents('project/base/config/query-service/' . id_role . '/' . $name . '.json'), true);
        } else if (file_exists('project/base/config/query-service/' . $name . '.json')) {
            $data = json_decode(file_get_contents('project/base/config/query-service/' . $name . '.json'), true);
        }
        $this->render->json($data);
    }

    private function getInputData($my_data, $my_fields)
    {
        $input_data = array();
        foreach ($my_fields as $field) {
            if (isset($my_data[$field['id']])) {
                $input_data[$field['id']] = $my_data[$field['id']];
                if (isset($field['type'])) {
                    if ($field['type'] == 'password') {
                        $input_data[$field['id']] = password_hash($input_data[$field['id']], 1);
                    }
                    if ($field['type'] == 'uuid') {
                        $this->uuid = md5(uniqid());
                        $input_data[$field['id']] = $this->uuid;
                    }
                }
            }
        }
        return $input_data;
    }

    private function getDataByJson($query, $where)
    {
        if (is_string($query)) {
            $data = $this->db->query($query . " " . $where);
            if ($data) {
                $data = $data->fetchAll(PDO::FETCH_ASSOC);
            }

        } else {
            $table = $query['table'];
            $column = $query['column'];

            if (isset($query['join'])) {
                $join = $query['join'];
                if (isset($query['type'])) {
                    $type = $query['type'];
                    switch ($type) {
                        case 'sum':
                            $data = $this->db->sum($table, $join, $column, $where);
                            break;
                        default:
                            $data = $this->db->select($table, $join, $column, $where);
                    }
                } else {
                    $data = $this->db->select($table, $join, $column, $where);
                }
            } else {
                if (isset($query['type'])) {
                    $type = $query['type'];
                    switch ($type) {
                        case 'sum':
                            $data = $this->db->sum($table, $column, $where);
                            break;
                        default:
                            $data = $this->db->select($table, $column, $where);
                    }
                } else {
                    $data = $this->db->select($table, $column, $where);
                }
            }
        }
        return $data;
    }

    private function replaceQueryStringVariable($query, $where, $json_data)
    {
        $new_query = $query;
        foreach ($json_data as $jk => $jv) {
            if ($jk != "query") {
                if (array_key_exists($jk, $where)) {
                    $new_query = str_replace('$' . $jk, $jv[0], $new_query);
                } else {
                    $new_query = str_replace('$' . $jk, $jv[1], $new_query);
                }
            }
        }

        $where_key = array();
        $where_value = array();
        foreach ($where as $wk => $wv) {
            $where_key[] = '$' . $wk;
            $where_value[] = $wv;
        }
        return str_replace($where_key, $where_value, $new_query);
    }

    private function getDataResult($post_data)
    {
        $name = $post_data['name'];
        $json_data = false;

        if (file_exists('project/base/config/query-service/' . id_role . '/' . $name . '.json')) {
            $json_data = json_decode(file_get_contents('project/base/config/query-service/' . id_role . '/' . $name . '.json'), true);
        } else if (file_exists('project/base/config/query-service/' . $name . '.json')) {
            $json_data = json_decode(file_get_contents('project/base/config/query-service/' . $name . '.json'), true);
        }

        $data['data'] = array();

        if ($json_data) {
            if (is_array($json_data['query'])) {
                $array_keys = array_keys($json_data['query']);
                if ($array_keys[0] === 0) {
                    $query = $json_data['query'];
                    foreach ($query as $key => $q) {
                        if (is_string($q)) {
                            $where = "";
                            if (isset($post_data['where'])) {
                                $where = $post_data['where'][$key];
                                if (is_array($where)) {
                                    $q = $this->replaceQueryStringVariable($q, $where, $json_data);
                                    $where = "";
                                }
                            }
                            $data['data'][] = $this->getDataByJson($q, $where);
                        } else {
                            $where = array();
                            if (isset($post_data['where'])) {
                                if (isset($post_data['where'][$key])) {
                                    $where = $post_data['where'][$key];
                                    if (isset($q['default_order']) && !isset($where["ORDER"])) {
                                        $where["ORDER"] = $q['default_order'];
                                    }
                                }
                            }
                            $data['data'][] = $this->getDataByJson($q, $where);
                        }
                        $data['error'][$key] = $this->db->error();
                    }
                } else {
                    $where = array();
                    if (isset($post_data['where'])) {
                        $where = $post_data['where'];
                    }
                    if (isset($json_data['default_order']) && !isset($where["ORDER"])) {
                        $where["ORDER"] = $json_data['default_order'];
                    }
                    $data['data'] = $this->getDataByJson($json_data['query'], $where);
                }
            } else {
                $q = $json_data['query'];
                $where = "";
                if (isset($post_data['where'])) {
                    $where = $post_data['where'];
                    if (is_array($where)) {
                        $q = $this->replaceQueryStringVariable($q, $where, $json_data);
                        $where = "";
                    }
                }
                $data['data'] = $this->getDataByJson($q, $where);
                $data['error'] = $this->db->error();
            }
            if ($data['data'] === false) {
                $data['data'] = array();
            }
            $data['log'] = $this->db->log();
        }
        return $data;
    }

    public function getData()
    {
        $post_data = $this->render->json_post();
        $data = array();
        $data = $this->getDataResult($post_data);
        $this->render->json($data);
    }

    public function getDataArray()
    {
        $post_data = $this->render->json_post();
        $queries = $post_data['queries'];
        $data = array();
        foreach ($queries as $key => $query) {
            $data[] = $this->getDataResult($query);
            $data[$key]['log'] = $data[$key]['log'][$key];
        }
        $this->render->json($data);
    }

    private function replaceInsertedId($data, $id) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->replaceInsertedId($value, $id);
            } elseif (is_string($value)) {
                $data[$key] = str_replace('__ID__', $id, $value);
            }
        }
        return $data;
    }

    private function afterSuccess($id, $post_data) {
        if (isset($post_data[0])) {
            $responses = [];
            foreach ($post_data as $item) {
                $replaced = $this->replaceInsertedId($item, $id);
                $responses[] = $this->getExecuteMutationResult($replaced);
            }
            return $responses;
        } else {
            $post_data = $this->replaceInsertedId($post_data, $id);
            return $this->getExecuteMutationResult($post_data);
        }
    }
    
    private function getExecuteMutationResult($post_data)
    {
        $name = $post_data['name'];
        $type = $post_data['type'];

        $json_data = json_decode(file_get_contents('project/base/config/mutation-service/' . $name . '.json'), true);

        $table = $json_data['table'];
        $primary_key = $json_data['primary_key'];
        $data = array();

        $this->uuid = false;
        $where = [$primary_key => '-1'];
        if (isset($post_data['id'])) {
            $where = [$primary_key => $post_data['id']];
        } else if (isset($post_data['where'])) {
            $where = $post_data['where'];
        }
        if ($type == 'insert') {
            if (in_array(id_role, $json_data['roles']['insert'])) {
                if (is_array($post_data['data'])) {
                    $array_keys = array_keys($post_data['data']);
                    if ($array_keys[0] === 0) {
                        $input_data = array();
                        foreach ($array_keys as $key) {
                            $input_data[] = $this->getInputData($post_data['data'][$key], $json_data['fields']);
                        }
                        if ($this->db->insert($table, $input_data)) {
                            $data['success_message'] = true;
                            
                            $id = $this->db->id();

                            if (isset($post_data['after_success'])) {
                                $data['after_insert_response'] = $this->afterSuccess($id, $post_data['after_success']);
                            }

                        }
                    } else {
                        $input_data = $this->getInputData($post_data['data'], $json_data['fields']);
                        if ($this->db->insert($table, $input_data)) {
                            $id = $this->db->id();
                            if ($this->uuid) {
                                $id = $this->uuid;
                            }
                            $data['id'] = $id;
                            $data['success_message'] = true;

                            if (isset($post_data['after_success'])) {
                                $data['after_insert_response'] = $this->afterSuccess($id, $post_data['after_success']);
                            }
                        }
                    }
                }
            }
        } elseif ($type == 'update') {
            $input_data = $this->getInputData($post_data['data'], $json_data['fields']);
            if (in_array(id_role, $json_data['roles']['update'])) {
                if ($this->db->update($table, $input_data, $where)) {
                    $data['success_message'] = true;

                    if (isset($post_data['after_success'])) {
                        $data['after_insert_response'] = $this->afterSuccess("-1", $post_data['after_success']);
                    }
                }
            }
        } elseif ($type == 'delete') {
            if (in_array(id_role, $json_data['roles']['delete'])) {
                if ($this->db->delete($table, $where)) {
                    $data['success_message'] = true;

                    if (isset($post_data['after_success'])) {
                        $data['after_insert_response'] = $this->afterSuccess("-1", $post_data['after_success']);
                    }
                }
            }
        }
        if (!isset($data['success_message'])) {
            $data['error'] = $this->db->error();
            $data['error_message'] = true;
        }
        $data['log'] = $this->db->log();
        return $data;
    }

    public function executeMutation()
    {
        $post_data = $this->render->json_post();
        $data = array();
        $data = $this->getExecuteMutationResult($post_data);
        if (count($data) === 0) {
            $data = array("error_message" => true);
        }
        $this->render->json($data);
    }

    public function executeMutationArray()
    {
        $post_data = $this->render->json_post();
        $mutations = $post_data['mutations'];
        $data = array();
        foreach ($mutations as $key => $mutation) {
            $data[] = $this->getExecuteMutationResult($mutation);
            $data[$key]['log'] = $data[$key]['log'][$key];
        }
        $this->render->json($data);
    }
}
