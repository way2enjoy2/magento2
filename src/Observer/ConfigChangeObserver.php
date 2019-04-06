<?php

namespace Way2enjoy\Magento\Observer;

use Way2enjoy\Magento\Model\Config\ConnectionStatus;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ConfigChangeObserver implements ObserverInterface
{
    protected $status;

    public function __construct(ConnectionStatus $status)
    {
        $this->status = $status;
    }

    public function execute(Observer $observer)
    {
        $this->status->update();
    }
}
