<?php

namespace DigidepsBehat;

trait AuthenticationTrait
{
    
    /**
     * @Given I am logged in as :email with password :password
     */
    public function iAmLoggedInAsWithPassword($email, $password)
    {
        $this->visitPath('/logout');
        $this->visitPath('/login');
        $this->fillField('login_email',$email);
        $this->fillField('login_password', $password);
        $this->pressButton('login_login');
        $this->assertResponseStatus(200);
    }
    
    /**
     * @Given I am logged in to admin as :email with password :password
     */
    public function iAmLoggedInToAdminAsWithPassword($email, $password)
    {
        $adminUrl = $this->getSymfonyParam('admin_host');
        $this->visitPath($adminUrl.'/logout');
        $this->iAmAtAdminLogin();
        $this->fillField('login_email',$email);
        $this->fillField('login_password', $password);
        $this->pressButton('login_login');
        $this->assertResponseStatus(200);
    }
    
    /**
     * @Given I am not logged into admin
     */
    public function notLoggedInAdmin() {
        $this->iAmAtAdminLogin();
        $this->visitPath('/logout');
    }
    
    /**
     * @Given I am not logged in
     */
    public function iAmNotLoggedIn()
    {
        $this->visitPath('/logout');
    }

    /**
     * @Given I am on the login page
     */
    public function iAmAtLogin()
    {
        $this->visitPath('/login');
    }
   
    /**
     * @Given I am on admin login page
     */
    public function iAmAtAdminLogin()
    {
        $adminUrl = $this->getSymfonyParam('admin_host');
        $this->visitPath($adminUrl.'/login');
    }
    
    /**
     * @Then the URL :url should not be accessible
     */
    public function theUrlShouldNotBeAccessible($url)
    {
        $previousUrl = $this->getSession()->getCurrentUrl();
        $this->visit($url);
        $this->assertResponseStatus(500);
        $this->visit($previousUrl);
    }
    
    /**
     * @Then I expire the session
     */
    public function iExpireTheSession()
    {
        $this->getSession()->setCookie($this->sessionName, null);
    }   
    
}