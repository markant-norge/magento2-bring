<?php
namespace Markant\Bring\Plugin\Widget;


class Context
{
    public function afterGetButtonList(
        \Magento\Backend\Block\Widget\Context $subject,
        $buttonList
    )
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('Magento\Framework\App\Action\Context')->getRequest();
        if($request->getFullActionName() == 'adminhtml_order_shipment_view'){

            $buttonList->add(
                'mybring_button',
                [
                    'label' => __('Send to Bring'),
                    'onclick' => 'setLocation(\'' . $this->getCustomUrl() . '\')',
                    'class' => 'ship'
                ]
            );
        }

        return $buttonList;
    }

    public function getCustomUrl()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $urlManager = $objectManager->get('Magento\Framework\Url');
        return $urlManager->getUrl('sales/*/mybring');
    }
}