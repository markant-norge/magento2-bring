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


class RuleType  extends \Magento\Framework\View\Element\Html\Select {


    const CART_WEIGHT = 'cart_weight';

    public function __construct(
        \Magento\Framework\View\Element\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }



    static public function rules () {
        return [
            self::CART_WEIGHT => __('Cart Weight (kg)'),
        ];
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
        if (!$this->getOptions()) {
            foreach (self::rules() as $id => $label) {
                $this->addOption($id, $label);
            }
        }
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
