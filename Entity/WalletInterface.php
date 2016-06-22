<?php

namespace WalletBundle\Entity;

use WalletBundle\Exception\MoneyTypeNotExistException;

interface WalletInterface
{
    const AVAILABLE_MONEY = 1;
    const FREEZE_MONEY = 2;

    /**
     * @param int $amount
     * @return WalletInterface
     */
    public function setTotalAmount($amount);

    /**
     * @return int
     */
    public function getTotalAmount();

    /**
     * @return int
     */
    public function getAvailableAmount();

    /**
     * @param int $amount
     * @return WalletInterface
     */
    public function setFreezeAmount($amount);

    /**
     * @return int
     */
    public function getFreezeAmount();

    /**
     * @param WalletHistoryInterface $history
     * @return WalletInterface
     */
    public function addHistory(WalletHistoryInterface $history);

    /**
     * @return WalletHistoryInterface|Null
     */
    public function getLastTransaction();

    /**
     * @param int $amount
     * @param int $type
     * @return boolean
     * @throws MoneyTypeNotExistException
     */
    public function isAvailableMoney($amount, $type = self::AVAILABLE_MONEY);
}