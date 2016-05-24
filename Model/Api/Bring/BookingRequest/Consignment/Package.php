<?php

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/24/16 10:48 AM
 */

namespace Markant\Bring\Model\Api\Bring\BookingRequest\Consignment;


use Markant\Bring\Model\Api\Bring\ApiEntity;
use Markant\Bring\Model\Api\Bring\DataValidationException;

class Package extends ApiEntity
{

    protected $_data = [
        'weightInKg' => null,
        'goodsDescription' => null,
        'dimensions' => [
            'heightInCm' => null,
            'widthInCm' => null,
            'lengthInCm' => null
        ],
        'containerId' => null,
        'packageType' => null,
        'numberOfItems' => null,
        'correlationId' => null
    ];

    public function setWeightInKg ($weightInKg) {
        $val = (float)$weightInKg;
        if ($val <= 0) {
            throw new \InvalidArgumentException("Argument weightInKg must be greater then zero.");
        }
        return $this->setData('weightInKg', $val);
    }

    public function setGoodsDescription ($goodsDescription) {
        return $this->setData('goodsDescription', $goodsDescription);
    }

    public function setContainerId ($containerId) {
        return $this->setData('containerId', $containerId);
    }
    public function setPackageType ($packageType) {
        return $this->setData('packageType', $packageType);
    }
    public function setNumberOfItems ($numberOfItems) {
        return $this->setData('numberOfItems', $numberOfItems);
    }
    public function setCorrelationId ($correlationId) {
        return $this->setData('correlationId', $correlationId);
    }

    public function setDimensionHeightInCm ($heightInCm) {
        return $this->setDimensionsData('heightInCm', $heightInCm);
    }


    public function setDimensionWidthInCm ($widthInCm) {
        return $this->setDimensionsData('widthInCm', $widthInCm);
    }


    public function setDimensionLengthInCm ($lengthInCm) {
        return $this->setDimensionsData('lengthInCm', $lengthInCm);
    }



    public function validate()
    {
        if ($this->getData('weightInKg') <= 0) {
            throw new DataValidationException('BookingRequest\Consignment\Package requires "weightInKg" to be greater then zero.');
        }
        if ($this->getDimensionsData('heightInCm') <= 0) {
            throw new DataValidationException('BookingRequest\Consignment\Package requires "heightInCm" to be greater then zero.');
        }
        if ($this->getDimensionsData('widthInCm') <= 0) {
            throw new DataValidationException('BookingRequest\Consignment\Package requires "widthInCm" to be greater then zero.');
        }
        if ($this->getDimensionsData('lengthInCm') <= 0) {
            throw new DataValidationException('BookingRequest\Consignment\Package requires "lengthInCm" to be greater then zero.');
        }
    }


    private function setDimensionsData($key, $value) {
        if (!isset($this->_data['dimensions'])) $this->_data['dimensions'] = [];
        $this->_data['dimensions'][$key] = $value;
        return $this;
    }

    private function getDimensionsData($key) {
        return $this->_data['dimensions'][$key];
    }
}