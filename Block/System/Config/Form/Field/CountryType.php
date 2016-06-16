<?php

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/20/16 2:03 PM
 */

namespace Markant\Bring\Block\System\Config\Form\Field;

use Markant\Bring\Model\Config\Source\BringMethod;


class CountryType  extends \Magento\Framework\View\Element\Html\Select {
    /**
     * methodList
     *
     * @var array
     */
    protected $countryFactory;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Directory\Model\Config\Source\Country $countryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->countryFactory = $countryFactory;
    }
    /**
     * Returns countries array
     *
     * @return array
     */
    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml() {
        $this->setOptions($this->countryFactory->toOptionArray(false));
        return parent::_toHtml();
    }
    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value) {
        return $this->setName($value);
    }
}