<?php if (!defined('INDEX')) exit('No direct script access allowed');

class table extends Controller
{
    public function getTableViewOptions()
    {
        $post_data = $this->render->json_post();
        $name = $post_data['name'];
        $data = $this->loadTableConfig($name);
        $this->render->json($data);
    }

    public function getData()
    {
        $post_data = $this->render->json_post();
        $name = $post_data['name'];
        $json_data = $this->loadTableConfig($name);

        $response = [
            'data' => [],
            'total_rows' => 0,
            'error' => null,
            'log' => []
        ];

        if (!$json_data || !isset($json_data['query'])) {
            $response['error'] = 'Query config not found or malformed.';
            return $this->render->json($response);
        }

        $queryConfig = $json_data['query'];
        $where = $post_data['where'] ?? [];

        if (is_string($queryConfig)) {
            $response = $this->handleRawQuery($queryConfig, $json_data['total_rows_query'], $where);
        } else {
            $response = $this->handleStructuredQuery($queryConfig, $where);

            if (isset($queryConfig['sub_query']) && is_array($queryConfig['sub_query'])) {
                $response['data'] = $this->handleSubQueries($response['data'], $queryConfig['sub_query']);
            }
        }

        $response['error'] = $this->db->error();
        $response['log'] = $this->db->log();
        $this->render->json($response);
    }

    private function loadTableConfig($name)
    {
        $paths = [
            "project/base/config/table-view/" . id_role . "/$name.json",
            "project/base/config/table-view/$name.json"
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $json = json_decode(file_get_contents($path), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $json;
                }
            }
        }

        return null;
    }

    private function handleRawQuery($query, $total_query, $where)
    {
        $response = ['data' => [], 'total_rows' => 0];

        if (is_array($where)) {
            foreach ($where as $k => $v) {
                $query = str_replace('$' . $k, $v, $query);
                $total_query = str_replace('$' . $k, $v, $total_query);
            }
        }

        $data = $this->db->query($query);
        if ($data) {
            $response['data'] = $data->fetchAll(PDO::FETCH_ASSOC);
        }

        $total = $this->db->query($total_query);
        if ($total) {
            $rows = $total->fetchAll(PDO::FETCH_ASSOC);
            if (strpos(strtolower($total_query), 'group by') !== false) {
                $response['total_rows'] = count($rows);
            } elseif (isset($rows[0]['total_rows'])) {
                $response['total_rows'] = (int) $rows[0]['total_rows'];
            }
        }

        return $response;
    }

    private function handleStructuredQuery($query, $where)
    {
        $response = ['data' => [], 'total_rows' => 0];
        $table = $query['table'];
        $column = $query['column'];
        $join = $query['join'] ?? null;

        if (isset($query['default_order']) && !isset($where['ORDER'])) {
            $where['ORDER'] = $query['default_order'];
        }

        $count_where = $where;
        unset($count_where['ORDER'], $count_where['LIMIT']);

        if (isset($where['GROUP'])) {
            if ($join) {
                $groupData = $this->db->select($table, $join, $column, $count_where);
            } else {
                $groupData = $this->db->select($table, $column, $count_where);
            }
            $response['total_rows'] = count($groupData);
        } else {
            $response['total_rows'] = $join
                ? $this->db->count($table, $join, $column[0], $count_where)
                : $this->db->count($table, $column[0], $count_where);
        }

        $response['data'] = $join
            ? $this->db->select($table, $join, $column, $where)
            : $this->db->select($table, $column, $where);

        return $response;
    }

    private function handleSubQueries($mainData, $subQueries)
    {
        foreach ($subQueries as $sub) {
            $reference_column = $sub['reference_column'];
            $foreign_key = $sub['foreign_key'];
            $reference_ids = array_column($mainData, $reference_column);

            $where = [$foreign_key => $reference_ids];

            $columns = $sub['column'];

            $join = isset($sub['join']) ? $sub['join'] : null;
            $rows = $join
                ? $this->db->select($sub['table'], $join, $columns, ["AND" => $where])
                : $this->db->select($sub['table'], $columns, ["AND" => $where]);

            $grouped = [];
            foreach ($rows as $row) {
                $grouped[$row[$foreign_key]][] = $row;
            }

            foreach ($mainData as &$item) {
                $item[$sub['column_id']] = isset($grouped[$item[$reference_column]]) ? $grouped[$item[$reference_column]] : [];
            }
        }

        return $mainData;
    }
}
