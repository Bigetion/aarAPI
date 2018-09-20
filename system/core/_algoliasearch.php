<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
/*
 * Copyright (c) 2013 Algolia
 * http://www.algolia.com/
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 */

require_once 'system/modules/algoliasearch/AlgoliaException.php';
require_once 'system/modules/algoliasearch/AlgoliaConnectionException.php';
require_once 'system/modules/algoliasearch/Client.php';
require_once 'system/modules/algoliasearch/ClientContext.php';
require_once 'system/modules/algoliasearch/Index.php';
require_once 'system/modules/algoliasearch/IndexBrowser.php';
require_once 'system/modules/algoliasearch/PlacesIndex.php';
require_once 'system/modules/algoliasearch/SynonymType.php';
require_once 'system/modules/algoliasearch/Version.php';
require_once 'system/modules/algoliasearch/Json.php';
require_once 'system/modules/algoliasearch/FailingHostsCache.php';
require_once 'system/modules/algoliasearch/FileFailingHostsCache.php';
require_once 'system/modules/algoliasearch/InMemoryFailingHostsCache.php';
require_once 'system/modules/algoliasearch/Iterators/AlgoliaIterator.php';
require_once 'system/modules/algoliasearch/Iterators/RuleIterator.php';
require_once 'system/modules/algoliasearch/Iterators/SynonymIterator.php';

class Algolia {
  
  public function __construct() {
    $this->client = new \AlgoliaSearch\Client('8JYXZ2Q84M', '4189d51b19d7f8f48d16344285c3fc2e');
  }

  public function setIndex($index) {
    $this->index = $this->client->initIndex($index);
    return $this;
  }

  public function addObjects($objects) {
    return $this->index->addObjects($objects);
  }

  public function updateObjects($objects) {
    return $this->index->saveObjects($objects);
  }

  public function deleteObjects($ids) {
    $this->index->deleteObjects($ids);
  }

  public function getObject($ids, $columns) {
    return $this->index->getObject($ids, $columns);
  }

  public function search($query_string, $options) {
    return $this->index->search($query_string, $options);
  }
}
?>
