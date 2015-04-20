Feature: report
    
    @deputy
    Scenario: test login goes to previous page
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I follow "tab-accounts"
        And I click on "account-n1"
        Then the URL should match "/report/\d+/account/\d+"
        When I expire the session
        And I reload the page
        Then I should be on "/login"
        When I fill in the following:
          | login_email | behat-user@publicguardian.gsi.gov.uk |
          | login_password | Abcd1234 |
        And I submit the form
        Then the URL should match "/report/\d+/account/\d+"
    
        
        