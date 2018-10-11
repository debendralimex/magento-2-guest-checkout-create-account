<?php

namespace Debendra\GuestRegistration\Observer;

class Convertguest implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Api\OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Convertguest constructor.
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_orderFactory = $orderFactory;
        $this->orderCustomerService = $orderCustomerService;
        $this->_customer = $customer;
        $this->orderRepository = $orderRepository;
        $this->_storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();

        if (count($orderIds)) {

            $orderId = $orderIds[0];
            $order = $this->_orderFactory->create()->load($orderId);

            $customer= $this->_customer->create();
            $customer->setWebsiteId($this->_storeManager->getStore()->getWebsiteId());
            $customer->loadByEmail($order->getCustomerEmail());

            /*Convert guest to customer*/
            if ($order->getId() && !$customer->getId()) {
                /*New Customer*/
                $this->orderCustomerService->create($orderId);
            } else {
                /*Registered customer guest checkout*/
                $order->setCustomerId($customer->getId());
                $order->setCustomerIsGuest(0);
                $this->orderRepository->save($order);
            }
        }
    }
}