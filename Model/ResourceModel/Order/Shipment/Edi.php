<?php
namespace Markant\Bring\Model\ResourceModel\Order\Shipment;

use Magento\Sales\Model\ResourceModel\EntityAbstract as SalesResource;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Sales\Model\Spi\ShipmentTrackResourceInterface;

/**
 * Flat sales order shipment comment resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Edi extends SalesResource implements ShipmentTrackResourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment_edi_resource';

    /**
     * Validator
     *
     * @var \Markant\Bring\Model\Order\Shipment\Edi\Validator
     */
    protected $validator;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Attribute $attribute
     * @param \Magento\SalesSequence\Model\Manager $sequenceManager
     * @param Snapshot $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Markant\Bring\Model\Order\Shipment\Edi\Validator $validator
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Sales\Model\ResourceModel\Attribute $attribute,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Markant\Bring\Model\Order\Shipment\Edi\Validator $validator,
        $connectionName = null
    ) {
        $this->validator = $validator;
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $attribute,
            $sequenceManager,
            $connectionName
        );
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_shipment_edi', 'entity_id');
    }

    /**
     * Performs validation before save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Markant\Bring\Model\Order\Shipment\Edi $object */
        if (!$object->getParentId() && $object->getShipment()) {
            $object->setParentId($object->getShipment()->getId());
        }

        parent::_beforeSave($object);
        $errors = $this->validator->validate($object);
        if (!empty($errors)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Cannot save edi:\n%1", implode("\n", $errors))
            );
        }

        return $this;
    }
}
