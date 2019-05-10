Feature: Enabling and disabling NDR for Lay deputies

  Scenario: Enabling NDR for the first time when Client already has a Report retrieves a new NDR report when the Client logs in and disables the Report
    Given emails are sent from "admin" area
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And there is an activated "Lay Deputy" user with NDR "disabled" and email "red-squirrel@publicguardian.gov.uk" and password "Abcd1234"
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    When I enable NDR for user "red-squirrelpublicguardiangovuk"
    And I am logged in as "red-squirrel@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see the "ndr-start" link

  Scenario: Disabling NDR when Client already has a Report removes NDR from Client dashboard and enables completion of Report
  Scenario: Re-enabling NDR for a Client retrieves the existing NDR when the Client logs in and disables the Report
