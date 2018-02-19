Feature: Report 104-5 start

  @deputy @104-5
  Scenario: load app status to common sections completed, change type to 104 and check not submittable
    Given I load the application status from "102-5-common-sections-complete"
    And I change the report of the client with case number "01000010" to "104-5"

  @deputy @104-5
  Scenario: test tabs for 104-5
    # assert not submittable yet
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    # click on 104-5 report
    And I click on "pa-report-open" in the "client-01000010" region
    #test tabs
    And I should see a "#edit-decisions" element
    And I should see the "edit-contacts" link
    And I should see the "edit-visits_care" link
    And I should see the "edit-lifestyle" link
    And I should see the "edit-actions" link
    And I should see the "edit-other_info" link
    And I should see the "edit-documents" link
    And I should not see the "edit-bank_accounts" link
    And I should not see the "edit-money_transfers" link
    And I should not see the "edit-money_in" link
    And I should not see the "edit-money_out" link
    And I should not see the "edit-money_out_short" link
    And I should not see the "edit-money_in_short" link
    And I should not see the "edit-assets" link
    And I should not see the "edit-debts" link
    And I should not see the "edit-gifts" link
    And I should not see a "#edit-balance" element
    And I should not see the "edit-deputy-expenses" link
    #And I should not see a "#edit-pa_fee_expense" element
    # check not submittable (as 104 lifestyle it not completed yet)
    Then the PROF report should not be submittable



