<?php
namespace Lof\BarcodeWarehouseIntegration\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Magento\Framework\UrlInterface;

class Data extends AbstractHelper
{
    /**
     * @var \Lof\MultiBarcode\Model\BarcodeFactory
     */
    private $multiBarcode;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $product;

    public function __construct(
        File $file,
        FileFactory $fileFactory,
        Filesystem\DirectoryList $dir,
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        UrlInterface $urlBuilder,
        \Magento\Catalog\Model\ProductFactory $product,
        \Lof\MultiBarcode\Model\BarcodeFactory $barcode,
        \Lof\MultiBarcode\Helper\Data $helper,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->filesystem = $filesystem;
        $this->urlBuilder = $urlBuilder;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->file = $file;
        $this->dir = $dir;
        $this->helper = $helper;
        $this->fileFactory = $fileFactory;
        $this->scopeConfig=$scopeConfig;
        $this->_moduleManager = $moduleManager;
        $this->multiBarcode = $barcode;
        $this->product = $product;
    }
    public function generateBarcode($collection)
    {
        try {
            foreach ($collection as $product) {
                $barcode = $this->multiBarcode->create();
                $productId = $this->product->create()->getIdBySku($product->getProductSku());
                $barcodeNumber = $productId."-".$this->generateRandomString(8);
                $barcode->setQty('1')->setProductId($productId)
                    ->setbarcode($barcodeNumber)
                    ->setUrl($productId.$barcodeNumber.".png")
                    ->setWarehouseCode($product->getWarehouseCode());
                $barcode->save();
                $this->helper->generateBarcode($barcode, $productId);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
