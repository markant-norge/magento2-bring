<?php
namespace Markant\Bring\Block\System\Config\Form\Field;
use Magento\Framework\Api\SearchCriteriaBuilder;



class AttributeType  extends \Magento\Framework\View\Element\Html\Select {

    protected $attributeRepo;

    protected $searchCriteriaBuilder;


    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Catalog\Model\Product\Attribute\Repository $attributeRepo,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attributeRepo = $attributeRepo;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        $s = $this->searchCriteriaBuilder->create();

        $list = $this->attributeRepo->getList($s);
        foreach ($list->getItems() as $item) {
            $this->addOption($item->getAttributeCode(), $item->getAttributeCode());
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