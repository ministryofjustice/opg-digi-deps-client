Feature: Report 103 start

  @deputy
  Scenario: load app status before 102 money got completed, change type to 103 and check not submittable
    # Since 103 shares same section as 102, import status from 102 before money section (that is the only different section) were added
    # that checkpoint correspond to a 103 report without money added
    Given I load the application status from "money-transactions-before"
    And I change the report 1 type to "103"
    Then the report should not be submittable



