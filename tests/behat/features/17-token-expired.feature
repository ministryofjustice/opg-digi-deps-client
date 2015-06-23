Feature: Activation link resending
    When the token expires, the user can have the activation link resent and continue from the user activation step
    
    @deputy
    Scenario: Activation link: resend expired link and restart from activation step
        Given I reset the email log
        And I change the user "behat-user@publicguardian.gsi.gov.uk" token to "behatuser123abc" dated last week
        When I go to "/user/activate/behatuser123abc"
        And I save the page as "user-activate-token-expired"
        And I click on "ask-us-to-send-new-link"
        Then I should be on "/user/activate/password/sent/behatuser123abc"
        And I save the page as "user-activate-token-expired-sent"
        And an email with subject "Digideps - activation email" should have been sent to "behat-user@publicguardian.gsi.gov.uk"
        When I open the "/user/activate/" link from the email
        And I save the page as "user-activate-token-expired-click-from-email"
        Then the response status code should be 200
        And the URL should match "/user/activate/[a-z0-9]+"

     @deputy
    Scenario: Send password link: resend expired link and restart from activation step
        Given I reset the email log
        And I change the user "behat-user@publicguardian.gsi.gov.uk" token to "behatuser123abc" dated last week
        When I go to "/user/password-reset/behatuser123abc"
        And I save the page as "password-reset-token-expired"
        And I click on "ask-us-to-send-new-link"
        Then I should be on "/user/activate/password/sent/behatuser123abc"
        And I save the page as "password-reset-token-expired-sent"
        And an email with subject "Reset your password" should have been sent to "behat-user@publicguardian.gsi.gov.uk"
        When I open the "/user/password-reset/" link from the email
        And I save the page as "password-reset-token-expired-click-from-email"
        Then the response status code should be 200
        And the URL should match "/user/password-reset/[a-z0-9]+"
        