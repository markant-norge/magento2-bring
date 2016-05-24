<?php
namespace Markant\Bring\Model\Api\Bring;
use Markant\Bring\Model\Api\Bring\BookingRequest\Consignment;

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/24/16 9:20 AM
 */
class BookingRequest extends ApiEntity
{

    const SCHEMA_VERSION = 1;

    protected $_data = [
        'testIndicator' => true,
        'schemaVersion' => self::SCHEMA_VERSION,
        'consignments' => []
    ];


    public function setTestIndicator ($testIndicator) {
        return $this->setData('testIndicator', $testIndicator);
    }

    public function addConsignment(Consignment $consignment) {
        return $this->addData('consignments', $consignment);
    }
    public function addPackage(Consignment $consignment) {
        return $this->addData('consignments', $consignment);
    }

    public function validate()
    {
        if (!$this->getData('consignments')) {
            throw new DataValidationException('BookingRequest requires at least one of "consignments".');
        }
    }
}