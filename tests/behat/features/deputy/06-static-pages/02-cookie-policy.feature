Feature: Cookie Policy

    @deputy
    Scenario: init data
        Given I load the application status from "report-submit-pre"

    @deputy
    Scenario: I see a link in the bottom footer of the service on every page
        Given I go to "/login"
        Then the "Cookies" link, in the footer, url should contain "https://www.gov.uk/help/cookies"
        And I am logged in as "admin@publicguardian.gov.uk" with password "Abcd1234"
        Then the "Cookies" link, in the footer, url should contain "https://www.gov.uk/help/cookies"

    @deputy
    Scenario: When I click on the cookie link it appear in the same window
        Given I go to "/login"
        And I press "Cookies" in the footer
        Then I should be on "https://www.gov.uk/help/cookies"

    @deputy
    Scenario: When I visit the site for the first time I see a cookie banner
        Given I go to "/login"
        Then I should see the cookie warning banner
        And the "Find out more about cookies" link url should contain "https://www.gov.uk/help/cookies"

    @deputy
    Scenario: When I have visited the site previous, I don't see the cookie banner
        Given I go to "/login"
        Then I should not see the cookie warning banner



