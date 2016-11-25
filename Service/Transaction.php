<?php

namespace WalletBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use WalletBundle\Entity\UserWalletInterface;
use WalletBundle\Entity\WalletHistoryInterface;
use WalletBundle\Entity\WalletInterface;
use WalletBundle\Exception\NotEnoughMoneyException;

class Transaction implements TransactionInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;
    
    /**
     * @var WalletHistoryInterface
     */
    private $walletHistory;


    public function __construct(EntityManagerInterface $em, WalletHistoryInterface $walletHistory)
    {
        $this->em = $em;
        $this->walletHistory = $walletHistory;
    }

    /**
     * {@inheritdoc}
     */
    public function addMoney(UserWalletInterface $user, $amount, $name, $description = null, array $data = [])
    {
        $wallet = $user->getWallet();

        $this->em->getConnection()->beginTransaction();
        try {
            $wallet->setTotalAmount($wallet->getTotalAmount() + $amount);
            $this->addWalletHistory($wallet, WalletHistoryInterface::TYPE_INCOME, $amount, $name, $description, $data);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function subMoney(UserWalletInterface $user, $amount, $name, $description = null, array $data = [])
    {
        $wallet = $user->getWallet();

        if (false === $wallet->isAvailableMoney($amount)) {
            throw new NotEnoughMoneyException;
        }

        $this->em->getConnection()->beginTransaction();
        try {
            $wallet->setTotalAmount($wallet->getTotalAmount() - $amount);
            $this->addWalletHistory($wallet, WalletHistoryInterface::TYPE_SALARY, $amount, $name, $description, $data);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function freezeMoney(UserWalletInterface $user, $amount, $name, $description = null, array $data = [])
    {
        $wallet = $user->getWallet();

        $this->em->getConnection()->beginTransaction();
        try {
            $wallet->setFreezeAmount($wallet->getFreezeAmount() + $amount);
            $this->addWalletHistory($wallet, WalletHistoryInterface::TYPE_RECLAMATION, $amount, $name, $description, $data);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function moveFreezeMoneyToUser(UserWalletInterface $fromUser, UserWalletInterface $toUser, $amount, $name, $description = null, array $data = [])
    {
        $fromWallet = $fromUser->getWallet();
        $toWallet = $toUser->getWallet();

        if (false === $fromWallet->isAvailableMoney($amount, WalletInterface::FREEZE_MONEY)) {
            throw new NotEnoughMoneyException;
        }

        $this->em->getConnection()->beginTransaction();
        try {
            $fromWallet->setTotalAmount($fromWallet->getTotalAmount() - $amount);
            $fromWallet->setFreezeAmount($fromWallet->getFreezeAmount() - $amount);

            $toWallet->setTotalAmount($toWallet->getTotalAmount() + $amount);
            $this->addWalletHistory($toWallet, WalletHistoryInterface::TYPE_INCOME, $amount, $name, $description, $data);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    protected function addWalletHistory(WalletInterface $wallet, $type, $amount, $name, $description = null, array $data = [])
    {
        /** @var WalletHistoryInterface $history */
        $history = new $this->walletHistory($type);
        $history->setAmount($amount);
        $history->setName($name);
        $history->setDescription($description);
        $history->setData($data);

        $wallet->addHistory($history);
    }
}
