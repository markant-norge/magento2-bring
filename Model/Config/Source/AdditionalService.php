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
use Peec\Bring\API\Data\ShippingGuideData;

/**
 * @codeCoverageIgnore
 */
class AdditionalService implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options array
     *
     * @var array
     */
    protected $_options;





    static public function products () {
        return [
            ShippingGuideData::EVARSLING => __('Recipient notification over SMS or E-Mail'),
            ShippingGuideData::POSTOPPKRAV =>  __('Cash on Delivery'),
            ShippingGuideData::LORDAGSUTKJORING =>  __('Delivery on Saturdays'),
            ShippingGuideData::ENVELOPE =>  __('Express Envelope'),
            ShippingGuideData::ADVISERING =>  __('Bring contacts recipient'),
            ShippingGuideData::PICKUP_POINT =>  __('Delivery to pickup point'),
            ShippingGuideData::EVE_DELIVERY =>  __('Evening delivery')
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
        if (!$this->_options) {
            foreach (self::products() as $k => $v) {
                $this->_options[] = array(
                    'value' => $k,
                    'label' => $v
                );
            }
        }

        $options = $this->_options;
        array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);

        return $options;
    }
}
