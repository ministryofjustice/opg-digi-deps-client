Feature: deputy / password reset

    @deputy
    Scenario: Password reset
      Given I load the application status from "report-submit-pre"
      And I save the application status into "reset-password-start"
      And emails are sent from "deputy" area
      And I go to "/logout"
      And I go to "/login"
      When I click on "forgotten-password"
      # empty form
      And I fill in "password_forgotten_email" with ""
      And I press "password_forgotten_submit"
      Then the form should be invalid
      # invalid email
      When I fill in "password_forgotten_email" with "invalidemail"
      And I press "password_forgotten_submit"
      Then the form should be invalid
      # non-existing email (no email is sent)
      When I fill in "password_forgotten_email" with "ehat-not-existing@publicguardian.gov.uk"
      And I press "password_forgotten_submit"
      Then the form should be valid
      And I click on "return-to-login"
      And no "deputy" email should have been sent to "ehat-not-existing@publicguardian.gov.uk"
      # existing email (email is now sent)
      When I go to "/login"
      And I click on "forgotten-password"
      And I fill in "password_forgotten_email" with "BEHAT-UsEr@publicguardian.gov.uk"
      And I press "password_forgotten_submit"
      Then the form should be valid
      And I click on "return-to-login"
      And the last email should have been sent to "behat-user@publicguardian.gov.uk"
      # open password reset page
      When I open the "/user/password-reset/" link from the email
      # empty
      When I fill in the following:
          | reset_password_password_first   |  |
          | reset_password_password_second  |  |
      And I press "reset_password_save"
      Then the form should be invalid
      #password mismatch
      When I fill in the following:
          | reset_password_password_first   | Abcd1234 |
          | reset_password_password_second  | Abcd12345 |
      And I press "reset_password_save"
      Then the form should be invalid
      # (nolowercase, nouppercase, no number skipped as already tested in "set password" scenario)
      # correct !!
      When I fill in the following:
          | reset_password_password_first   | Abcd12345 |
          | reset_password_password_second  | Abcd12345 |
      And I press "reset_password_save"
      Then the form should be valid
      And the URL should match "/lay"
      # test login
      Given I am logged in as "behat-user@publicguardian.gov.uk" with password "Abcd12345"
      Then the URL should match "/lay"
      # assert set password link is not accessible
      When I open the "/user/password-reset/" link from the email
      Then the response status code should be 500
      # restore previous password
      And I load the application status from "reset-password-start"
