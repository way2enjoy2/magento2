<?php

namespace Way2enjoy\Magento\Model;

use Way2enjoy;
use Way2enjoy\Magento\Model\Config;

use Magento\Catalog\Model\Product\Image;
use Psr\Log\LoggerInterface as Logger;

class OptimizableImage
{
    protected $logger;
    protected $config;
    protected $image;

    public function __construct(Logger $logger, Config $config, Image $image)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->image = $image;
    }

    public function getUrl()
    {
        $dir = $this->config->getMediaDirectory();
        $path = $this->getOptimizedPath();

        /* Fall back to unoptimized version if optimized one does not exist. */
        if (!$dir->isFile($path)) {
            $path = $this->getUnoptimizedPath();
        }

        return $this->config->getMediaUrl($path);
    }

    public function optimize()
    {
        if (!$this->isOptimizable()) {
            $this->logger->debug("Skipping {$this->getUnoptimizedPath()}.");
            return false;
        }

        if (!$this->config->apply()) {
            $this->logger->debug("API key not configured.");
            return false;
        }

        $dir = $this->config->getMediaDirectory();
        $path = $this->getOptimizedPath();

        if (!$dir->isFile($path)) {
            $source = $dir->readFile($this->getUnoptimizedPath());

            try {
                $result = Way2enjoy\fromBuffer($source)->toBuffer();
                $this->config->getStatus()->updateCompressionCount();
            } catch (Way2enjoy\Exception $err) {
                $this->logger->error($err);
                return false;
            }

            $dir->writeFile($path, $result);
            $this->logger->debug("Optimized {$this->getUnoptimizedPath()}.");
        }

        return true;
    }

    protected function isOptimizable()
    {
        switch (strtolower($this->image->getDestinationSubdir())) {
            case "thumbnail":
                $type = "thumbnail";
                break;
            case "small_image":
                $type = "small";
                break;
            case "swatch_thumb":
            case "swatch_image":
                $type = "swatch";
                break;
            case "image":
            default:
                $type = "base";
        }

        return $this->config->isOptimizableType($type);
    }

    protected function getOptimizedPath()
    {
        $hash = $this->getUnoptimizedHash();
        $file = $this->getFilename();
        return implode("/", [$this->config->getPathPrefix(), $hash[0], $hash[1], $hash, $file]);
    }

    protected function getUnoptimizedPath()
    {
        /* In Magento <= 2.1.5, getNewFile() returned the path of the
           unoptimized cached image, as opposed to getBaseFile(), which
           returned the original.

           As of Magento 2.1.6, the implemention has changed and getNewFile()
           is always undefined - we now need to access the underlying image
           asset. Unfortunately this is private, so we need to hack around it. */
        $path = $this->image->getNewFile();

        if (!$path) {
            $abs = $this->getImageAsset()->getPath();
            $dir = $this->config->getMediaDirectory();
            $path = $dir->getRelativePath($abs);
        }

        return $path;
    }

    protected function getUnoptimizedHash()
    {
        $file = $this->config->getMediaPath($this->getUnoptimizedPath());
        return hash_file("sha256", $file);
    }

    protected function getFilename()
    {
        return basename($this->getUnoptimizedPath());
    }

    protected function getImageAsset()
    {
        /* TODO: This is a giant hack because Magento does not yet expose
           the image asset property. See:
           https://github.com/magento/magento2/pull/9503 */
        $class = new \ReflectionClass($this->image);
        $property = $class->getParentClass()->getProperty("imageAsset");
        $property->setAccessible(true);

        return $property->getValue($this->image);
    }
}
