<?php
namespace Lof\BarcodeWarehouseIntegration\Controller\Adminhtml\System\Config;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Lof\BarcodeWarehouseIntegration\Helper\Data;

class Generate extends \Magento\Backend\App\Action
{
    protected $_pageFactory;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var \Lof\Inventory\Model\ResourceModel\Stock\CollectionFactory
     */
    private $_collection;

    public function __construct(
        Context $context,
        \Lof\BarcodeWarehouseIntegration\Helper\Data $helper,
        \Lof\Inventory\Model\ResourceModel\Stock\CollectionFactory $collectionFactory,
        PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_collection = $collectionFactory;
        $this->helper = $helper;
        return parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->_collection->create();
        $this->helper->generateBarcode($collection);
    }
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_BarcodeWarehouseIntegration::generate');
    }
}
