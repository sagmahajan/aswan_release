<?php
/**
* Inchoo
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@magentocommerce.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Please do not edit or add to this file if you wish to upgrade
* Magento or this extension to newer versions in the future.
** Inchoo *give their best to conform to
* "non-obtrusive, best Magento practices" style of coding.
* However,* Inchoo *guarantee functional accuracy of
* specific extension behavior. Additionally we take no responsibility
* for any possible issue(s) resulting from extension usage.
* We reserve the full right not to provide any kind of support for our free extensions.
* Thank you for your understanding.
*
* @category Inchoo
* @package GoogleConnect
* @author Marko Martinović <marko.martinovic@inchoo.net>
* @copyright Copyright (c) Inchoo (http://inchoo.net/)
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*/

class Inchoo_GoogleConnect_IndexController extends Mage_Core_Controller_Front_Action
{
    protected $referer = null;

    public function connectAction()
    {
        if(!($this->referer = $this->getRequest()->getParam('state')))
            $this->referer = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        try {
            $this->_connectCallback();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        if (strpos($this->referer, "create")!==false)
            $this->_redirectUrl(urldecode(Mage::getBaseUrl()));
        else
            $this->_redirectUrl(urldecode($this->referer));
    }

    public function disconnectAction()
    {
        $this->referer = Mage::getUrl('googleconnect/account');

        $customer = Mage::getSingleton('customer/session')->getCustomer();

        try {
            $this->_disconnectCallback($customer);
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        $this->_redirectUrl($this->referer);
    }

    protected function _disconnectCallback(Mage_Customer_Model_Customer $customer) {
        Mage::helper('inchoo_googleconnect')->disconnect($customer);

        Mage::getSingleton('core/session')
            ->addSuccess(
                $this->__('You have successfully disconnected your Google account
                    from our store account.')
            );
    }

    protected function _connectCallback() {
        if(!($errorCode = $this->getRequest()->getParam('error')) &&
            !($code = $this->getRequest()->getParam('code'))) {
            // Direct route access - deny
            return;
        }

        if($errorCode) {
            // Google API read light - abort
            if($errorCode === 'access_denied') {
                Mage::getSingleton('core/session')
                    ->addNotice(
                        $this->__('Google Connect process aborted.')
                    );

                return;
            }

            throw new Exception(
                sprintf(
                    $this->__('Sorry, "%s" error occured. Please try again.'),
                    $errorCode
                )
            );

            return;
        }

        if ($code) {
            // Google API green light - proceed
            $model = Mage::getSingleton('inchoo_googleconnect/client');
            $client = $model->getClient();
            $oauth2 = $model->getOauth2();

            $client->authenticate();
            $token = $client->getAccessToken();
            $userInfo = $oauth2->userinfo->get();

            $customersByGoogleId = Mage::helper('inchoo_googleconnect')
                ->getCustomersByGoogleId($userInfo['id']);

            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                // Logged in user
                if($customersByGoogleId->count()) {
                    // Google account already connected to other account - deny
                    Mage::getSingleton('core/session')
                        ->addNotice(
                            $this->__('Your Google account is already connected
                                to one of our store accounts.')
                        );

                    return;
                }

                // Connect from account dashboard - attach
                $customer = Mage::getSingleton('customer/session')->getCustomer();

                Mage::helper('inchoo_googleconnect')->connectByGoogleId(
                    $customer,
                    $userInfo['id'],
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Your Google account is now connected to your
                        store accout. You can now login using our Google Connect
                        button or using store account credentials you will
                        receive to your email address.')
                );

                return;
            }

            if($customersByGoogleId->count()) {
                // Existing connected user - login
                $customer = $customersByGoogleId->getFirstItem();

                Mage::helper('inchoo_googleconnect')->loginByCustomer($customer);

                Mage::getSingleton('core/session')
                    ->addSuccess(
                        $this->__('You have successfully logged in using your
                            Google account.')
                    );

                return;
            }

            $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getWebsite()->getId())
                    ->loadByEmail($userInfo['email']);

            if($customer->getId())  {
                // Email account already exists - attach, login
                Mage::helper('inchoo_googleconnect')->connectByGoogleId(
                    $customer,
                    $userInfo['id'],
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('We have discovered you already have an account at
                        our store. Your Google account is now connected to your
                        store account.')
                );

                return;
            }

            // New connection - create, attach, login
            if(empty($userInfo['given_name'])) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your Google first name.
                        Please try again.')
                );
            }

            if(empty($userInfo['family_name'])) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your Google last name.
                        Please try again.')
                );
            }

            Mage::helper('inchoo_googleconnect')->connectByCreatingAccount(
                $customer,
                $userInfo['email'],
                $userInfo['given_name'],
                $userInfo['family_name'],
                $userInfo['id'],
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Your Google account is now connected to your new user
                    accout at our store. Now you can login using our Google Connect
                    button or using store account credentials you will receive to
                    your email address.')
            );
        }
    }

}
