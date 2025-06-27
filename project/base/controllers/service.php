<?php if (!defined('INDEX')) exit('No direct script access allowed');

class service extends Controller
{
    public $uuid = false;

    // ================================
    // GET DATA SECTION
    // ================================

    public function getData()
    {
        $post_data = $this->render->json_post();
        $data = $this->getDataResult($post_data);
        $this->render->json($data);
    }

    private function getDataResult($post_data)
    {
        $name = $post_data['name'];
        $json_data = $this->loadQueryConfig($name);

        if (!$json_data || !isset($json_data['query'])) {
            return [
                'data' => [],
                'error' => 'Query config not found or malformed.'
            ];
        }

        if (is_array($json_data['query']) && isset($json_data['query'][0])) {
            return $this->handleQueryArray($json_data, $post_data);
        }

        return $this->handleQuerySingle($json_data, $post_data);
    }

    private function loadQueryConfig($name)
    {
        $paths = [
            'project/base/config/query-service/' . id_role . '/' . $name . '.json',
            'project/base/config/query-service/' . $name . '.json',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $json = json_decode(file_get_contents($path), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $json;
                }
            }
        }

        return false;
    }

    private function handleQueryArray($json_data, $post_data)
    {
        $result = ['data' => [], 'error' => [], 'log' => []];

        foreach ($json_data['query'] as $key => $query) {
            $where = $this->getWhereForKey($post_data, $key, $query);
            $data = $this->processQuery($query, $where, $json_data);

            $result['data'][] = $data;
            $result['error'][$key] = $this->db->error();
        }

        $result['log'] = $this->db->log();
        return $result;
    }

    private function handleQuerySingle($json_data, $post_data)
    {
        $query = $json_data['query'];
        $where = isset($post_data['where']) ? $post_data['where'] : [];

        if (isset($json_data['default_order']) && !isset($where["ORDER"])) {
            $where["ORDER"] = $json_data['default_order'];
        }

        $data = $this->processQuery($query, $where, $json_data);

        return [
            'data' => $data ?: [],
            'error' => $this->db->error(),
            'log' => $this->db->log(),
        ];
    }

    private function getWhereForKey($post_data, $key, $query)
    {
        $where = [];

        if (isset($post_data['where'][$key])) {
            $where = $post_data['where'][$key];

            if (is_array($where) && isset($query['default_order']) && !isset($where["ORDER"])) {
                $where["ORDER"] = $query['default_order'];
            }
        }

        return $where;
    }

    private function processQuery($query, $where, $json_data)
    {
        if (is_string($query)) {
            if (is_array($where)) {
                $query = $this->replaceQueryStringVariable($query, $where, $json_data);
                $where = "";
            }
            return $this->getDataByJson($query, $where);
        }

        return $this->getDataByJson($query, $where);
    }

    private function getDataByJson($query, $where)
    {
        if (is_string($query)) {
            $data = $this->db->query($query . " " . $where);
            return $data ? $data->fetchAll(PDO::FETCH_ASSOC) : [];
        }

        $table = $query['table'];
        $column = $query['column'];

        if (isset($query['join'])) {
            $join = $query['join'];
            $type = $query['type'] ?? 'select';

            return ($type === 'sum')
                ? $this->db->sum($table, $join, $column, $where)
                : $this->db->select($table, $join, $column, $where);
        } else {
            $type = $query['type'] ?? 'select';

            return ($type === 'sum')
                ? $this->db->sum($table, $column, $where)
                : $this->db->select($table, $column, $where);
        }
    }

    private function replaceQueryStringVariable($query, $where, $json_data)
    {
        foreach ($json_data as $key => $value) {
            if ($key !== "query") {
                $query = str_replace('$' . $key, isset($where[$key]) ? $value[0] : $value[1], $query);
            }
        }

        foreach ($where as $key => $value) {
            $query = str_replace('$' . $key, $value, $query);
        }

        return $query;
    }

    // ================================
    // MUTATION SECTION
    // ================================

    public function executeMutation()
    {
        $post_data = $this->render->json_post();
        $data = $this->runMutation($post_data);
        if (empty($data)) {
            $data = ["error_message" => true];
        }
        $this->render->json($data);
    }

    private function runMutation($post_data)
    {
        $name = $post_data['name'];
        $type = $post_data['type'];

        $json_data = $this->loadMutationConfig($name);
        if (!$json_data) return ["error" => "Mutation config not found"];

        if (!$this->checkMutationPermission($type, $json_data)) {
            return ["error" => "Permission denied"];
        }

        switch ($type) {
            case 'insert':
                return $this->handleInsertMutation($post_data, $json_data);
            case 'update':
                return $this->handleUpdateMutation($post_data, $json_data);
            case 'delete':
                return $this->handleDeleteMutation($post_data, $json_data);
            default:
                return ["error" => "Unsupported mutation type"];
        }
    }

    private function loadMutationConfig($name)
    {
        $file = 'project/base/config/mutation-service/' . $name . '.json';
        if (file_exists($file)) {
            $json = json_decode(file_get_contents($file), true);
            return (json_last_error() === JSON_ERROR_NONE) ? $json : false;
        }
        return false;
    }

    private function checkMutationPermission($type, $json_data)
    {
        return isset($json_data['roles'][$type]) && in_array(id_role, $json_data['roles'][$type]);
    }

    private function handleInsertMutation($post_data, $json_data)
    {
        $table = $json_data['table'];
        $fields = $json_data['fields'];
        $data = [];

        $array_keys = array_keys($post_data['data']);
        $multi_insert = isset($array_keys[0]) && is_numeric($array_keys[0]);

        if ($multi_insert) {
            $input_data = array_map(function ($item) use ($fields) {
                return $this->getInputData($item, $fields);
            }, $post_data['data']);

            if ($this->db->insert($table, $input_data)) {
                $data['success_message'] = true;
                $id = $this->db->id();
                if (isset($post_data['after_success'])) {
                    $data['after_insert_response'] = $this->afterSuccess($id, $post_data['after_success']);
                }
            }
        } else {
            $input_data = $this->getInputData($post_data['data'], $fields);
            if ($this->db->insert($table, $input_data)) {
                $id = $this->uuid ?: $this->db->id();
                $data['id'] = $id;
                $data['success_message'] = true;
                if (isset($post_data['after_success'])) {
                    $data['after_insert_response'] = $this->afterSuccess($id, $post_data['after_success']);
                }
            }
        }

        $data['log'] = $this->db->log();
        return $data;
    }

    private function handleUpdateMutation($post_data, $json_data)
    {
        $table = $json_data['table'];
        $fields = $json_data['fields'];

        $where = $post_data['where'] ?? [$json_data['primary_key'] => $post_data['id']];
        $input_data = $this->getInputData($post_data['data'], $fields);

        $data = [];
        if ($this->db->update($table, $input_data, $where)) {
            $data['success_message'] = true;
            if (isset($post_data['after_success'])) {
                $data['after_insert_response'] = $this->afterSuccess('-1', $post_data['after_success']);
            }
        } else {
            $data['error_message'] = true;
        }

        $data['log'] = $this->db->log();
        return $data;
    }

    private function handleDeleteMutation($post_data, $json_data)
    {
        $table = $json_data['table'];
        $where = $post_data['where'] ?? [$json_data['primary_key'] => $post_data['id']];
        $data = [];

        if ($this->db->delete($table, $where)) {
            $data['success_message'] = true;
            if (isset($post_data['after_success'])) {
                $data['after_insert_response'] = $this->afterSuccess('-1', $post_data['after_success']);
            }
        } else {
            $data['error_message'] = true;
        }

        $data['log'] = $this->db->log();
        return $data;
    }

    private function getInputData($my_data, $my_fields)
    {
        $input_data = array();

        foreach ($my_fields as $field) {
            $id = $field['id'];
            $value = isset($my_data[$id]) ? $my_data[$id] : null;

            if (!isset($field['required']) || $field['required'] !== true || $value !== null) {
                $input_data[$id] = $value;

                if (isset($field['type'])) {
                    switch ($field['type']) {
                        case 'password':
                            $input_data[$id] = password_hash($value, PASSWORD_BCRYPT);
                            break;
                        case 'uuid':
                            $this->uuid = md5(uniqid());
                            $input_data[$id] = $this->uuid;
                            break;
                    }
                }
            }
        }

        return $input_data;
    }

    private function afterSuccess($id, $post_data)
    {
        if (isset($post_data[0])) {
            return array_map(function ($item) use ($id) {
                return $this->runMutation($this->replaceInsertedId($item, $id));
            }, $post_data);
        }

        return $this->runMutation($this->replaceInsertedId($post_data, $id));
    }

    private function replaceInsertedId($data, $id)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->replaceInsertedId($value, $id);
            } elseif (is_string($value)) {
                $data[$key] = str_replace('__ID__', $id, $value);
            }
        }
        return $data;
    }
}
