<?php
/**
 * Adiacent srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@adiacent.com so we can send you a copy immediately.
 *
 * @category   Adiacent
 * @package    Adiacent_Autologin
 * @copyright  Copyright (c) 2016 Adiacent srl (http://www.adiacent.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Adiacent\Autologin\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Integration\Model\Oauth\Token;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\SessionManagerInterface;

class Index extends Action
{
    const COOKIE_NO_PRIVACY = 'privacy_mobile';
    /**
     * @var CollectionFactory
     */
    protected $customerCollection;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var TokenFactory
     */
    protected $tokenFactory;
    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    public function __construct(
        Context $context,
        CollectionFactory $customerCollection,
        Session $customerSession,        
        TokenFactory $tokenFactory,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    )
    {
        parent::__construct($context);

        $this->customerCollection = $customerCollection;
        $this->customerSession = $customerSession;
        $this->tokenFactory = $tokenFactory;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    public function execute()
    {
        $tokenString = $this->getRequest()->getParam('token');

        if($tokenString && !$this->customerSession->isLoggedIn())
        {
          $token = $this->getToken($tokenString);
          if ($token) {
            $customerId = $token->getCustomerId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
            $this->customerSession->setCustomerAsLoggedIn($customer);
          }
        }
        $metadata = $this->cookieMetadataFactory
          ->createPublicCookieMetadata()
          ->setDuration(86400) //Un giorno
          ->setPath($this->sessionManager->getCookiePath())
          ->setDomain($this->sessionManager->getCookieDomain());

        $this->cookieManager->setPublicCookie(
          self::COOKIE_NO_PRIVACY,
          '1',
          $metadata
        );
        $this->_redirect($this->_redirect->getRefererUrl());
        return;
    }

    private function getToken($tokenString)
    {
      $token = $this->tokenFactory->create()->loadByToken($tokenString);
      if ($token->getId() && !$token->getRevoked()) {
        $userType = $token->getUserType();
        if ($userType ==  UserContextInterface::USER_TYPE_CUSTOMER) {
          return $token;
        }
      }
      return null;
    }
}