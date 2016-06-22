<?php

namespace WalletBundle\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use WalletBundle\Exception\WrongWalletHistoryTypeException;

/**
 * @ORM\MappedSuperclass()
 */
abstract class WalletHistory implements WalletHistoryInterface
{
    use TimestampableEntity;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $amount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $type = WalletHistoryInterface::TYPE_INCOME;

    /**
     * @var WalletInterface
     */
    protected $wallet;

    /**
     * @var string
     *
     * @ORM\Column(type="array")
     */
    protected $data = [];


    public function __construct($type = null)
    {
        $types = [
            WalletHistoryInterface::TYPE_INCOME,
            WalletHistoryInterface::TYPE_PAYMENT,
            WalletHistoryInterface::TYPE_RECLAMATION
        ];

        if (null !== $type) {
            if (false === array_key_exists($type, array_flip($types))) {
                throw new WrongWalletHistoryTypeException;
            }

            $this->setType($type);
        }
    }

    public function __toString()
    {
        return (string)$this->getName();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return WalletHistoryInterface
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return WalletHistoryInterface
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return WalletHistoryInterface
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return WalletHistoryInterface
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return WalletHistoryInterface
     */
    public function setData(array $data = [])
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return WalletInterface
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * @param WalletInterface $wallet
     * @return WalletHistoryInterface
     */
    public function setWallet(WalletInterface $wallet)
    {
        $this->wallet = $wallet;
        return $this;
    }
}