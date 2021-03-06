<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;

trait AuthenticationTrait
{
    /**
     * @Given I am logged in as :email with password :password
     */
    public function iAmLoggedInAsWithPassword($email, $password)
    {
        $this->visitPath('/logout');
        $this->visitPath('/login');
        $this->fillField('login_email', $email);
        $this->fillField('login_password', $password);
        $this->pressButton('login_login');
    }

    /**
     * @Given I am logged in to admin as :email with password :password
     */
    public function iAmLoggedInToAdminAsWithPassword($email, $password)
    {
        $this->visitAdminPath('/logout');

        $this->iAmAtAdminLogin();
        $this->fillField('login_email', $email);
        $this->fillField('login_password', $password);
        $this->pressButton('login_login');
        $this->theFormShouldBeValid();
    }

    /**
     * @deprecated Use  I am on admin page "/login" instead
     * @Given I am on admin login page
     */
    public function iAmAtAdminLogin()
    {
        $this->visitAdminPath('/login');
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
     * @Then the admin URL :url should not be accessible
     */
    public function theAdminUrlShouldNotBeAccessible($url)
    {
        $previousUrl = $this->getSession()->getCurrentUrl();
        $this->visitAdminPath($url);
        $this->assertResponseStatus(500);
        $this->visit($previousUrl);
    }

    /**
     * @Then the URL :url should be forbidden
     */
    public function theUrlShouldBeForbidden($url)
    {
        $previousUrl = $this->getSession()->getCurrentUrl();
        $this->visit($url);
        $this->assertResponseStatus(403);
        $this->visit($previousUrl);
    }

    /**
     * @Then I expire the session
     */
    public function iExpireTheSession()
    {
        $this->getSession()->setCookie($this->sessionName, null);
    }

    /**
     * @Then the following :area pages should return the following status:
     */
    public function theFollowingPagesShouldReturnTheFollowingStatus($area, TableNode $table)
    {
        foreach ($table->getRowsHash() as $url => $expectedReturnCode) {
            $area =='admin' ? $this->visitAdminPath($url): $this->visitPath($url);
            $actual = $this->getSession()->getStatusCode();
            if (intval($expectedReturnCode) !== intval($actual)) {
                throw new \RuntimeException("$url: Current response status code is $actual, but $expectedReturnCode expected.");
            }
        }
    }
}
