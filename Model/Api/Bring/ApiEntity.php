<?php
namespace Markant\Bring\Model\Api\Bring;


/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/24/16 9:24 AM
 */
abstract class ApiEntity
{

    protected $_data = [];

    protected function setData($key, $value) {
        $this->_data[$key] = $value;
        return $this;
    }

    protected function getData($key) {
        return $this->_data[$key];
    }

    protected function addData($key, $value) {
        if (!isset($this->_data[$key]) || !is_array($this->_data[$key])) {
            $this->_data[$key] = [];
        }
        $this->_data[$key][] = $value;
        return $this;
    }

    protected function containsData($key) {
        return isset($this->_data[$key]);
    }

    abstract public function validate();


    public function toArray () {
        $this->validate();
        return $this->dataToArray($this->_data);
    }

    protected function dataToArray (array $data) {
        $result = [];
        foreach ($data as $key => $value) {
            if ($value instanceof ApiEntity) {
                $result[$key] = $value->toArray();
            } else if (is_array($value)) {
                $result[$key] = $this->dataToArray($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

}