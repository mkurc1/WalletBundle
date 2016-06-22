<?php

namespace WalletBundle\Service;

use WalletBundle\Entity\UserWalletInterface;
use WalletBundle\Exception\NotEnoughMoneyException;

interface TransactionInterface
{
    /**
     * @param UserWalletInterface $user
     * @param integer            $amount
     * @param string             $name
     * @param null|string        $description
     * @param array              $data
     * @throws \Exception
     */
    public function addMoney(UserWalletInterface $user, $amount, $name, $description = null, array $data = []);

    /**
     * @param UserWalletInterface $user
     * @param integer            $amount
     * @param string             $name
     * @param null|string        $description
     * @param array              $data
     * @throws NotEnoughMoneyException
     * @throws \Exception
     */
    public function subMoney(UserWalletInterface $user, $amount, $name, $description = null, array $data = []);

    /**
     * @param UserWalletInterface $user
     * @param integer            $amount
     * @param string             $name
     * @param null|string        $description
     * @param array              $data
     * @throws \Exception
     */
    public function freezeMoney(UserWalletInterface $user, $amount, $name, $description = null, array $data = []);

    /**
     * @param UserWalletInterface $fromUser
     * @param UserWalletInterface $toUser
     * @param integer            $amount
     * @param string             $name
     * @param null|string        $description
     * @param array              $data
     * @throws NotEnoughMoneyException
     * @throws \Exception
     */
    public function moveFreezeMoneyToUser(UserWalletInterface $fromUser, UserWalletInterface $toUser, $amount, $name, $description = null, array $data = []);
}