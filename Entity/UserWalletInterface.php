<?php

namespace WalletBundle\Entity;

interface UserWalletInterface
{
    /**
     * @return WalletInterface
     */
    public function getWallet();
}