Feature: admin

    @deputy
    Scenario: login and add deputy user
        Given I reset the email log
        #And I am on "http://digideps-admin.local/app_dev.php/"
        Given I am on admin login page
        And I save the page as "admin-login"
        Then the response status code should be 200
        # test wrong credentials
        When I fill in the following:
            | login_email     | admin@publicguardian.gsi.gov.uk |
            | login_password  |  WRONG PASSWORD !! |
        And I click on "login"
        Then I should see the "header errors" region
        And I save the page as "admin-login-error1"
        # test user email in caps
        When I fill in the following:
            | login_email     | ADMIN@PUBLICGUARDIAN.GSI.GOV.UK |
            | login_password  | Abcd1234 |
        And I click on "login"
        Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
        #When I go to "http://digideps-admin.local/app_dev.php/logout"
        Given I am not logged into admin
        # test right credentials
        When I fill in the following:
            | login_email     | admin@publicguardian.gsi.gov.uk |
            | login_password  | Abcd1234 |
        And I click on "login"
        #When I go to "/admin"
        Given I am on admin page "/admin"
        # invalid email
        When I fill in the following:
            | admin_email | invalidEmail |
            | admin_firstname | 1 |
            | admin_lastname | 2 |
            | admin_roleId | 2 |
        And I press "admin_save"
        Then the form should be invalid
        And I save the page as "admin-deputy-error1"
        And I should not see "invalidEmail" in the "users" region
        # assert form OK
        When I fill in the following:
            | admin_email | behat-user@publicguardian.gsi.gov.uk |
            | admin_firstname | John |
            | admin_lastname | Doe |
            | admin_roleId | 2 |
        And I click on "save"
        Then I should see "behat-user@publicguardian.gsi.gov.uk" in the "users" region
        Then I should see "Lay Deputy" in the "users" region
        And I save the page as "admin-deputy-added"
        And an email containing a link matching "/user/activate/" should have been sent to "behat-user@publicguardian.gsi.gov.uk"

    @admin
    Scenario: login and add admin user, check audit log
        Given I reset the email log
        And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then the last audit log entry should contain:
          | from | admin@publicguardian.gsi.gov.uk |
          | action | login |
        #When I go to "/admin"
        Given I am on admin page "/admin"
        And I fill in the following:
            | admin_email | behat-admin-user@publicguardian.gsi.gov.uk |
            | admin_firstname | John |
            | admin_lastname | Doe |
            | admin_roleId | 1 |
        And I click on "save"
        Then I should see "behat-admin-user@publicguardian.gsi.gov.uk" in the "users" region
        Then the response status code should be 200
        And I should see "OPG Administrator" in the "users" region
        And I save the page as "admin-admin-added"
        And an email containing a link matching "/user/activate/" should have been sent to "behat-admin-user@publicguardian.gsi.gov.uk"
        And the last audit log entry should contain:
          | from | admin@publicguardian.gsi.gov.uk |
          | action | user_add |
          | user_affected | behat-admin-user@publicguardian.gsi.gov.uk |
        #When I go to "/logout"
        Given I am on admin page "/logout"
        Then the last audit log entry should contain:
          | from | admin@publicguardian.gsi.gov.uk |
          | action | logout |
