<?php

namespace Simon\SecurionPay\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Simon\SecurionPay\Gateway\Config\Checkout\Config;
use Simon\SecurionPay\Gateway\Http\Data\Request;
use Simon\SecurionPay\Helper\Currency as CurrencyHelper;
use Simon\SecurionPay\Model\Adapter\SecurionPayAdapterFactory;

class LoadCheckoutRequest extends Action
{
    const ADMIN_RESOURCE = 'Simon_SecurionPay::get_checkout_request';

    /**
     * @var SecurionPayAdapterFactory
     */
    protected $adapterFactory;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var CurrencyHelper
     */
    protected $currencyHelper;
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var Quote
     */
    private $quote;

    /**
     * Signature constructor.
     * @param Context $context
     * @param SecurionPayAdapterFactory $adapterFactory
     * @param Quote $quote
     * @param Config $config
     * @param CurrencyHelper $currencyHelper
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        SecurionPayAdapterFactory $adapterFactory,
        Quote $quote,
        Config $config,
        CurrencyHelper $currencyHelper,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->adapterFactory = $adapterFactory;
        $this->quote = $quote;
        $this->config = $config;
        $this->currencyHelper = $currencyHelper;
        $this->jsonFactory = $jsonFactory;
    }

    public function dispatch(RequestInterface $request)
    {
        if (!$this->config->isActive()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('noRoute');

            return $resultRedirect;
        }

        return parent::dispatch($request); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $quote = $this->quote->getQuote();
        if (!$quote->getId()) {
            return $this->createResponse([
                'message' => 'Quote does not exist.'
            ])->setHttpResponseCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }

        try {
            $response = $this->adapterFactory->create()->getCheckout([
                Request::FIELD_CURRENCY => $quote->getBaseCurrencyCode(),
                Request::FIELD_REQUIRE_ATTEMPT => false,
                Request::FIELD_AMOUNT => $this->currencyHelper->getMinorUnits($quote->getGrandTotal())
            ])->getBody();
            return $this->createResponse([
                'signature' => $response['signature']
            ]);
        } catch (\Exception $e) {
            return $this->createResponse([
                'message' => $e->getMessage()
            ])->setHttpResponseCode(\Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param array $data
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function createResponse(array $data = [])
    {
        $result = $this->jsonFactory->create();
        $result->setData($data);
        return $result;
    }
}
