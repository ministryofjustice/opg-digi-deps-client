Feature: deputy / report / account
    
    @deputy
    Scenario: add account
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the first report overview page
        And I follow "tab-accounts"
        And I save the page as "report-account-empty"
        # empty form
        And I press "account_save"
        And I save the page as "report-account-add-error"
        Then the "account_openingDateExplanation" field is expandable
        Then the following fields should have an error:
            | account_bank |
            | account_accountNumber_part_1 |
            | account_accountNumber_part_2 |
            | account_accountNumber_part_3 |
            | account_accountNumber_part_4 |
            | account_sortCode_sort_code_part_1 |
            | account_sortCode_sort_code_part_2 |
            | account_sortCode_sort_code_part_3 |
            | account_openingDate_day |
            | account_openingDate_month |
            | account_openingDate_year |
            | account_openingDateExplanation |
            | account_openingBalance |
        # test validators
        When I fill in the following:
            | account_bank    | HSBC - main account | 
            # invalid number
            | account_accountNumber_part_1 | a | 
            | account_accountNumber_part_2 | b | 
            | account_accountNumber_part_3 | c | 
            | account_accountNumber_part_4 | d | 
            # invalid sort code
            | account_sortCode_sort_code_part_1 | g |
            | account_sortCode_sort_code_part_2 | h |
            | account_sortCode_sort_code_part_3 |  |
            # date outside report range
            | account_openingDate_day   | 5 |
            | account_openingDate_month | 4 |
            | account_openingDate_year  | 1983 |
            | account_openingBalance  | 1,155.00 |
        And I press "account_save"
        Then the following fields should have an error:
            | account_accountNumber_part_1 |
            | account_accountNumber_part_2 |
            | account_accountNumber_part_3 |
            | account_accountNumber_part_4 |
            | account_sortCode_sort_code_part_1 |
            | account_sortCode_sort_code_part_2 |
            | account_sortCode_sort_code_part_3 |
            | account_openingDate_day |
            | account_openingDate_month |
            | account_openingDate_year |
            | account_openingDateExplanation |
        # missing validation for date mismatch and explanation not given
        And I fill in the following:
            | account_bank    | HSBC - main account | 
            | account_accountNumber_part_1 | 8 | 
            | account_accountNumber_part_2 | 7 | 
            | account_accountNumber_part_3 | 6 | 
            | account_accountNumber_part_4 | 5 | 
            | account_sortCode_sort_code_part_1 | 88 |
            | account_sortCode_sort_code_part_2 | 77 |
            | account_sortCode_sort_code_part_3 | 66 |
            | account_openingDate_day   | 5 |
            | account_openingDate_month | 4 |
            | account_openingDate_year  | 2015 |
            | account_openingBalance  | 1,155.00 |
        And I press "account_save"
        Then the following fields should have an error:
          | account_openingDate_day |
          | account_openingDate_month |
          | account_openingDate_year |
          | account_openingDateExplanation |
        # correct values (opening balance explanation added)
        When I fill in "account_openingDateExplanation" with "earlier transaction made with other account"
        And I press "account_save"
        And I save the page as "report-account-list"
        Then the response status code should be 200
        And the form should be valid
        And the URL should match "/report/\d+/accounts"
        And I should see "HSBC - main account" in the "list-accounts" region
        When I click on "account-8765"
        Then I should not see the "opening-balance-explanation" region
        # refresh page and check values
        When I follow "tab-accounts"
        And I should see "HSBC - main account" in the "list-accounts" region
        And I should see "8765" in the "list-accounts" region
        And I should see "£1,155.00" in the "list-accounts" region
        
    @deputy
    Scenario: edit account
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the account "8765" page of the first report
        And I click on "edit-account-details"
        And I save the page as "report-account-edit-start"
        # assert fields are filled in from db correctly
        Then the following fields should have the corresponding values:
            | account_bank    | HSBC - main account | 
            | account_accountNumber_part_1 | 8 | 
            | account_accountNumber_part_2 | 7 | 
            | account_accountNumber_part_3 | 6 | 
            | account_accountNumber_part_4 | 5 | 
            | account_sortCode_sort_code_part_1 | 88 |
            | account_sortCode_sort_code_part_2 | 77 |
            | account_sortCode_sort_code_part_3 | 66 |
            | account_openingDate_day   | 05 |
            | account_openingDate_month | 04 |
            | account_openingDate_year  | 2015 |
            | account_openingBalance  | 1,155.00 |
        # check invalid values
        When I fill in the following:
            | account_bank    |  | 
            | account_accountNumber_part_1 | a | 
            | account_accountNumber_part_2 | 123 | 
            | account_accountNumber_part_3 | - | 
            | account_accountNumber_part_4 |  | 
            | account_sortCode_sort_code_part_1 | a |
            | account_sortCode_sort_code_part_2 | 123 |
            | account_sortCode_sort_code_part_3 |  |
            | account_openingDate_day   |  |
            | account_openingDate_month | 13 |
            | account_openingDate_year  | string |
            | account_openingBalance  |  |
        And I press "account_save"
        Then the following fields should have an error:
            | account_bank |
            | account_accountNumber_part_1 |
            | account_accountNumber_part_2 |
            | account_accountNumber_part_3 |
            | account_accountNumber_part_4 |
            | account_sortCode_sort_code_part_1 |
            | account_sortCode_sort_code_part_2 |
            | account_sortCode_sort_code_part_3 |
            | account_openingDate_day |
            | account_openingDate_month |
            | account_openingDate_year |
            | account_openingBalance |
        And I save the page as "report-account-edit-errors"
        # right values
        When I fill in the following:
            | account_bank    | HSBC main account | 
            | account_accountNumber_part_1 | 1 | 
            | account_accountNumber_part_2 | 2 | 
            | account_accountNumber_part_3 | 3 | 
            | account_accountNumber_part_4 | 4 | 
            | account_sortCode_sort_code_part_1 | 12 |
            | account_sortCode_sort_code_part_2 | 34 |
            | account_sortCode_sort_code_part_3 | 56 |
            | account_openingDate_day   | 1 |
            | account_openingDate_month | 2 |
            | account_openingDate_year  | 2015 |
            | account_openingBalance  | 1,150.00 |
        And I press "account_save"
        # check values are saved
        When I click on "edit-account-details"
        Then the following fields should have the corresponding values:
            | account_bank    | HSBC main account | 
            | account_accountNumber_part_1 | 1 | 
            | account_accountNumber_part_2 | 2 | 
            | account_accountNumber_part_3 | 3 | 
            | account_accountNumber_part_4 | 4 | 
            | account_sortCode_sort_code_part_1 | 12 |
            | account_sortCode_sort_code_part_2 | 34 |
            | account_sortCode_sort_code_part_3 | 56 |
            | account_openingDate_day   | 01 |
            | account_openingDate_month | 02 |
            | account_openingDate_year  | 2015 |
            | account_openingBalance  | 1,150.00 | 
        And I save the page as "report-account-edit-reloaded"


    @deputy
    Scenario: add another account 6666 (will be deleted by next scenario)
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the first report overview page
        Then I add the following bank account:
            | bank    | Barclays acccount to delete | 
            | accountNumber | 6 | 6 | 6 | 6 | 
            | sortCode | 55 | 55 | 55 |
            | openingDate   | 4/4/2015 | 
            | openingDateExplanation  | just a test |
            | openingBalance  | 1,300.00 |
        


    @deputy
    Scenario: delete account 6666 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the accounts page of the first report
        When I click on "account-6666"
        And I click on "edit-account-details"
        # delete and cancel
        And I click on "delete-account"
        And I click on "delete-confirm-cancel"
        # delete and confirm
        And I click on "delete-account"
        And I press "account_delete"
        Then I should not see the "account-6666" link

    @deputy
    Scenario: assert closing balance form is not shown when there are no transactions
        Given I save the application status into "report-no-totals"
        Given I set the report 1 end date to 3 days ago
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the accounts page of the first report
        Then I should not see the "account-closing-balance-form" region
        Then I load the application status from "report-no-totals"
    