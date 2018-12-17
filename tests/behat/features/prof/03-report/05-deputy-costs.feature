Feature: PROF deputy costs

  Scenario: add cost fixed, no previous, no interim
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    And I click on "edit-prof_deputy_costs, start"
    Then the step with the following values CAN be submitted:
      | deputy_costs_profDeputyCostsHowChargedFixed | 1 |
    And the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasPrevious_1 | no |
    And the step with the following values CAN be submitted:
      | deputy_costs_received_profDeputyFixedCost | 1000 |
    And the step with the following values CAN be submitted:
      | deputy_costs_scco_profDeputyCostsAmountToScco | 100 |
      | deputy_costs_scco_profDeputyCostsReasonBeyondEstimate | scco reason |
    And the step with the following values CAN be submitted:
      | deputy_other_costs_profDeputyOtherCosts_0_amount | 1 |
      | deputy_other_costs_profDeputyOtherCosts_6_amount | 1 |
      | deputy_other_costs_profDeputyOtherCosts_6_moreDetails | breakdown other details |
    And each text should be present in the corresponding region:
      | how-changed | Fixed |
      | has-previous | No |
      | fixed-cost-amount | 1,000.00 |
      | total-cost | 1,000 |
      | scco-assessment-amount | £100.00 |
      | scco-assessment-reason | scco reason |
      | breakdown-other | £100.00 |
      | breakdown-other-details | breakdown other details |
