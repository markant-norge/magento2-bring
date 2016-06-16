<?php
namespace Markant\Bring\Block\System\Config\Form\Field;


class ComparisonType  extends \Magento\Framework\View\Element\Html\Select {


    const LT = 'lt';
    const LTE = 'lte';
    const GT = 'gt';
    const GTE = 'gte';

    public function __construct(
        \Magento\Framework\View\Element\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }


    static public function rules () {
        return [
            self::LT => __('Less then'),
            self::LTE => __('Less or equal to'),
            self::GT => __('Greater then'),
            self::GTE => __('Greater or equal to'),
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
