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


    public function validate()
    {
    }
}