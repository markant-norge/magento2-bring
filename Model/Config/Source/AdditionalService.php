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

    const EVARSLING = 'EVARSLING';
    const POSTOPPKRAV = 'POSTOPPKRAV';
    const LORDAGSUTKJORING = 'LORDAGSUTKJORING';
    const ENVELOPE = 'ENVELOPE';
    const ADVISERING = 'ADVISERING';
    const PICKUP_POINT = 'PICKUP_POINT';
    const EVE_DELIVERY = 'EVE_DELIVERY';




    static public function products () {
        return [
            self::EVARSLING => __('Recipient notification over SMS or E-Mail'),
            self::POSTOPPKRAV =>  __('Cash on Delivery'),
            self::LORDAGSUTKJORING =>  __('Delivery on Saturdays'),
            self::ENVELOPE =>  __('Express Envelope'),
            self::ADVISERING =>  __('Bring contacts recipient'),
            self::PICKUP_POINT =>  __('	Delivery to pickup point'),
            self::EVE_DELIVERY =>  __('Evening delivery')
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
