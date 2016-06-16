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
class BringProductRuleType extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray {
    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = [];
    protected $_bringMethodRenderer;
    protected $_ruleRenderer;
    protected $_comparisonRenderer;

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

    protected function getBringMethodRenderer() {
        if (!$this->_bringMethodRenderer) {
            $this->_bringMethodRenderer = $this->getLayout()->createBlock(
                '\Markant\Bring\Block\System\Config\Form\Field\BringMethodType', '', ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_bringMethodRenderer;
    }

    protected function getRuleRenderer() {
        if (!$this->_ruleRenderer) {
            $this->_ruleRenderer = $this->getLayout()->createBlock(
                '\Markant\Bring\Block\System\Config\Form\Field\RuleType', '', ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_ruleRenderer;
    }

    protected function getComparisonRenderer() {
        if (!$this->_comparisonRenderer) {
            $this->_comparisonRenderer = $this->getLayout()->createBlock(
                '\Markant\Bring\Block\System\Config\Form\Field\ComparisonType', '', ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_comparisonRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender() {
        $this->addColumn(
            'bring_product', [
                'label' => __('Bring Product'),
                'renderer' => $this->getBringMethodRenderer(),
            ]
        );
        $this->addColumn(
            'rule', [
                'label' => __('Rule'),
                'renderer' => $this->getRuleRenderer(),
            ]
        );
        $this->addColumn(
            'comparison', [
                'label' => __('Comparison'),
                'renderer' => $this->getComparisonRenderer(),
            ]
        );
        $this->addColumn('value', array('label' => __('Value')));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row) {
        $bringProduct = $row->getBringProduct();
        $rule = $row->getRule();
        $comparison = $row->getComparison();
        $options = [];
        if ($bringProduct) {
            $options['option_' . $this->getBringMethodRenderer()->calcOptionHash($bringProduct)] = 'selected="selected"';
        }
        if ($rule) {
            $options['option_' . $this->getRuleRenderer()->calcOptionHash($rule)] = 'selected="selected"';
        }
        if ($comparison) {
            $options['option_' . $this->getComparisonRenderer()->calcOptionHash($comparison)] = 'selected="selected"';
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
        $ints = ['value'];
        if (in_array($columnName, $ints)) {
            $this->_columns[$columnName]['class'] = 'input-text';
            $this->_columns[$columnName]['style'] = 'width:50px';
        }
        return parent::renderCellTemplate($columnName);
    }
}
