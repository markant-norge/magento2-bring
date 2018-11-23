<?php

namespace Markant\Bring\Model\Config\Source;


/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/18/16 1:34 PM
 */
use Markantnorge\Bring\API\Data\ShippingGuideData;

/**
 * @codeCoverageIgnore
 */
class DeliveryOption implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options array
     *
     * @var array
     */
    protected $_options;


    static public function getDeliveryOptions()
    {
        return [
            ['label' => 'ONE_DELIVERY_THEN_PIB', 'value'=>'ONE_DELIVERY_THEN_PIB'],
            ['label' => 'ONE_DELIVERY_THEN_PIB', 'value'=>'TWO_DELIVERIES_THEN_PIB'],
            ['label' => 'ONE_DELIVERY_THEN_PIB', 'value'=>'TWO_DELIVERIES_THEN_RETURN']
        ];
    }

    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     * @return array
     */
    public function toOptionArray()
    {
        $options = self::getDeliveryOptions();
        return $options;
    }
}
