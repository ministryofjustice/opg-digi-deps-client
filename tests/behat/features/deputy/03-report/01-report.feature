Feature: deputy / report / edit and test tabs
            
    @deputy
    Scenario: edit report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I click on "client-home"
        And I click on "edit-report-period-2016-report"
        Then the following fields should have the corresponding values:
            | report_edit_startDate_day | 01 |
            | report_edit_startDate_month | 01 |
            | report_edit_startDate_year | 2016 |
            | report_edit_endDate_day | 31 |
            | report_edit_endDate_month | 12 |
            | report_edit_endDate_year | 2016 |
        # check validations
        When I fill in the following:
            | report_edit_startDate_day | aa |
            | report_edit_startDate_month | bb |
            | report_edit_startDate_year | c |
            | report_edit_endDate_day |  |
            | report_edit_endDate_month |  |
            | report_edit_endDate_year |  |
        And I press "report_edit_save"
        Then the following fields should have an error:
           | report_edit_startDate_day |
            | report_edit_startDate_month |
            | report_edit_startDate_year |
            | report_edit_endDate_day |
            | report_edit_endDate_month |
            | report_edit_endDate_year |
        # valid values
        When I fill in the following:
            | report_edit_startDate_day | 01 |
            | report_edit_startDate_month | 01 |
            | report_edit_startDate_year | 2016 |
            | report_edit_endDate_day | 31 |
            | report_edit_endDate_month | 12 |
            | report_edit_endDate_year | 2016 |
        And I press "report_edit_save"
        Then the form should be valid
        # check values
        And I click on "edit-report-period-2016-report"
        Then the following fields should have the corresponding values:
            | report_edit_startDate_day | 01 |
            | report_edit_startDate_month | 01 |
            | report_edit_startDate_year | 2016 |
            | report_edit_endDate_day | 31 |
            | report_edit_endDate_month | 12 |
            | report_edit_endDate_year | 2016 |

    @deputy
    Scenario: test tabs for "Property and Affairs" report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I save the page as "report-property-affairs-homepage"
        Then I should see a "#edit-contacts" element
        And I should see a "#edit-decisions" element
        And I should see a "#edit-accounts" element
        And I should see a "#edit-assets" element

    @deputy
    Scenario: Check report notification and submission warnings
        # set report due
        Given I set the report 1 end date to 3 days ago
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        # disabled element are not visible from behat
        And I should not see a "report_submit_submitReport" element
        And I should not see a ".report_submission_period" element
