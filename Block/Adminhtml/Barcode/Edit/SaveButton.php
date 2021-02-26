<?php
namespace Lof\BarcodeWarehouseIntegration\Block\Adminhtml\Barcode\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class UpdateButton
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $url = "lof_barcode_warehouse/barcode/update";
        return [
            'label' => __('Update Stock by Barcode'),
            'class' => 'save primary',
            'on_click' => sprintf("location.href = '%s';", $this->getUpdateUrl()),
            'sort_order' => 90,
        ];
    }
    public function getUpdateUrl()
    {
        return $this->getUrl('lof_barcode_warehouse/adjuststock/save');
    }
}

