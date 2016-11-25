# WalletBundle

The bundle added wallet into Users and allow to managed it. This bundle is for [Symfony](http://symfony.com/) Framework.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2e67737a-4442-4ea7-aff5-f22140427c45/big.png)](https://insight.sensiolabs.com/projects/2e67737a-4442-4ea7-aff5-f22140427c45)

## Configure

Require the bundle with composer:

    $ composer require mkurc1/wallet-bundle

Enable the bundle in the kernel:

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new WalletBundle\WalletBundle(),
            // ...
        );
    }

Create your Wallet class:

    <?php
    
    namespace AppBundle\Entity;
    
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\Mapping as ORM;
    use WalletBundle\Entity\Wallet as BaseWallet;
    
    /**
     * @ORM\Entity
     * @ORM\Table(name="wallet")
     */
    class Wallet extends BaseWallet
    {
        /**
         * @var integer
         *
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;
        
        /**
         * @var ArrayCollection
         *
         * @ORM\OneToMany(targetEntity="AppBundle\Entity\WalletHistory", mappedBy="wallet", cascade={"persist"}, orphanRemoval=true)
         * @ORM\OrderBy({"createdAt"="DESC"})
         */
        protected $histories;
    
        public function __construct()
        {
            parent::__construct();
            // your own logic
        }
    }
    
Create your WalletHistory class:

    <?php
    
    namespace AppBundle\Entity;
    
    use Doctrine\ORM\Mapping as ORM;
    use WalletBundle\Entity\WalletInterface;
    use WalletBundle\Entity\WalletHistory as BaseWalletHistory;
    
    /**
     * @ORM\Entity
     * @ORM\Table(name="wallet_history")
     */
    class WalletHistory extends BaseWalletHistory
    {
        /**
         * @var integer
         *
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;
        
        /**
         * @var WalletInterface
         *
         * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Wallet", inversedBy="histories")
         * @ORM\JoinColumn(nullable=false)
         */
        protected $wallet;
        
        public function __construct($type = null)
        {
            parent::__construct($type);
            // your own logic
        }
    }
    
Implement WalletBundle\Entity\UserWalletInterface on User entity:

    <?php

    namespace AppBundle\Entity;

    use WalletBundle\Entity\WalletInterface;
    use WalletBundle\Entity\UserWalletInterface;

    class User implement UserWalletInterface
    {
        // your user entity logic
        
        /**
         * @var WalletInterface
         *
         * @ORM\OneToOne(targetEntity="AppBundle\Entity\Wallet", cascade={"persist"})
         */
        protected $wallet;
        
        /**
         * @return WalletInterface
         */
        public function getWallet()
        {
            return $this->wallet;
        }
    
        /**
         * @param WalletInterface $wallet
         * @return User
         */
        public function setWallet($wallet)
        {
            $this->wallet = $wallet;
            return $this;
        }
    }
    
Configure your application:

    # app/config/config.yml
    wallet:
        classes:
            wallet: AppBundle\Entity\Wallet # your wallet class
            wallet_history: AppBundle\Entity\WalletHistory # your wallet history class
    
Update your database schema:

    $ php app/console doctrine:schema:update --force

Usages:

    <?php
    
    $entityManager = $this->container->get('doctrine.orm.entity_manager');
    $userRepository = $entityManager->getRepository('AppBundle:User');
    $transaction = $this->container->get('wallet.transaction');
    
    // add money to user account
    $user = $userRepository->findOneByName('foo');
    $amount = 199;
    $transactionName = 'Bonus for your activity'
    $transaction->addMoney($user, $amount, $transactionName);
    
    // subtract money from user account
    $user = $userRepository->findOneByName('foo');
    $amount = 400;
    $transactionName = 'Payment for subscription'
    $transaction->subMoney($user, $amount, $transactionName);
    // if account don't have enough money, method throw exception WalletBundle\Exception\NotEnoughMoneyException
    
    // freeze money on user account (account don't need to have enough money to freeze it)
    $user = $userRepository->findOneByName('foo');
    $amount = 50;
    $transactionName = 'Reclamation'
    $transaction->freezeMoney($user, $amount, $transactionName);
    
    // move freeze money to another user account
    $fromUser = $userRepository->findOneByName('foo');
    $toUser = $userRepository->findOneByName('bar');
    $amount = 50;
    $transactionName = 'Refund'
    $transaction->moveFreezeMoneyToUser($fromUser, $toUser, $amount, $transactionName);
    // if account don't have enough freeze money in wallet, method throw exception WalletBundle\Exception\NotEnoughMoneyException
    
You now can use your wallet system!
    
## License

The bundle is released under the [MIT License](LICENSE).
