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

    private function handleSubQueries(array $mainData, array $subQueries): array
    {
        if (empty($mainData)) return $mainData;

        $mainIds = array_column($mainData, 'id');
        $indexed = [];

        foreach ($subQueries as $sub) {
            if (!isset($sub['key'], $sub['table'], $sub['foreign_key'], $sub['target_key'], $sub['column'])) {
                continue;
            }

            $where = [$sub['foreign_key'] => $mainIds];
            $subData = isset($sub['join'])
                ? $this->db->select($sub['table'], $sub['join'], $sub['column'], $where)
                : $this->db->select($sub['table'], $sub['column'], $where);

            $grouped = [];
            foreach ($subData as $item) {
                $grouped[$item[$sub['foreign_key']]][] = $item;
            }

            foreach ($mainData as &$row) {
                $id = $row[$sub['target_key']] ?? null;
                $row[$sub['key']] = $grouped[$id] ?? [];
            }
        }

        return $mainData;
    }
}
