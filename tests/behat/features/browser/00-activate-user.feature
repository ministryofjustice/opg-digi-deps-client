Feature: Browser - add and activate user

    @browser
    Scenario: login and add deputy user
        Given I reset the email log
        Given I am on admin login page
        When I fill in the following:
            | login_email     | ADMIN@PUBLICGUARDIAN.GSI.GOV.UK |
            | login_password  | Abcd1234 |
        Then I click on "login"
        And I am on admin page "/admin"
        Then I create a new "Lay Deputy" user "John" "Doe" with email "behat-user@publicguardian.gsi.gov.uk"

    @browser
    Scenario: login and add user (deputy)
        Given I am on "/logout"
        And I open the "/user/activate/" link from the email
        And I activate the user with password "Abcd1234"

    @browser
    Scenario: add user details (deputy)
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "/user/details"
        When I set the user details to:
            | name | John | Doe |
            | address | 102 Petty France | MOJ | London | SW1H 9AJ | GB |
            | phone | 020 3334 3555  | 020 1234 5678  |
    
    @browser
    Scenario: add client
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "client/add"
        And I save the page as "deputy-step3"
        When I set the client details to:
            | name | Peter | White |
            | caseNumber | 12345ABC |
            | courtDate | 1 | 1 | 2016 |
            | allowedCourtOrderTypes_0 | 1 |
            | address |  1 South Parade | First Floor  | Nottingham  | NG1 2HT  | GB |
            | phone | 0123456789  |

    @browser
    Scenario: create report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then the URL should match "report/create/\d+"
        Then I fill in the following:
            | report_startDate_day | 01 |
            | report_startDate_month | 01 |
            | report_startDate_year | 2016 |
            | report_endDate_day | 31 |
            | report_endDate_month | 12 |
            | report_endDate_year | 2016 |
        And I press "report_save"
        Then the URL should match "report/\d+/overview"
