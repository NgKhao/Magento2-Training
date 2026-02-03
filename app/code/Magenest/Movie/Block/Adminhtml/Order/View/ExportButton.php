<?php

namespace Magenest\Movie\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Widget\Context;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\ConfigInterface;

class ExportButton extends View
{
    protected Context $context;

    protected function _construct()
    {
        parent::_construct();

        $order = $this->getOrder();
        if (!$order) {
            return;
        }

        $this->addButton(
            'order_export_csv',
            [
                'label' => __('Export CSV'),
                'class' => 'action-secondary',
                'onclick' => sprintf(
                    "setLocation('%s')",
                    $this->getExportCsvUrl()
                ),
                'sort_order' => 90
            ]
        );
    }

    /**
     * Get export CSV URL
     */
    public function getExportCsvUrl(): string
    {
        return $this->getUrl(
            'movie/order/export',
            ['order_id' => $this->getOrderId()]
        );
    }
}
