<?php

namespace Way2enjoy\Magento\Model;

use Magento\Catalog\Model\Product\Image;
use Magento\Framework\ObjectManagerInterface as ObjectManager;

class OptimizableImageFactory
{
    const INSTANCE = "Way2enjoy\Magento\Model\OptimizableImage";

    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(Image $image)
    {
        return $this->objectManager->create(self::INSTANCE, ["image" => $image]);
    }
}
