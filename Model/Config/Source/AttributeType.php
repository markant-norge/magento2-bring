<?php
namespace Markant\Bring\Model\Config\Source;

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/31/16 11:49 AM
 */


use Magento\Framework\Api\SearchCriteriaBuilder;

class AttributeType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    protected $attributeRepo;

    protected $searchCriteriaBuilder;

    public function __construct(\Magento\Catalog\Model\Product\Attribute\Repository $attributeRepo,
                                SearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->attributeRepo = $attributeRepo;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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

            $s = $this->searchCriteriaBuilder->create();
            $list = $this->attributeRepo->getList($s);
            foreach ($list->getItems() as $item) {
                $this->_options[] = array(
                    'value' => $item->getAttributeCode(),
                    'label' => $item->getAttributeCode()
                );
            }
        }

        $options = $this->_options;
        array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);

        return $options;
    }
}