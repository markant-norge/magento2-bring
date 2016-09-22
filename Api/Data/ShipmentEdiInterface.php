<?php
namespace Markant\Bring\Api\Data;

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/23/16 11:39 AM
 */
interface ShipmentEdiInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * Parent ID.
     */
    const PARENT_ID = 'parent_id';

    /*
     * Order ID.
     */
    const ORDER_ID = 'order_id';

    /*
     * Created-at timestamp.
     */
    const CREATED_AT = 'created_at';
    /*
     * Updated-at timestamp.
     */
    const UPDATED_AT = 'updated_at';



    const WEIGHT = 'weight';

    const LENGTH = 'length';

    const WIDTH = 'width';

    const HEIGHT = 'height';

    // Outputs

    const LABEL_URL = 'label_url';

    const WAYBILL = 'waybill';

    const TRACKING_URL = 'tracking';

    const CONSIGNMENT_NUMBER = 'consignment_number';

    const PACKAGE_NUMBERS = 'package_numbers';

    const EARLIEST_PICKUP = 'earliest_pickup';

    const EXPECTED_DELIVERY = 'expected_delivery';


    const RETURN_LABEL_URL = 'return_label_url';



    public function getLength();
    public function setLength($length);
    public function getWidth();
    public function setWidth($width);
    public function getHeight();
    public function setHeight($height);


    public function getLabelUrl();
    public function setLabelUrl($value);
    public function getWaybill();
    public function setWaybill($value);
    public function getTrackingUrl();
    public function setTrackingUrl($value);
    public function getConsignmentNumber();
    public function setConsignmentNumber($value);
    public function getPackageNumbers();
    public function setPackageNumbers($value);
    public function getEarliestPickup();
    public function setEarliestPickup($value);
    public function getExpectedDelivery();
    public function setExpectedDelivery($value);
    public function getReturnLabelUrl();
    public function setReturnLabelUrl($value);


    /**
     * Gets the created-at timestamp for the shipment package.
     *
     * @return string|null Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the shipment package.
     *
     * @param string $createdAt timestamp
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the ID for the shipment package.
     *
     * @return int|null Shipment package ID.
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Gets the order_id for the shipment package.
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Gets the parent ID for the shipment package.
     *
     * @return int Parent ID.
     */
    public function getParentId();




    /**
     * Gets the updated-at timestamp for the shipment package.
     *
     * @return string|null Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Gets the weight for the shipment package.
     *
     * @return float Weight.
     */
    public function getWeight();

    /**
     * Sets the updated-at timestamp for the shipment package.
     *
     * @param string $timestamp
     * @return $this
     */
    public function setUpdatedAt($timestamp);

    /**
     * Sets the parent ID for the shipment package.
     *
     * @param int $id
     * @return $this
     */
    public function setParentId($id);

    /**
     * Sets the weight for the shipment package.
     *
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight);


    /**
     * Sets the order_id for the shipment package.
     *
     * @param int $id
     * @return $this
     */
    public function setOrderId($id);


    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\ShipmentTrackExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\ShipmentTrackExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentTrackExtensionInterface $extensionAttributes
    );

}