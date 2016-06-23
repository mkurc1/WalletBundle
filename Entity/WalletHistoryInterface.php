<?php

namespace WalletBundle\Entity;

interface WalletHistoryInterface
{
    const TYPE_INCOME = 1;
    const TYPE_SALARY = 2;
    const TYPE_RECLAMATION = 3;

    /**
     * @return integer
     */
    public function getAmount();

    /**
     * @param integer $amount
     * @return WalletHistoryInterface
     */
    public function setAmount($amount);

    /**
     * @return integer
     */
    public function getType();

    /**
     * @param integer $type
     * @return WalletHistoryInterface
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return WalletHistoryInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return WalletHistoryInterface
     */
    public function setDescription($description);

    /**
     * @return array
     */
    public function getData();

    /**
     * @param array $data
     * @return WalletHistoryInterface
     */
    public function setData(array $data = []);

    /**
     * @param WalletInterface $wallet
     * @return WalletHistoryInterface
     */
    public function setWallet(WalletInterface $wallet);
}