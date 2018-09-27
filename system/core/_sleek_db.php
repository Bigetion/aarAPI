<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

require_once 'system/modules/sleekdb/src/SleekDB.php';

class sleekdb {
  public function setStore($store) {
    $this->store = new \SleekDB\SleekDB($store);
    return $this;
  }
}
?>
