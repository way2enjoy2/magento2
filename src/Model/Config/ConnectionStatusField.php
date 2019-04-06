<?php

namespace Way2enjoy\Magento\Model\Config;

use Way2enjoy\Magento\Model\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ConnectionStatusField extends Field
{
    protected $status;

    public function __construct(Context $context, ConnectionStatus $status)
    {
        $this->status = $status;
        parent::__construct($context);
    }

    public function getElementHtml(AbstractElement $element)
    {
        $classes = ["way2enjoy-connection-status"];

        switch ($this->status->getStatus()) {
            case ConnectionStatus::UNKNOWN:
                $element->setValue(__("Save configuration to check status."));
                break;
            case ConnectionStatus::SUCCESS:
                $classes[] = "way2enjoy-success";
                $element->setValue(__("API connection successful."));
                $element->setComment(__(
                    "You have made %1 compressions this month.",
                    $this->status->getCompressionCount()
                ));
                break;
            case ConnectionStatus::FAILURE:
                $classes[] = "way2enjoy-failure";
                $element->setValue(__("API connection unsuccessful."));
                $element->setComment(__(
                    "Error: %1",
                    $this->status->getLastError()
                ));
                break;
        }

        return "<div class=\"" . implode(" ", $classes) . "\">{$element->getElementHtml()}</div>";
    }

    // @codingStandardsIgnoreLine - Magento violates underscore prefix rule.
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->getElementHtml($element);
    }
}
