<?php
namespace Lof\BarcodeWarehouseIntegration\Block\Adminhtml\System\Config;

/**
 * Class GenerateBarcode
 * @package Lof\BarcodeInventory\Block\Adminhtml\System\Config
 */
class Generate extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }
    /**
     * Add color picker
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return String
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = "";
        $url = $this->_backendUrl->getUrl("lof_barcode_warehouse/system_config/generate", []);
        $html .= '
        <div class="pp-buttons-container"><button type="button" id="generate_barcode_warehouse"><span><span><span>'.__("GENERATE").'</span></span></span></button></div>
        <style>
        #btn_id{
            width:558px;
            background-color: #eb5202;
            color: #fff;
        }
        </style>';
        $html .= "
        <script>
        require([
                'jquery',
                'Magento_Ui/js/modal/confirm'
            ],
            function($, confirmation) {
                $('#generate_barcode_warehouse').on('click', function(event){
                    event.preventDefault;
                    confirmation({
                        title: '".__("You are really want to generate all barcode?")."',
                        content: '',
                        actions: {
                            confirm: function () {
                            jQuery.ajax({
                                    url : '$url',
                                    showLoader: true,
                                    type : 'POST',
                                    data: {
                                        format: 'json'
                                    },
                                    dataType:'json',
                                    success : function() {              
                                        alert('All Barcodes are Generated');
                                    },
                                    error : function(request,error)
                                    {
                                        alert(\"Error\");
                                    }
                                });
                            },
                        }
                    });
                });
            });</script>";
        return $html;
    }
}
