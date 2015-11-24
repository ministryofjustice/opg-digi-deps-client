<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;

trait EmailTrait
{

    /**
     * @param boolean $throwExceptionIfNotFound
     * @param integer $index = last (default), 1=second last
     * 
     * @return array|null
     */
    private function getEmailMockFromApi($throwExceptionIfNotFound = true, $index ='last')
    {
        $this->visitBehatLink('email-get-last');

        $emailsJson = $this->getSession()->getPage()->getContent();
        $emailsArray = json_decode($emailsJson , true);

        if ($throwExceptionIfNotFound && empty($emailsArray[0]['to'])) {
            throw new \RuntimeException("No email has been sent. Api returned: " . $emailsJson );
        }
        
        // translate index into number
        $map = ['last'=>0, 'second_last'=>1];
        if (!isset($map[$index])) {
             throw new \RuntimeException("position $index not recognised");
        }
        $indexNumber = $map[$index];
        
        return isset($emailsArray[$indexNumber]) ? $emailsArray[$indexNumber] : null;
    }

    /**
     * @Given I reset the email log
     */
    public function iResetTheEmailLog()
    {
        $this->visitBehatLink('email-reset');
        $this->assertResponseStatus(200);

        $this->assertNoEmailShouldHaveBeenSent();
    }


    /**
     * @Then no email should have been sent
     */
    public function assertNoEmailShouldHaveBeenSent()
    {
        $content = $this->getEmailMockFromApi(false);
        if ($content) {
            throw new \RuntimeException("Found unexpected email with subject '" . $content['subject'] . "'");
        }
    }

    
    /**
     * @param string $regexpr
     * @return string link matching the given pattern
     * 
     * @throws \Exception
     */
    private function getFirstLinkInEmailMatching($regexpr)
    {
        list($links, $mailContent) = $this->getLinksFromEmailHtmlBody();

        $filteredLinks = array_filter($links, function ($element) use ($regexpr) {
            return preg_match('#'.$regexpr.'#i', $element);
        });

        if (empty($filteredLinks)) {
            throw new \Exception("no link in the email's body. Filter: $regexpr . Body:\n $mailContent");
        }
        if (count(array_unique($filteredLinks)) > 1) {
            throw new \Exception("more than one link found in the email's body. Filter: $regexpr . Links: " . implode("\n", $filteredLinks).". Body:\n $mailContent");
        }
        
        return array_shift($filteredLinks);
    }
    
    
    /**
     * @When I open the :regexpr link from the email
     */
    public function iOpenTheSpecificLinkOnTheEmail($regexpr)
    {
        $linkToClick = $this->getFirstLinkInEmailMatching($regexpr);
        
        // visit the link
        $this->visit($linkToClick);
    }
    
    
    /**
     * @When I open the :regexpr link from the email on the :area area
     */
    public function iOpenTheLinkFromTheEmailOnTheArea($regexpr, $area)
    {
        $linkToClick = $this->getFirstLinkInEmailMatching($regexpr);
        
        if ($area == 'admin') {
            $linkToClick = str_replace($this->getSymfonyParam('non_admin_host'), $this->getSymfonyParam('admin_host'), $linkToClick);
        } else if ($area == 'deputy') {
            $linkToClick = str_replace($this->getSymfonyParam('admin_host'), $this->getSymfonyParam('non_admin_host'), $linkToClick);
        } else {
            throw new \RuntimeException(__METHOD__ . ": $area not defined");
        }
        
        // visit the link
        $this->visit($linkToClick);
    }
    
    
    /**
     * @Then the last email containing a link matching :partialLink should have been sent to :to
     */
    public function anEmailContainingALinkMatchingShouldHaveBeenSentTo($partialLink, $to)
    {
        $this->getFirstLinkInEmailMatching($partialLink);
        
        $mail = $this->getEmailMockFromApi();
        $mailTo = key($mail['to']);

        if ($mailTo !== 'the specified email address' && $mailTo != $to) {
            throw new \RuntimeException("Addressee '" . $mailTo . "' does not match the expected '" . $to . "'");
        }
    }
    
    /**
     * @Then the :index email should have been sent to :to
     */
    public function theWhichEmailShouldHaveBeenSentTo($index, $to)
    {
        $mail = $this->getEmailMockFromApi(true, $index);
        $mailTo = key($mail['to']);

        if ($mailTo !== 'the specified email address' && $mailTo != $to) {
            throw new \RuntimeException("Addressee '" . $mailTo . "' does not match the expected '" . $to . "'");
        }
    }
    
    
    /**
     * @Then the :index email :contentType part should contain the following:
     */
    public function theEmailAttachmentShouldContain($index, $contentType, TableNode $table)
    {
        $mail = $this->getEmailMockFromApi(true, $index);
        
        // find body of the part with the given contentType
        $part = array_filter($mail['parts'], function($part) use ($contentType) {
            return $part["contentType"] === $contentType;
        });
        if (empty($part)) {
            throw new \RuntimeException("part $contentType not found in $index email");
        }
        $html = array_shift($part)['body'];
        
        $doc = new \DOMDocument();    
        $doc->loadHtml($html);
        
        foreach($table->getRowsHash() as $id => $mustContain) {
           $element = $doc->getElementById($id);
           if (!$element) {
               throw new \RuntimeException("node #$id not found");
           }
           $textContent = $element->textContent;
           if (strpos(strtolower($textContent), strtolower($mustContain)) === false) {
               throw new \RuntimeException("#$id element: $mustContain' not found inside '$textContent'");
           }
        }
    }

    /**
     * @return array[array links, string mailContent]
     */
    private function getLinksFromEmailHtmlBody()
    {
        $mailContent = $this->getEmailMockFromApi()['parts'][0]['body'];

        preg_match_all('#https?://[^\s"<]+#', $mailContent, $matches);

        return [$matches[0], $mailContent];
    }
    
    /**
     * @Then the last email should contain :text
     */
    public function mailContainsText($text)
    {
        $mailContent = $this->getEmailMockFromApi()['parts'][0]['body'];

        if (strpos($mailContent, $text) === FALSE) {
            throw new \Exception("Text: $text not found in email. Body: \n $mailContent");
        }
    }

}
