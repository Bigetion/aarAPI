<?php

  /**
   * Coditions trait.
   */
  trait ConditionsTrait {

    // Add conditions to filter data.
    public function where( $fieldName = '', $condition = '', $value = '' ) {
      if ( empty( $fieldName ) ) show_error( 'SleekDB Error', 'Field name in conditional comparision can not be empty.' );
      if ( empty( $condition ) ) show_error( 'SleekDB Error', 'The comparison operator can not be empty.' );
      // Append the condition into the conditions variable.
      $this->conditions[] = [
        'fieldName' => $fieldName,
        'condition' => trim( $condition ),
        'value'     => $value
      ];
      return $this;
    }

    // Set the amount of data record to skip.
    public function skip( $skip = 0 ) {
      if ( $skip === false ) $skip = 0;
      $this->skip = (int) $skip;
      return $this;
    }

    // Set the amount of data record to limit.
    public function limit( $limit = 0 ) {
      if ( $limit === false ) $limit = 0;
      $this->limit = (int) $limit;
      return $this;
    }

    // Set the sort order.
    public function orderBy( $order = false, $orderBy = '_id' ) {
      // Validate order.
      $order = strtolower( $order );
      if ( ! in_array( $order, [ 'asc', 'desc' ] ) ) show_error( 'SleekDB Error', 'Invalid order found, please use "asc" or "desc" only.' );
      $this->orderBy = [
        'order' => $order,
        'field' => $orderBy
      ];
      return $this;
    }

    // Do a fulltext like search against more than one field.
    public function search( $field = '', $keyword = '' ) {
      if ( empty( $field ) ) show_error( 'SleekDB Error', 'Field name cant be empty while search' );
      if ( ! empty( $keyword ) ) $this->searchKeyword = [
        'field'   => (array) $field,
        'keyword' => $keyword
      ];
      return $this;
    }

    // Re-generate the cache for the query.
    public function makeCache() {
      $this->makeCache = true;
      $this->useCache  = false;
      return $this;
    }

    // Re-use existing cache of the query, if dosent exists 
    // then would make new cache.
    public function useCache() {
      $this->useCache  = true;
      $this->makeCache = false;
      return $this;
    }

    // Delete cache for the current query.
    public function deleteCache() {
      $this->_deleteCache();
      return $this;
    }

    // Delete all cache of the current store.
    public function deleteAllCache() {
      $this->_emptyAllCache();
      return $this;
    }

  }
  