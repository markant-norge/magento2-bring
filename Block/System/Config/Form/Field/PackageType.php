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
class PackageType extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray {
    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = [];
    protected $_factorRenderer;
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

    protected function getFactorRenderer() {
        if (!$this->_factorRenderer) {
            $this->_factorRenderer = $this->getLayout()->createBlock(
                '\Markant\Bring\Block\System\Config\Form\Field\AttributeType', '', ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_factorRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender() {
        $this->addColumn('title', array('label' => __('Title')));
        $this->addColumn('width', array('label' => __('Width (cm)')));
        $this->addColumn('length', array('label' => __('Length (cm)')));
        $this->addColumn('height', array('label' => __('Height (cm)')));
        $this->addColumn(
            'factor_attribute', [
                'label' => __('Factor attribute'),
                'renderer' => $this->getFactorRenderer(),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row) {
        $factorAttribute = $row->getFactorAttribute();
        $options = [];
        if ($factorAttribute) {
            $options['option_' . $this->getFactorRenderer()->calcOptionHash($factorAttribute)] = 'selected="selected"';
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
        $ints = ['width', 'length', 'height', 'factor'];
        if (in_array($columnName, $ints)) {
            $this->_columns[$columnName]['class'] = 'input-text';
            $this->_columns[$columnName]['style'] = 'width:50px';
        }
        return parent::renderCellTemplate($columnName);
    }
}
