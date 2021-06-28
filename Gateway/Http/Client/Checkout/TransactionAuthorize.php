<?php
/**
 * Copyright © Visiture, LLC. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simon\SecurionPay\Gateway\Http\Client\Checkout;

use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;
use Simon\SecurionPay\Gateway\Http\Client\AbstractTransaction;
use Simon\SecurionPay\Gateway\Http\Client\Adapter\AdapterInterface;
use Simon\SecurionPay\Gateway\Http\Data\ResponseFactory;
use Simon\SecurionPay\Model\Adapter\SecurionPayAdapterFactory;

class TransactionAuthorize extends AbstractTransaction
{
    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * TransactionAuthorize constructor.
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     * @param SecurionPayAdapterFactory $adapterFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        LoggerInterface $logger,
        Logger $customLogger,
        SecurionPayAdapterFactory $adapterFactory,
        ResponseFactory $responseFactory
    ) {
        parent::__construct($logger, $customLogger, $adapterFactory);
        $this->responseFactory = $responseFactory;
    }
    /**
     * @inheritDoc
     */
    protected function process(array $data)
    {
        $response = $this->responseFactory->create();
        $response->setStatus(200);
        $response->setBody([
            AdapterInterface::FIELD_ID => $data[AdapterInterface::FIELD_CHARGE_ID],
            AdapterInterface::FIELD_CUSTOMER_ID => $data[AdapterInterface::FIELD_CUSTOMER_ID]
        ]);
        return $response;
    }
}
