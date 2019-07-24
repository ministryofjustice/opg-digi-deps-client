Feature: Report 103 start

  @deputy @deputy-103
  Scenario: Check 103 report not initially submittable
    # assert not submittable yet
    Given I am logged in as "behat-lay-deputy-103@publicguardian.gov.uk" with password "Abcd1234"
    When I set the report start date to "1/1/2016"
    And I set the report end date to "31/12/2016"
    And I click on "report-start"
    # assert all tabs available
    Then I should see the "edit-decisions" link
    Then I should see the "edit-contacts" link
    Then I should see the "edit-visits_care" link
    Then I should see the "edit-deputy_expenses" link
    Then I should see the "edit-gifts" link
    Then I should see the "edit-bank_accounts" link
    Then I should not see the "edit-money_transfers" link
    Then I should see the "edit-money_in_short" link
    Then I should see the "edit-money_out_short" link
    Then I should see the "edit-assets" link
    Then I should see the "edit-debts" link
    Then I should see the "edit-actions" link
    Then I should see the "edit-other_info" link
    Then I should see the "edit-documents" link
    # check not submittable (as 103 money section it not completed yet)
    Then the lay report should not be submittable



