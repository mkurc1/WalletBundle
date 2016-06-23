<?php

namespace WalletBundle\Transaction\Event;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use FOS\UserBundle\Entity\UserManager;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WalletBundle\Entity\UserWalletInterface;
use WalletBundle\Entity\WalletHistoryInterface;
use WalletBundle\Exception\NotEnoughMoneyException;
use WalletBundle\Service\TransactionInterface;

class TransactionTest extends KernelTestCase
{
    public static function setUpBeforeClass()
    {
        self::bootKernel();
    }

    public function setup()
    {
        $this->purgeDatabase();
    }

    protected function tearDown()
    {
    }

    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->purge();
    }

    protected function createUser($username = 'foo', $password = 'bar')
    {
        /** @var UserManager $userManager */
        $userManager = $this->getService('fos_user.user_manager');
        $user = $userManager->createUser();

        $user->setUsername($username);
        $user->setEmail($username . '@foo.com');
        $user->setPlainPassword($password);
        $user->setEnabled(true);

        $userManager->updateUser($user);

        return $user;
    }

    public function testUserClassImplementInterface()
    {
        $user = $this->createUser();

        $class = new ReflectionClass($user);
        $this->assertTrue($class->implementsInterface(UserWalletInterface::class));
    }

    public function testUserHasWallet()
    {
        /** @var UserWalletInterface $user */
        $user = $this->createUser();
        $wallet = $user->getWallet();

        $this->assertNotNull($wallet);
        $this->assertEquals(0, $wallet->getAvailableAmount());
        $this->assertEquals(0, $wallet->getFreezeAmount());
        $this->assertEquals(0, $wallet->getTotalAmount());
    }

    public function testAddMoneyToWallet()
    {
        /** @var UserWalletInterface $user */
        $user = $this->createUser();
        $wallet = $user->getWallet();

        /** @var TransactionInterface $transaction */
        $transaction = $this->getService('wallet.transaction');

        $transaction->addMoney($user, 199, 'foo');
        $this->assertEquals(199, $wallet->getTotalAmount());
        $this->assertEquals(199, $wallet->getAvailableAmount());

        $lastTransaction = $wallet->getLastTransaction();
        $this->assertNotNull($lastTransaction);
        $this->assertEquals(WalletHistoryInterface::TYPE_INCOME, $lastTransaction->getType());
        $this->assertEquals('foo', $lastTransaction->getName());
        $this->assertEquals(199, $lastTransaction->getAmount());
    }

    public function testTwiceAddMoneyToWallet()
    {
        /** @var UserWalletInterface $user */
        $user = $this->createUser();
        $wallet = $user->getWallet();

        /** @var TransactionInterface $transaction */
        $transaction = $this->getService('wallet.transaction');

        $transaction->addMoney($user, 199, 'foo');
        $transaction->addMoney($user, 249, 'bar', 'foobar');
        $this->assertEquals(448, $wallet->getTotalAmount());
        $this->assertEquals(448, $wallet->getAvailableAmount());

        $lastTransaction = $wallet->getLastTransaction();
        $this->assertNotNull($lastTransaction);
        $this->assertEquals(WalletHistoryInterface::TYPE_INCOME, $lastTransaction->getType());
        $this->assertEquals('bar', $lastTransaction->getName());
        $this->assertEquals(249, $lastTransaction->getAmount());
        $this->assertEquals('foobar', $lastTransaction->getDescription());
    }

    public function testSubMoneyFromWallet()
    {
        /** @var UserWalletInterface $user */
        $user = $this->createUser();
        $wallet = $user->getWallet();

        /** @var TransactionInterface $transaction */
        $transaction = $this->getService('wallet.transaction');

        $transaction->addMoney($user, 600, 'foo');
        $transaction->subMoney($user, 400, 'bar');
        $this->assertEquals(200, $wallet->getTotalAmount());
        $this->assertEquals(200, $wallet->getAvailableAmount());

        $lastTransaction = $wallet->getLastTransaction();
        $this->assertNotNull($lastTransaction);
        $this->assertEquals(WalletHistoryInterface::TYPE_SALARY, $lastTransaction->getType());
        $this->assertEquals('bar', $lastTransaction->getName());
        $this->assertEquals(400, $lastTransaction->getAmount());
    }

    public function testSubMoneyFromWalletWithNotEnoughMoney()
    {
        /** @var UserWalletInterface $user */
        $user = $this->createUser();

        /** @var TransactionInterface $transaction */
        $transaction = $this->getService('wallet.transaction');

        $this->expectException(NotEnoughMoneyException::class);

        $transaction->subMoney($user, 400, 'bar');
    }

    public function testSubMoneyFromWalletWithFreezeMoney()
    {
        /** @var UserWalletInterface $user */
        $user = $this->createUser();
        $wallet = $user->getWallet();

        /** @var TransactionInterface $transaction */
        $transaction = $this->getService('wallet.transaction');

        $transaction->addMoney($user, 600, 'foo');
        $transaction->freezeMoney($user, 50, 'bar');
        $transaction->subMoney($user, 400, 'foobar');
        $this->assertEquals(200, $wallet->getTotalAmount());
        $this->assertEquals(150, $wallet->getAvailableAmount());
        $this->assertEquals(50, $wallet->getFreezeAmount());

        $lastTransaction = $wallet->getLastTransaction();
        $this->assertNotNull($lastTransaction);
        $this->assertEquals(WalletHistoryInterface::TYPE_SALARY, $lastTransaction->getType());
        $this->assertEquals('foobar', $lastTransaction->getName());
        $this->assertEquals(400, $lastTransaction->getAmount());
    }

    public function testSubMoneyFromWalletWithFreezeMoreMoney()
    {
        /** @var UserWalletInterface $user */
        $user = $this->createUser();

        /** @var TransactionInterface $transaction */
        $transaction = $this->getService('wallet.transaction');

        $this->expectException(NotEnoughMoneyException::class);

        $transaction->addMoney($user, 600, 'foo');
        $transaction->freezeMoney($user, 400, 'bar');
        $transaction->subMoney($user, 400, 'foobar');
    }

    public function testFreezeMoneyInWallet()
    {
        /** @var UserWalletInterface $user */
        $user = $this->createUser();
        $wallet = $user->getWallet();

        /** @var TransactionInterface $transaction */
        $transaction = $this->getService('wallet.transaction');

        $transaction->addMoney($user, 600, 'foo');
        $transaction->freezeMoney($user, 400, 'bar');
        $this->assertEquals(600, $wallet->getTotalAmount());
        $this->assertEquals(200, $wallet->getAvailableAmount());
        $this->assertEquals(400, $wallet->getFreezeAmount());

        $lastTransaction = $wallet->getLastTransaction();
        $this->assertNotNull($lastTransaction);
        $this->assertEquals(WalletHistoryInterface::TYPE_RECLAMATION, $lastTransaction->getType());
        $this->assertEquals('bar', $lastTransaction->getName());
        $this->assertEquals(400, $lastTransaction->getAmount());
    }

    public function testFreezeMoneyFromWalletWithNotMoney()
    {
        /** @var UserWalletInterface $user */
        $user = $this->createUser();
        $wallet = $user->getWallet();

        /** @var TransactionInterface $transaction */
        $transaction = $this->getService('wallet.transaction');

        $transaction->freezeMoney($user, 400, 'bar');
        $this->assertEquals(0, $wallet->getAvailableAmount());
        $this->assertEquals(0, $wallet->getTotalAmount());
        $this->assertEquals(400, $wallet->getFreezeAmount());

        $lastTransaction = $wallet->getLastTransaction();
        $this->assertNotNull($lastTransaction);
        $this->assertEquals(WalletHistoryInterface::TYPE_RECLAMATION, $lastTransaction->getType());
        $this->assertEquals('bar', $lastTransaction->getName());
        $this->assertEquals(400, $lastTransaction->getAmount());
    }

    public function testMoveFreezeMoneyFromUserToUser()
    {
        /** @var UserWalletInterface $fromUser */
        $fromUser = $this->createUser('user1');
        $fromWallet = $fromUser->getWallet();

        /** @var UserWalletInterface $toUser */
        $toUser = $this->createUser('user2');
        $toWallet = $toUser->getWallet();

        /** @var TransactionInterface $transaction */
        $transaction = $this->getService('wallet.transaction');

        $transaction->addMoney($fromUser, 600, 'foo');
        $transaction->freezeMoney($fromUser, 400, 'bar');

        $this->assertEquals(200, $fromWallet->getAvailableAmount());
        $this->assertEquals(600, $fromWallet->getTotalAmount());
        $this->assertEquals(400, $fromWallet->getFreezeAmount());

        $this->assertEquals(0, $toWallet->getAvailableAmount());
        $this->assertEquals(0, $toWallet->getAvailableAmount());
        $this->assertEquals(0, $toWallet->getAvailableAmount());

        $transaction->moveFreezeMoneyToUser($fromUser, $toUser, 400, 'foobar');
        $this->assertEquals(400, $toWallet->getAvailableAmount());
        $this->assertEquals(400, $toWallet->getTotalAmount());
        $this->assertEquals(0, $toWallet->getFreezeAmount());

        $this->assertEquals(200, $fromWallet->getAvailableAmount());
        $this->assertEquals(200, $fromWallet->getTotalAmount());
        $this->assertEquals(0, $fromWallet->getFreezeAmount());
    }

    public function testMoveFreezeMoneyFromUserToUserWithNotEnoughMoney()
    {
        /** @var UserWalletInterface $fromUser */
        $fromUser = $this->createUser('user1');
        $fromWallet = $fromUser->getWallet();

        /** @var UserWalletInterface $toUser */
        $toUser = $this->createUser('user2');
        $toWallet = $toUser->getWallet();

        /** @var TransactionInterface $transaction */
        $transaction = $this->getService('wallet.transaction');

        $transaction->freezeMoney($fromUser, 400, 'bar');

        $this->assertEquals(0, $fromWallet->getAvailableAmount());
        $this->assertEquals(0, $fromWallet->getTotalAmount());
        $this->assertEquals(400, $fromWallet->getFreezeAmount());

        $this->assertEquals(0, $toWallet->getAvailableAmount());
        $this->assertEquals(0, $toWallet->getTotalAmount());
        $this->assertEquals(0, $toWallet->getFreezeAmount());

        $this->expectException(NotEnoughMoneyException::class);
        $transaction->moveFreezeMoneyToUser($fromUser, $toUser, 400, 'foobar');
    }

    protected function getService($name)
    {
        return static::$kernel->getContainer()->get($name);
    }
}