Feature: PROF dashboard

  Scenario: PROF dashboard check visibility, pagination and search
    Given I load the application status from "prof-users-uploaded"
    And I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    # check pagination
    And I should see the "client" region exactly 15 times
    When I click on "paginator-page-2"
    Then I should see the "client" region exactly 3 times
    # check search
    When I fill in "search" with "01000010"
    And I press "search_submit"
    Then I should see the "client-01000010" region
    And I should see the "client" region exactly 1 times
    # check tabs
    When I click on "tab-ready"
    Then I should not see the "client" region
    # check navigation links
    When I click on "pa-dashboard" in the navbar region
    Then I should be on "/pa/"

  Scenario: PROF links in header
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    #PROF links
    Then I should see the "pa-dashboard" link
    And I should see the "org-settings" link
    And I should see the "logout" link
    #Lay deputy links
    And I should not see the "user-account" link
    And I should not see the "reports" link
