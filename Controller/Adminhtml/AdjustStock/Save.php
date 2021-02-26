<?php
namespace Lof\BarcodeWarehouseIntegration\Controller\Adminhtml\AdjustStock;

use Magento\Backend\App\Action;

class Save extends Action
{
    protected $_warehouseFactory;
    protected $_sourceFactory;
    protected $_stockFactory;
    protected $_sourceItemFactory;
    protected $_adjustStockFactory;
    protected $_adjustStockProductFactory;
    protected $authSession;

    public function __construct(
        Action\Context $context,
        \Magento\Inventory\Model\SourceFactory $sourceFactory,
        \Lof\Inventory\Model\WarehouseFactory $warehouseFactory,
        \Lof\Inventory\Model\StockFactory $stockFactory,
        \Magento\Inventory\Model\SourceItemFactory $sourceItemFactory,
        \Lof\Inventory\Model\AdjustStockFactory $adjustStockFactory,
        \Lof\Inventory\Model\AdjustStockProductFactory $adjustStockProductFactory,
        \Lof\MultiBarcode\Model\BarcodeFactory $barcode,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->_warehouseFactory = $warehouseFactory;
        $this->_sourceFactory = $sourceFactory;
        $this->_stockFactory = $stockFactory;
        $this->_sourceItemFactory = $sourceItemFactory;
        $this->_adjustStockFactory = $adjustStockFactory;
        $this->_adjustStockProductFactory = $adjustStockProductFactory;
        $this->authSession = $authSession;
        $this->_barcode = $barcode;
        $this->_product = $product;

        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $dataRequest = $this->getRequest()->getParams();
        $modelWarehouse = $this->_warehouseFactory->create();
        $modelSource = $this->_sourceFactory->create();
        $modelStockWarehouse = $this->_stockFactory->create();
        $modelStockSource = $this->_sourceItemFactory->create();
        $modelAdjustStock = $this->_adjustStockFactory->create();
        $modelAdjustProductStock = $this->_adjustStockProductFactory->create();
        $barcodewarehouse = $dataRequest['barcode_warehouse'];
        $barcode = $this->_barcode->create()->getCollection()->addFieldToFilter('barcode',$barcodewarehouse)->getFirstItem();
        $product = $this->_product->load($barcode->getProductId());
        $productData = ['entity_id' => $product->getId(), 'sku' => $product->getSku(), 'name' => $product->getName(), 'position' => '1','quantity' => $barcode->getQty(), 'record_id'=> '1'];
        $products['0'] = $productData;
        $warehouse = $modelWarehouse->getCollection()
            ->addFieldToFilter('warehouse_code', $dataRequest['general_information']['warehouse_code'])
            ->getFirstItem();
        $source = $modelSource->getCollection()
            ->addFieldToFilter('source_code', $dataRequest['general_information']['warehouse_code'])
            ->getFirstItem();
        $dataAdjustStock = [
            'adjustment_code' => $dataRequest['general_information']['adjustment_code'],
            'warehouse_code' => $dataRequest['general_information']['warehouse_code'],
            'note' => $dataRequest['general_information']['note'],
            'type' => $dataRequest['general_information']['type'],
            'total_qty_change' => $this->getTotalQuantityAdjustStock($products),
            'created_by' => $this->authSession->getUser()->getUsername()
        ];
        $modelAdjustStock->setData($dataAdjustStock);
        try {
            $modelAdjustStock->save();
            $this->messageManager->addSuccess(__('You added this adjust stock.'));
            $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setFormData($dataRequest);
            return $resultRedirect->setPath('*/*/new');
        }

        if ($warehouse->getWarehouse_code()) {
            foreach ($products as $product) {
                $dataStock = $modelStockWarehouse->getCollection()
                    ->addFieldToFilter('product_sku', $product['sku'])
                    ->addFieldToFilter('warehouse_code', $dataRequest['general_information']['warehouse_code'])
                    ->getFirstItem();
                $product['quantity'] = isset($product['quantity']) ? $product['quantity'] : 0;
                $qtyNew = $dataAdjustStock['type'] == 0 ? ($dataStock->getTotal_qty() + $product['quantity']) : ($dataStock->getTotal_qty() - $product['quantity']);
                //      1 => __('Goods issue'), 0 => 'Goods receipt'
                if ($qtyNew < 0) {
                    $this->messageManager->addError(__("The quantity of " . $product['name'] . " products in stock is not enough"));
                    continue;
                }

                $dataProduct = [
                    'adjuststock_id' => $modelAdjustStock->getId(),
                    'product_id' => $product['entity_id'],
                    'product_sku' => $product['sku'],
                    'product_name' => $product['name'],
                    'qty_old' => $dataStock->getTotal_qty() ? $dataStock->getTotal_qty() : 0,
                    'qty_new' => $qtyNew >= 0 ? $qtyNew : 0,
                    'qty_change' => $product['quantity'],
                ];
                $modelAdjustProductStock->setData($dataProduct)->save();

                if ($dataStock->getStock_id()) {
                    $modelStockWarehouse->load($dataStock->getStock_id())
                        ->setTotal_qty($qtyNew)
                        ->save();
                } else {
                    $data = [
                        'warehouse_code' => $dataRequest['general_information']['warehouse_code'],
                        'product_sku' => $product['sku'],
                        'total_qty' => $qtyNew
                    ];
                    $modelStockWarehouse->load($dataStock->getStock_id())
                        ->setData($data)
                        ->save();
                }
            }
        }
        if ($source->getSource_code()) {
            foreach ($products as $product) {
                $dataStock = $modelStockSource->getCollection()
                    ->addFieldToFilter('sku', $product['sku'])
                    ->addFieldToFilter('source_code', $dataRequest['general_information']['warehouse_code'])
                    ->getFirstItem();
                $product['quantity'] = isset($product['quantity']) ? $product['quantity'] : 0;
                $qtyNew = $dataAdjustStock['type'] == 0 ? ($dataStock->getQuantity() + $product['quantity']) : ($dataStock->getQuantity() - $product['quantity']);
                if ($qtyNew < 0) {
                    $this->messageManager->addError(__("The quantity of " . $product['name'] . " products in stock is not enough"));
                    continue;
                }
                $dataProduct = [
                    'adjuststock_id' => $modelAdjustStock->getId(),
                    'product_id' => $product['entity_id'],
                    'product_sku' => $product['sku'],
                    'product_name' => $product['name'],
                    'qty_old' => $dataStock->getQuantity() ? $dataStock->getQuantity() : 0,
                    'qty_new' => $qtyNew >= 0 ? $qtyNew : 0,
                    'qty_change' => $product['quantity'],
                ];
                $modelAdjustProductStock->setData($dataProduct)->save();

                if ($dataStock->getSource_item_id()) {
                    $modelStockSource->load($dataStock->getSource_item_id())
                        ->addData(['quantity' => $qtyNew])
                        ->save();
                } else {
                    $data = [
                        'source_code' => $dataRequest['general_information']['warehouse_code'],
                        'sku' => $product['sku'],
                        'quantity' => $qtyNew
                    ];
                    $modelStockSource->load($dataStock->getSource_item_id())
                        ->setData($data)
                        ->save();
                }
            }
        }
        return $resultRedirect->setPath('*/*/new');
    }

