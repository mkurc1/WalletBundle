<?php

namespace WalletBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use WalletBundle\Exception\MoneyTypeNotExistException;

/**
 * @ORM\MappedSuperclass()
 */
abstract class Wallet implements WalletInterface
{
    use TimestampableEntity;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", name="total_amount")
     */
    protected $totalAmount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", name="freeze_amount")
     */
    protected $freezeAmount = 0;

    /**
     * @var ArrayCollection
     */
    protected $histories;


    public function getLastTransaction()
    {
        return $this->getHistories()->last();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param int $totalAmount
     * @return Wallet
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    /**
     * @return int
     */
    public function getAvailableAmount()
    {
        $amount = $this->getTotalAmount() - $this->getFreezeAmount();
        if ($amount > 0) {
            return $amount;
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getFreezeAmount()
    {
        return $this->freezeAmount;
    }

    /**
     * @param int $freezeAmount
     * @return Wallet
     */
    public function setFreezeAmount($freezeAmount)
    {
        $this->freezeAmount = $freezeAmount;
        return $this;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->histories = new ArrayCollection();
    }

    /**
     * Add history
     *
     * @param WalletHistoryInterface $history
     *
     * @return Wallet
     */
    public function addHistory(WalletHistoryInterface $history)
    {
        $history->setWallet($this);
        $this->histories[] = $history;

        return $this;
    }

    /**
     * Remove history
     *
     * @param WalletHistoryInterface $history
     */
    public function removeHistory(WalletHistoryInterface $history)
    {
        $this->histories->removeElement($history);
    }

    /**
     * Get histories
     *
     * @return Collection
     */
    public function getHistories()
    {
        return $this->histories;
    }

    /**
     * @param int $amount
     * @param int $type
     * @return boolean
     * @throws MoneyTypeNotExistException
     */
    public function isAvailableMoney($amount, $type = self::AVAILABLE_MONEY)
    {
        switch ($type) {
            case WalletInterface::AVAILABLE_MONEY:
                return $this->getAvailableAmount() >= $amount;
            case WalletInterface::FREEZE_MONEY:
                return $this->getFreezeAmount() >= $amount && $this->getTotalAmount() >= $amount;
        }

        throw new MoneyTypeNotExistException;
    }
}
