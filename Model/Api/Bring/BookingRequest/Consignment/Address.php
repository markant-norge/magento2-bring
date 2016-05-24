<?php

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/24/16 10:30 AM
 */

namespace Markant\Bring\Model\Api\Bring\BookingRequest\Consignment;


use Markant\Bring\Model\Api\Bring\ApiEntity;
use Markant\Bring\Model\Api\Bring\DataValidationException;

class Address extends ApiEntity
{

    protected $_data = [
        'name' => null,
        'addressLine' => null,
        'addressLine2' => null,
        'postalCode' => null,
        'city' => null,
        'countryCode' => null,
        'reference' => null,
        'additionalAddressInfo' => null
    ];

    public function setName ($name) {
        return $this->setData('name', $name);
    }

    public function setAddressLine ($addressLine) {
        return $this->setData('addressLine', $addressLine);
    }

    public function setAddressLine2 ($addressLine2) {
        return $this->setData('addressLine2', $addressLine2);
    }

    public function setPostalCode ($postalCode) {
        return $this->setData('postalCode', $postalCode);
    }

    public function setCity ($city) {
        return $this->setData('city', $city);
    }

    public function setCountryCode ($countryCode) {
        return $this->setData('countryCode', $countryCode);
    }

    public function setReference ($reference) {
        return $this->setData('reference', $reference);
    }

    public function setAdditionalAddressInfo ($additionalAddressInfo) {
        return $this->setData('additionalAddressInfo', $additionalAddressInfo);
    }

    public function validate()
    {

        $required_fields = ['name','addressLine', 'postalCode', 'city', 'countryCode'];

        foreach ($required_fields as $f) {
            if (!$this->getData($f)) {
                throw new DataValidationException('BookingRequest\Consignment\Address requires "'.$f.'" to be set.');
            }
        }

    }
}