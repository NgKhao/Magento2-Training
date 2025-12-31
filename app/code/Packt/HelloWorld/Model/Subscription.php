<?php
namespace Packt\HelloWorld\Model;

class Subscription extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DECLINED = 'declined';
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    public function _construct()
    {
        $this->_init('Packt\HelloWorld\Model\ResourceModel\Subscription');
//        Nhiệm vụ: Nó trả lời câu hỏi "Tôi là Model Subscription, nhưng khi tôi muốn lưu xuống database, tôi phải nhờ ai?".
//
//    Tham số: Đường dẫn đến file Resource Model (file bạn đã hỏi ở câu trước).
//
//Cơ chế: Khi bạn gọi $model->save(), Magento sẽ nhìn vào hàm _init này, tìm đến Resource Model kia và bảo "Ê, lưu hộ tao cái này vào bảng packt_helloworld_subscription với".
    }
}
