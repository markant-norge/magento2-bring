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


    /*
     * Weight.
     */
    const WEIGHT = 'weight';

    const LENGTH = 'length';
    const WIDTH = 'width';
    const HEIGHT = 'height';


    public function getLength();
    public function setLength($length);
    public function getWidth();
    public function setWidth($width);
    public function getHeight();
    public function setHeight($height);



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