    public function getTotalQuantityAdjustStock($products)
    {
        $dataRequest = $this->getRequest()->getParams();
        $modelStockSource = $this->_sourceItemFactory->create();
        $modelStockWarehouse = $this->_stockFactory->create();
        $modelWarehouse = $this->_warehouseFactory->create();
        $modelSource = $this->_sourceFactory->create();
        $warehouse = $modelWarehouse->getCollection()
            ->addFieldToFilter('warehouse_code', $dataRequest['general_information']['warehouse_code'])
            ->getFirstItem();
        $source = $modelSource->getCollection()
            ->addFieldToFilter('source_code', $dataRequest['general_information']['warehouse_code'])
            ->getFirstItem();
        $totalQty = 0;
        if ($warehouse->getWarehouse_code()) {
            foreach ($products as $product) {
                $dataStock = $modelStockWarehouse->getCollection()
                    ->addFieldToFilter('product_sku', $product['sku'])
                    ->addFieldToFilter('warehouse_code', $dataRequest['general_information']['warehouse_code'])
                    ->getFirstItem();
                $product['quantity'] = isset($product['quantity']) ? $product['quantity'] : 0;
                $qtyNew = $dataRequest['general_information']['type'] == 0 ? ($dataStock->getTotal_qty() + $product['quantity']) : ($dataStock->getTotal_qty() - $product['quantity']);
                if ($qtyNew < 0) {
                    continue;
                }
                $totalQty += $product['quantity'];
            }
        }
        if ($source->getSource_code()) {
            foreach ($products as $product) {
                $dataStock = $modelStockSource->getCollection()
                    ->addFieldToFilter('sku', $product['sku'])
                    ->addFieldToFilter('source_code', $dataRequest['general_information']['warehouse_code'])
                    ->getFirstItem();
                $product['quantity'] = isset($product['quantity']) ? $product['quantity'] : 0;
                $qtyNew = $dataRequest['general_information']['type'] == 0 ? ($dataStock->getQuantity() + $product['quantity']) : ($dataStock->getQuantity() - $product['quantity']);
                if ($qtyNew < 0) {
                    continue;
                }
                $totalQty += $product['quantity'];
            }
        }
        return $totalQty;
    }
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_BarcodeWarehouseIntegration::save');
    }
}
