<?php

namespace Simon\SecurionPay\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Session\SessionManagerInterface;
use Simon\SecurionPay\Gateway\Config\Checkout\Config;
use Simon\SecurionPay\Gateway\Http\Client\Adapter\AdapterInterface;
use Simon\SecurionPay\Helper\Currency as CurrencyHelper;
use Simon\SecurionPay\Model\Adapter\SecurionPayAdapterFactory;

class Signature extends Action
{
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
     * @var SessionManagerInterface
     */
    protected $sessionManager;
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * Signature constructor.
     * @param Context $context
     * @param SecurionPayAdapterFactory $adapterFactory
     * @param Config $config
     * @param CurrencyHelper $currencyHelper
     * @param SessionManagerInterface $sessionManager
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        SecurionPayAdapterFactory $adapterFactory,
        Config $config,
        CurrencyHelper $currencyHelper,
        SessionManagerInterface $sessionManager,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->adapterFactory = $adapterFactory;
        $this->config = $config;
        $this->currencyHelper = $currencyHelper;
        $this->sessionManager = $sessionManager;
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
        $params = $this->getRequest()->getParams();
        if (!array_key_exists(AdapterInterface::FIELD_CURRENCY, $params) ||
            !array_key_exists(AdapterInterface::FIELD_AMOUNT, $params)
        ) {
            return $this->createResponse([
                'message' => 'Invalid Request'
            ])->setHttpResponseCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }

        try {
            $storeId = $this->sessionManager->getStoreId();
            $response = $this->adapterFactory->create()->getCheckout([
                AdapterInterface::FIELD_CURRENCY => $params[AdapterInterface::FIELD_CURRENCY],
                AdapterInterface::FIELD_AMOUNT => $this->currencyHelper->getMinorUnits(
                    $params[AdapterInterface::FIELD_AMOUNT]
                ),
                AdapterInterface::FIELD_REQUIRE_ATTEMPT => $this->config->isThreeDSecureEnabled($storeId)
            ]);
            $body = $response->getBody();
            return $this->createResponse([
                'signature' => $body['signature']
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
