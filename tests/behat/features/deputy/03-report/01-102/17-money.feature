Feature: Report money 102

  @deputy
  # save status in order to be reused for 103 later
  Scenario: save status before starting money 102
    Given I save the application status into "money-transactions-before"

  @deputy @shaun
  Scenario: money in 102
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "report-start, edit-money_in, start"
    # add transaction n.1 and check validation
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | account_group_0 | pensions |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | account_category_0 | state-pension |
    And the step with the following values CANNOT be submitted:
      | account_description |  |       |
      | account_amount      |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | account_description |  | 0   |
      | account_amount      |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | account_description |  | 0   |
      | account_amount      | 10000000.01 | [ERR] |
    And the "#error-summary" element should contain "10,000,000"
    And the step with the following values CAN be submitted:
      | account_description | pension received |
      | account_amount      | 12345.67         |
    # add another: yes
    And I choose "yes" when asked for adding another record
    # add transaction n.2
    And the step with the following values CAN be submitted:
      | account_group_0 | pensions |
    And the step with the following values CAN be submitted:
      | account_category_0 | state-pension |
    And the step with the following values CAN be submitted:
      | account_description | delete me |
      | account_amount      | 1         |
    # add another: yes
    And I choose "yes" when asked for adding another record
    # add transaction n.3
    And the step with the following values CAN be submitted:
      | account_group_0 | moneyin-other |
    And the step with the following values CAN be submitted:
      | account_category_0 | anything-else |
    And the step with the following values CAN be submitted:
      | account_description | money found on the road |
      | account_amount      | 50                      |
    # add another: yes
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | account_group_0 | salary-or-wages |
    # no sub categories
    And the step with the following values CAN be submitted:
      | account_description | money from part time job |
      | account_amount      | 500                      |
    # add another: no
    And I choose "no" when asked for adding another record
    # check record in summary page
    And each text should be present in the corresponding region:
      | State Pension           | transaction-pension-received        |
      | pension received        | transaction-pension-received        |
      | £12,345.67              | transaction-pension-received        |
      | State Pension           | transaction-delete-me               |
      | delete me               | transaction-delete-me               |
      | £1                      | transaction-delete-me               |
      | Anything else           | transaction-money-found-on-the-road |
      | money found on the road | transaction-money-found-on-the-road |
      | £50.00                  | transaction-money-found-on-the-road |
      | money from part time job | transaction-money-from-part-time-job |
      | £500.00                  | transaction-money-from-part-time-job |
      | £12,346.67              | pensions-total                      |
      | £50.00                  | moneyin-other-total                 |
      | £500.00                 | salary-or-wages-total               |
    # remove transaction n.2
    When I click on "delete" in the "transaction-delete-me" region
    Then I should not see the "transaction-delete-me" region
    # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
    # edit transaction n.3
    When I click on "edit" in the "transaction-money-found-on-the-road" region
    Then the following fields should have the corresponding values:
      | account_description | money found on the road |
      | account_amount      | 50.00                      |
    And the step with the following values CAN be submitted:
      | account_description | Some money found on the road |
      | account_amount      | 51                      |
    And each text should be present in the corresponding region:
      | Anything else           | transaction-some-money-found-on-the-road |
      | Some money found on the road | transaction-some-money-found-on-the-road |
      | £51.00 | transaction-some-money-found-on-the-road |

  @deputy @shaun
  Scenario: money out
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "report-start"
    #And I should not see "#finances-section .behat-alert-message"
    And I click on "edit-money_out, start"
      # add transaction n.1 and check validation
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | account_group_0 | household-bills |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | account_category_0 | broadband |
    And the step with the following values CANNOT be submitted:
      | account_description |  |       |
      | account_amount      |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | account_description |  | 0     |
      | account_amount      |  | [ERR] |
    And the step with the following values CAN be submitted:
      | account_description | january bill |
      | account_amount      | 12845.68     |
      # add another: yes
    And I choose "yes" when asked for adding another record
      # add transaction n.2
    And the step with the following values CAN be submitted:
      | account_group_0 | household-bills |
    And the step with the following values CAN be submitted:
      | account_category_0 | broadband |
    And the step with the following values CAN be submitted:
      | account_description | delete me |
      | account_amount      | 1         |
      # add another: yes
    And I choose "yes" when asked for adding another record
      # add transaction n.3
    And the step with the following values CAN be submitted:
      | account_group_0 | moneyout-other |
    And the step with the following values CAN be submitted:
      | account_category_0 | anything-else-paid-out |
    And the step with the following values CAN be submitted:
      | account_description | money found on the road |
      | account_amount      | 50                      |
      # add another: no
    And I choose "no" when asked for adding another record
      # check record in summary page
    And each text should be present in the corresponding region:
      | Broadband               | transaction-january-bill            |
      | january bill            | transaction-january-bill            |
      | £12,845.68              | transaction-january-bill            |
      | Broadband               | transaction-delete-me               |
      | delete me               | transaction-delete-me               |
      | £1                      | transaction-delete-me               |
      | Anything else           | transaction-money-found-on-the-road |
      | money found on the road | transaction-money-found-on-the-road |
      | £50.00                  | transaction-money-found-on-the-road |
      | £12,846.68              | household-bills-total               |
      # remove transaction n.2
    When I click on "delete" in the "transaction-delete-me" region
    Then I should not see the "transaction-delete-me" region
      # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
      # edit transaction n.3
    When I click on "edit" in the "transaction-money-found-on-the-road" region
    Then the following fields should have the corresponding values:
      | account_description | money found on the road |
      | account_amount      | 50.00                   |
    And the step with the following values CAN be submitted:
      | account_description | Some money found on the road |
      | account_amount      | 51                           |
    And each text should be present in the corresponding region:
      | Anything else                | transaction-some-money-found-on-the-road |
      | Some money found on the road | transaction-some-money-found-on-the-road |
      | £51.00                       | transaction-some-money-found-on-the-road |

  @deputy
  Scenario: Assert balance status is "not matching"
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "report-start"
    And I should see the "balance-state-not-matching" region







