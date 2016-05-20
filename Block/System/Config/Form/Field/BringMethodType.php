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

class BringMethodType  extends \Magento\Framework\View\Element\Html\Select {
    public function __construct(
        \Magento\Framework\View\Element\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
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
            foreach (BringMethod::products() as $id => $label) {
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
