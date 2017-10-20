Feature: deputy / report / edit client

    @deputy
    Scenario: edit client details
        Given I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "user-account, client-show, client-edit"
        Then the following fields should have the corresponding values:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 12345ABC |
            | client_courtDate_day | 01 |
            | client_courtDate_month | 01 |
            | client_courtDate_year | 2016 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        When I fill in the following:
            | client_firstname | |
            | client_lastname |  |
            | client_caseNumber |  |
            | client_courtDate_day | |
            | client_courtDate_month | |
            | client_courtDate_year | |
            | client_address |  |
            | client_address2 |  |
            | client_county | |
            | client_postcode | |
            | client_country | |
            | client_phone | aaa |
        And I press "client_save"
        Then the following fields should have an error:
            | client_firstname |
            | client_lastname |
            | client_courtDate_day |
            | client_courtDate_month |
            | client_courtDate_year |
            | client_caseNumber |
            | client_caseNumber |
            | client_address |
            | client_postcode |
            | client_phone |
        When I fill in the following:
            | client_firstname | Nolan |
            | client_lastname | Ross |
            | client_caseNumber | 12345ABC |
            | client_courtDate_day | 1 |
            | client_courtDate_month | 1 |
            | client_courtDate_year | 2016 |
            | client_address |  2 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I press "client_save"
        Then I should be on "/deputyship-details/your-client"
        And I should see "12345ABC" in the "case-number" region
        And I should see "NG1 2HT" in the "client-address-postcode" region
        When I click on "client-edit"
        Then the following fields should have the corresponding values:
            | client_firstname | Nolan |
        
