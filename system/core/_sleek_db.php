<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

require_once 'vendor/sleekdb/src/SleekDB.php';

class sleekdb {
  private $totalRows_ = 0;
  public function setStore($store) {
    $this->store = new \SleekDB\SleekDB($store);
    $data = $this->store->fetch();
    $this->jsonq = new \Nahid\JsonQ\Jsonq();
    $this->jsonq->collect($data);
    return $this;
  }

  private function setInit($store) {
    $this->store = new \SleekDB\SleekDB($store, array(
      'storeLocation' => 'application/db/',
      'enableAutoCache' => false,
      'timeOut' => 120,
    ));
  }

  private function nextAnd($current, $key, $op, $val) {
    return $current->where($key, $op, $val);
  }

  private function nextOr($current, $key, $op, $val) {
    return $current->orWhere($key, $op, $val);
  }

  private function getDataByWhere($jsonq, $where) {
    $currentWhere = '';
    $limit = -1;
    $nextCondition = '';

    $jsonq->macro('search', function($data, $condition) {
      $jsonq = new \Nahid\JsonQ\Jsonq();
      $jsonq->collect($data);
      $result = $this->getDataByWhere($jsonq, $condition);
      return count($result) > 0;
    });

    foreach($where as $val) {
      $next = 'and';
      if(isset($val['next'])) $next = $val['next'];
      if(isset($val['limit'])) {
        $limit = $val['limit'];
      }
      if(isset($val['condition'])) {
        if($currentWhere == '') {
          $currentWhere = $jsonq->where($val['condition'][0],$val['condition'][1],$val['condition'][2]);
          $nextCondition = $next;
        } else {
          switch ($nextCondition) {
            case 'and':
              $currentWhere = $this->nextAnd($currentWhere, $val['condition'][0],$val['condition'][1],$val['condition'][2]);
              $nextCondition = $next;
              break;
            case 'or':
              $currentWhere = $this->nextOr($currentWhere, $val['condition'][0],$val['condition'][1],$val['condition'][2]);
              $nextCondition = $next;
              break;
            default: 
              $currentWhere = $this->nextAnd($currentWhere, $val['condition'][0],$val['condition'][1],$val['condition'][2]);
              $nextCondition = $next;
          }
        }
      }
    }
    if($currentWhere == '') {
      $currentWhere = $this->jsonq;
    }
    $data = $currentWhere->get();
    $this->totalRows_ = count($data);
    if($limit != -1) {
      if ( $limit[0] > 0 ) $data = array_slice( $data, $limit[0] );
      if ( $limit[1] > 0 ) $data = array_slice( $data, 0, $limit[1] );
    }
    $returnData = array();
    foreach($data as $val) {
      $returnData[] = $val;
    }
    return $returnData;
  }

  public function select($store, $columns = [], $where = false) {
    $this->setInit($store);
    $data = $this->store->fetch($columns);
    $this->totalRows_ = count($data);

    if($where !== false){
      $this->jsonq = new \Nahid\JsonQ\Jsonq();
      $this->jsonq->collect($data);
      $data = $this->getDataByWhere($this->jsonq, $where);
    }
    return $data;
  }

  public function insert($store, $data) {
    $this->setInit($store);
    return $this->store->insert($data);
  }

  public function update($store, $updateData, $where = false) {
    $this->setInit($store);
    $data = array();

    if($where !== false){
      $data = $this->store->fetch();
      $this->jsonq = new \Nahid\JsonQ\Jsonq();
      $this->jsonq->collect($data);
      $data = $this->getDataByWhere($this->jsonq, $where);
    }
    return $this->store->update($updateData, $data);
  }

  public function delete($store, $where = false) {
    $this->setInit($store);
    $data = array();

    if($where !== false){
      $data = $this->store->fetch();
      $this->jsonq = new \Nahid\JsonQ\Jsonq();
      $this->jsonq->collect($data);
      $data = $this->getDataByWhere($this->jsonq, $where);
    }
    return $this->store->delete($data);
  }

  public function totalRows() {
    return $this->totalRows_;
  }
}
?>
