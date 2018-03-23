<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Markant\Bring\Model\Order\Shipment;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Model\AbstractModel;
use Markant\Bring\Api\Data\ShipmentEdiInterface;

/**
 * @method \Magento\Sales\Model\ResourceModel\Order\Shipment\Track _getResource()
 * @method \Magento\Sales\Model\ResourceModel\Order\Shipment\Track getResource()
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Edi extends AbstractModel implements ShipmentEdiInterface
{
    /**
     * Code of custom carrier
     */
    const CUSTOM_CARRIER_CODE = 'custom';

    /**
     * @var \Magento\Sales\Model\Order\Shipment|null
     */
    protected $_shipment = null;

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment_track';

    /**
     * @var string
     */
    protected $_eventObject = 'track';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_storeManager = $storeManager;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Markant\Bring\Model\ResourceModel\Order\Shipment\Edi');
    }



    /**
     * Declare Shipment instance
     *
     * @codeCoverageIgnore
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    public function setShipment(\Magento\Sales\Model\Order\Shipment $shipment)
    {
        $this->_shipment = $shipment;
        return $this;
    }

    /**
     * Retrieve Shipment instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        if (!$this->_shipment instanceof \Magento\Sales\Model\Order\Shipment) {
            $this->_shipment = $this->shipmentRepository->get($this->getParentId());
        }

        return $this->_shipment;
    }

    /**
     * Check whether custom carrier was used for this track
     *
     * @return bool
     */
    public function isCustom()
    {
        return $this->getCarrierCode() == self::CUSTOM_CARRIER_CODE;
    }

    /**
     * Retrieve hash code of current order
     *
     * @return string
     */
    public function getProtectCode()
    {
        return (string)$this->getShipment()->getProtectCode();
    }

    /**
     * Get store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if ($this->getShipment()) {
            return $this->getShipment()->getStore();
        }
        return $this->_storeManager->getStore();
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }


    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(ShipmentEdiInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(ShipmentEdiInterface::CREATED_AT, $createdAt);
    }


    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(ShipmentEdiInterface::ORDER_ID);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(ShipmentEdiInterface::PARENT_ID);
    }

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(ShipmentEdiInterface::UPDATED_AT);
    }

    /**
     * Returns weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->getData(ShipmentEdiInterface::WEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($timestamp)
    {
        return $this->setData(ShipmentEdiInterface::UPDATED_AT, $timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($id)
    {
        return $this->setData(ShipmentEdiInterface::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setWeight($weight)
    {
        return $this->setData(ShipmentEdiInterface::WEIGHT, $weight);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($id)
    {
        return $this->setData(ShipmentEdiInterface::ORDER_ID, $id);
    }


    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\ShipmentTrackExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\ShipmentTrackExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\ShipmentTrackExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }




    public function getLength()
    {
        return $this->getData(ShipmentEdiInterface::LENGTH);
    }

    /**
     * @param $length
     * @return $this
     */
    public function setLength($length)
    {
        return $this->setData(ShipmentEdiInterface::LENGTH, $length);
    }

    public function getWidth()
    {
        return $this->getData(ShipmentEdiInterface::WIDTH);
    }

    /**
     * @param $width
     * @return $this
     */
    public function setWidth($width)
    {
        return $this->setData(ShipmentEdiInterface::WIDTH, $width);
    }

    public function getHeight()
    {
        return $this->getData(ShipmentEdiInterface::HEIGHT);
    }

    /**
     * @param $height
     * @return $this
     */
    public function setHeight($height)
    {
        return $this->setData(ShipmentEdiInterface::HEIGHT, $height);
    }











    public function getLabelUrl()
    {
        return $this->getData(ShipmentEdiInterface::LABEL_URL);
    }

    public function setLabelUrl($value)
    {
        return $this->setData(ShipmentEdiInterface::LABEL_URL, $value);
    }


    public function getReturnLabelUrl()
    {
        return $this->getData(ShipmentEdiInterface::RETURN_LABEL_URL);
    }

    public function setReturnLabelUrl($value)
    {
        return $this->setData(ShipmentEdiInterface::RETURN_LABEL_URL, $value);
    }


    public function getWaybill()
    {
        return $this->getData(ShipmentEdiInterface::WAYBILL);
    }

    public function setWaybill($value)
    {
        return $this->setData(ShipmentEdiInterface::WAYBILL, $value);
    }

    public function getTrackingUrl()
    {
        return $this->getData(ShipmentEdiInterface::TRACKING_URL);
    }

    public function setTrackingUrl($value)
    {
        return $this->setData(ShipmentEdiInterface::TRACKING_URL, $value);
    }

    public function getConsignmentNumber()
    {
        return $this->getData(ShipmentEdiInterface::CONSIGNMENT_NUMBER);
    }

    public function setConsignmentNumber($value)
    {
        return $this->setData(ShipmentEdiInterface::CONSIGNMENT_NUMBER, $value);
    }

    public function getPackageNumbers()
    {
        $pgkNumber = $this->getData(ShipmentEdiInterface::PACKAGE_NUMBERS);
        if ($pgkNumber) {
            $pgkNumber = \Markant\Bring\Helper\Data::unserialize($pgkNumber);
        }
        return $pgkNumber;
    }

    public function setPackageNumbers($value)
    {
        return $this->setData(ShipmentEdiInterface::PACKAGE_NUMBERS, serialize($value));
    }

    public function getEarliestPickup()
    {
        return $this->getData(ShipmentEdiInterface::EARLIEST_PICKUP);
    }

    public function setEarliestPickup($value)
    {
        return $this->setData(ShipmentEdiInterface::EARLIEST_PICKUP, $value);
    }

    public function getExpectedDelivery()
    {
        return $this->getData(ShipmentEdiInterface::EXPECTED_DELIVERY);
    }

    public function setExpectedDelivery($value)
    {
        return $this->setData(ShipmentEdiInterface::EXPECTED_DELIVERY, $value);
    }
}
