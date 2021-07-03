<?php
/**
 * Copyright © Visiture, LLC. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Simon\SecurionPay\Api\Data;

interface ThreeDSecureInformationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const AMOUNT = 'amount';

    const CURRENCY = 'currency';

    const TOKEN = 'token';

    /**
     * Get the amount.
     *
     * @return int
     */
    public function getAmount();

    /**
     * Set the amount.
     *
     * @param int $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get the currency code.
     *
     * @return string
     */
    public function getCurrency();

    /**
     * Set the currency code.
     *
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency);

    /**
     * Get the token ID.
     *
     * @return string
     */
    public function getToken();

    /**
     * Set the token ID.
     *
     * @param string $token
     * @return $this
     */
    public function setToken($token);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Simon\SecurionPay\Api\Data\ThreeDSecureInformationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * @param \Simon\SecurionPay\Api\Data\ThreeDSecureInformationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Simon\SecurionPay\Api\Data\ThreeDSecureInformationExtensionInterface $extensionAttributes
    );
}
