<?php


namespace Lof\BarcodeWarehouseIntegration\Controller\Adminhtml\AdjustStock;

class NewAction extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Barcode Stock Adjustment"));
        return $resultPage;
    }
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_BarcodeWarehouseIntegration::new_action');
    }
}
