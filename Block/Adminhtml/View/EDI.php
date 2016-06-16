<?php
namespace Markant\Bring\Block\Adminhtml\View;

use \Magento\Backend\Block\Template\Context;

class EDI extends \Magento\Backend\Block\Template
{

    const EDI_TEMPLATE = 'order/edi/view.phtml';

    /**
     * @var \Markant\Bring\Model\ResourceModel\Order\Shipment\Edi\CollectionFactory
     */
    protected $_ediCollectionFactory;


    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;


    public function __construct(Context $context,
                                array $data,
                                \Magento\Framework\Registry $registry,
                                \Markant\Bring\Model\ResourceModel\Order\Shipment\Edi\CollectionFactory $ediCollectionFactory
    )
    {
        parent::__construct($context, $data);
        $this->setTemplate(self::EDI_TEMPLATE);
        $this->_coreRegistry = $registry;
        $this->_ediCollectionFactory = $ediCollectionFactory;
    }



    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }


    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getShipment()->getOrder();
    }



    /**
     * Get submit url
     *
     * @return string|true
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/addEdi', ['id' => $this->getShipment()->getId()]);
    }

}