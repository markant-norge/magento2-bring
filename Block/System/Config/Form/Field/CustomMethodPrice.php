<?php
namespace Markant\Bring\Block\System\Config\Form\Field;

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/20/16 1:57 PM
 */
class CustomMethodPrice extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray {
    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = [];
    protected $_bringMethodRenderer;
    /**
     * Enable the "Add after" button or not
     *
     * @var bool
     */
    protected $_addAfter = true;
    /**
     * Label of add button
     *
     * @var string
     */
    protected $_addButtonLabel;
    /**
     * Check if columns are defined, set template
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }
    /**
     * Returns renderer for country element
     *
     * @return \Magento\Braintree\Block\Adminhtml\Form\Field\Countries
     */
    protected function getBringMethodRenderer() {
        if (!$this->_bringMethodRenderer) {
            $this->_bringMethodRenderer = $this->getLayout()->createBlock(
                '\Markant\Bring\Block\System\Config\Form\Field\BringMethodType', '', ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_bringMethodRenderer;
    }
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender() {
        $this->addColumn(
            'shipping_method', [
                'label' => __('Shipping method'),
                'renderer' => $this->getBringMethodRenderer(),
            ]
        );
        $this->addColumn('price', array('label' => __('Price')));
        $this->addColumn('min_weight', array('label' => __('Min weight (Gr)')));
        $this->addColumn('max_weight', array('label' => __('Max weight (Gr)')));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row) {
        $shippingMethod = $row->getShippingMethod();
        $options = [];
        if ($shippingMethod) {
            $options['option_' . $this->getBringMethodRenderer()->calcOptionHash($shippingMethod)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        $ints = ['price', 'max_weight', 'min_weight'];
        if (in_array($columnName, $ints)) {
            $this->_columns[$columnName]['class'] = 'input-text';
            $this->_columns[$columnName]['style'] = 'width:50px';
        }
        return parent::renderCellTemplate($columnName);
    }
}
