<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

require_once 'vendor/sleekdb/src/SleekDB.php';

class sleekdb {
  public function setStore($store) {
    $this->store = new \SleekDB\SleekDB($store);
    return $this;
  }
}
?>
