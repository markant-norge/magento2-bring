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
use Markantnorge\Bring\API\Data\BringData;

/**
 * @codeCoverageIgnore
 */
class BringReturnMethod implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options array
     *
     * @var array
     */
    protected $_options;


    static public function returnProducts () {
        return [
            BringData::PRODUCT_SERVICEPAKKE_RETURSERVICE => __('Servicepakke'),
            BringData::PRODUCT_BPAKKE_DOR_DOR_RETURSERVICE => __('Dør til dør'),
            BringData::PRODUCT_EKSPRESS09_RETURSERVICE => __('Bedriftspakke Ekspress-Over natten 09')
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
            foreach (self::returnProducts() as $k => $v) {
                $this->_options[] = array(
                    'value' => $k,
                    'label' => $v
                );
            }
        }

        $options = $this->_options;
        array_unshift($options, ['value' => '', 'label' => __('--No return label--')]);

        return $options;
    }
}
