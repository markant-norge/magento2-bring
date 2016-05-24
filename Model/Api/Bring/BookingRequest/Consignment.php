<?php
namespace Markant\Bring\Model\Api\Bring\BookingRequest;
use Markant\Bring\Model\Api\Bring\ApiEntity;
use Markant\Bring\Model\Api\Bring\BookingRequest\Consignment\Address;
use Markant\Bring\Model\Api\Bring\BookingRequest\Consignment\Product;
use Markant\Bring\Model\Api\Bring\DataValidationException;

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/24/16 9:21 AM
 */
class Consignment extends ApiEntity
{

    protected $_data = [
        'shippingDateTime' => null,
        'product' => null,
        'purchaseOrder' => null,
        'correlationId' => null,
        'parties' => [
            'sender' => null,
            'recipient' => null,
            'pickupPoint' => null
        ]
    ];


    public function setShippingDateTime (\DateTime $dateTime) {
        return $this->setData('shippingDateTime', $dateTime->format('Y-m-d\TH:i:s'));
    }

    public function setProduct(Product $product) {
        return $this->setData('product', $product);
    }

    public function setPurchaseOrder ($purchaseOrder) {
        return $this->setData('purchaseOrder', $purchaseOrder);
    }

    public function setCorrelationId ($correlationId) {
        return $this->setData('correlationId', $correlationId);
    }

    public function setSender (Address $sender) {
        return $this->setPartiesData('sender', $sender);
    }

    public function setRecipient (Address $recipient) {
        return $this->setPartiesData('recipient', $recipient);
    }
    public function setPickupPoint ($pickupPoint) {
        return $this->setPartiesData('pickupPoint', $pickupPoint);
    }

    public function validate()
    {
        if (!$this->getData('product')) {
            throw new DataValidationException('BookingRequest\Consignment requires "product" to be set.');
        }
        if (!$this->getData('shippingDateTime')) {
            throw new DataValidationException('BookingRequest\Consignment requires "shippingDateTime" to be set.');
        }
        if (!$this->getPartiesData('recipient')) {
            throw new DataValidationException('BookingRequest\Consignment requires "recipient" to be set.');
        }
        if (!$this->getPartiesData('sender')) {
            throw new DataValidationException('BookingRequest\Consignment requires "sender" to be set.');
        }
    }

    private function setPartiesData($key, $value) {
        if (!isset($this->_data['parties'])) $this->_data['parties'] = [];
        $this->_data['parties'][$key] = $value;
        return $this;
    }

    private function getPartiesData($key) {
        return $this->_data['parties'][$key];
    }
    private function containsPartiesData($key) {
        return isset($this->_data['parties']) && isset($this->_data['parties'][$key]);
    }
}