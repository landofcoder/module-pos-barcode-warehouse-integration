<?php
namespace Lof\BarcodeWarehouseIntegration\Plugin;

class Block
{
    public function beforeToHtml(\Lof\MultiBarcode\Block\Adminhtml\Catalog\Product\Edit\Tab\Barcode $block)
    {
        $block->setTemplate('Lof_BarcodeWarehouseIntegration::product/edit/barcode.phtml');
    }
}