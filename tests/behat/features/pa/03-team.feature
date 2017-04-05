Feature: PA team

  Scenario: team page
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings"
    # settings page
    And I click on "user-accounts"
    Then I should see the "team-user-behat-pa1publicguardiangsigovuk" region

  Scenario: PA (named) logs in and adds PA_ADMIN user
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, user-accounts"
    # add user - test form
    When I click on "add"
    And I press "team_member_account_save"
    Then the following fields should have an error:
      | team_member_account_firstname  |
      | team_member_account_lastname   |
      | team_member_account_email      |
      | team_member_account_roleName_0 |
      | team_member_account_roleName_1 |
    # add user ADMIN
    When I fill in the following:
      | team_member_account_firstname  | Markk Admin                               |
      | team_member_account_lastname   | Yelloww                                   |
      | team_member_account_email      | behat-pa1-admin@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_0 | ROLE_PA_ADMIN                             |
    And I press "team_member_account_save"
    Then the form should be valid
    Then I should see the "team-user-behat-pa1-adminpublicguardiangsigovuk" region

  Scenario: activate PA_ADMIN user
    Given emails are sent from "deputy" area
    And I go to "/logout"
    And I open the "/user/activate/" link from the email
    Then the response status code should be 200
    # password step
    When I fill in the following:
      | set_password_password_first  | Abcd1234 |
      | set_password_password_second | Abcd1234 |
    When I click on "save"
    Then the form should be valid
    # assert pre-fill
    Then the following fields should have the corresponding values:
      | user_details_firstname | Markk Admin |
      | user_details_lastname  | Yelloww     |
    # add change details
    When I fill in the following:
      | user_details_firstname | Mark Admin          |
      | user_details_lastname  | Yellow              |
      | user_details_jobTitle  | Solicitor assistant |
      | user_details_phoneMain | 10000000002         |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard and I see the same clients
    And I should see the "client-1000010" region
    # check I see all the users
    When I click on "pa-settings, user-accounts"
    Then I should see the "team-user-behat-pa1publicguardiangsigovuk" region
    And I should see the "team-user-behat-pa1-adminpublicguardiangsigovuk" region

  Scenario: PA_ADMIN logs in and adds PA_TEAM_MEMBER
    Given I am logged in as "behat-pa1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, user-accounts, add"
    # add user ADMIN
    When I fill in the following:
      | team_member_account_firstname  | Robertt Team member                             |
      | team_member_account_lastname   | Blackk                                          |
      | team_member_account_email      | behat-pa1-team-member@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_1 | ROLE_PA_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the form should be valid
    # check all 3 users are displayed
    Then I should see the "team-user-behat-pa1publicguardiangsigovuk" region
    Then I should see the "team-user-behat-pa1-adminpublicguardiangsigovuk" region
    Then I should see the "team-user-behat-pa1-team-memberpublicguardiangsigovuk" region

  Scenario: activate ROLE_PA_TEAM_MEMBER user
    Given emails are sent from "deputy" area
    And I go to "/logout"
    And I open the "/user/activate/" link from the email
    Then the response status code should be 200
    # password step
    When I fill in the following:
      | set_password_password_first  | Abcd1234 |
      | set_password_password_second | Abcd1234 |
    When I click on "save"
    Then the form should be valid
    # assert pre-fill
    Then the following fields should have the corresponding values:
      | user_details_firstname | Robertt Team member |
      | user_details_lastname  | Blackk              |
    # add change details
    When I fill in the following:
      | user_details_firstname | Robert Team member |
      | user_details_lastname  | Black              |
      | user_details_jobTitle  | Solicitor helper   |
      | user_details_phoneMain | 10000000003        |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard and I see the same clients
    And I should see the "client-1000010" region
    # check I see all the users
    When I click on "pa-settings, user-accounts"
    Then I should see the "team-user-behat-pa1publicguardiangsigovuk" region
    Then I should see the "team-user-behat-pa1-adminpublicguardiangsigovuk" region
    Then I should see the "team-user-behat-pa1-team-memberpublicguardiangsigovuk" region
    And I should not see the "add" link

  Scenario: PA (named) logs in and edit users
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, user-accounts"
    # edit PA named
    When I click on "edit" in the "team-user-behat-pa1publicguardiangsigovuk" region
    Then the following fields should have the corresponding values:
      | team_member_account_firstname | John Named                          |
      | team_member_account_lastname  | Green                               |
      | team_member_account_email     | behat-pa1@publicguardian.gsi.gov.uk |
      | team_member_account_jobTitle  | Solicitor                           |
      | team_member_account_phoneMain | 10000000001                         |
    And I should not see a "team_member_account_roleName_0" element
    And I should not see a "team_member_account_roleName_1" element
    When I fill in the following:
      | team_member_account_firstname | Johnny Named                        |
      | team_member_account_lastname  | Greens                              |
      | team_member_account_email     | behat-pa1@publicguardian.gsi.gov.uk |
      | team_member_account_jobTitle  | Senior Solicitor                    |
      | team_member_account_phoneMain | +4410000000001                      |
    And I press "team_member_account_save"
    Then the form should be valid
    And I should see "Johnny Named" in the "team-user-behat-pa1publicguardiangsigovuk" region
    And I should see "Greens" in the "team-user-behat-pa1publicguardiangsigovuk" region
    And I should see "Senior Solicitor" in the "team-user-behat-pa1publicguardiangsigovuk" region
    And I should see "+4410000000001" in the "team-user-behat-pa1publicguardiangsigovuk" region
    # PA named edits Admin, downgrade role into team member
    Given I save the application status into "pa-team-before-downgrading-admin"
    And I should see "Administrator" in the "team-user-behat-pa1-adminpublicguardiangsigovuk" region
    When I click on "edit" in the "team-user-behat-pa1-adminpublicguardiangsigovuk" region
    Then the "team_member_account_roleName_0" field should contain "ROLE_PA_ADMIN"
    When I fill in "team_member_account_roleName_1" with "ROLE_PA_TEAM_MEMBER"
    And I press "team_member_account_save"
    Then I should not see "Administrator" in the "team-user-behat-pa1-adminpublicguardiangsigovuk" region
    # restore Admin role
    And I load the application status from "pa-team-before-downgrading-admin"
    # check PA named can edit team members
    When I click on "edit" in the "team-user-behat-pa1-team-memberpublicguardiangsigovuk" region
    Then the "team_member_account_roleName_1" field should contain "ROLE_PA_TEAM_MEMBER"
    Then the response status code should be 200

  Scenario: PA admin logs in and edit users
    Given I am logged in as "behat-pa1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, user-accounts"
    Then I should see "Edit" in the "team-user-behat-pa1-adminpublicguardiangsigovuk" region
    Then I should see "Edit" in the "team-user-behat-pa1-team-memberpublicguardiangsigovuk" region
    But I should not see "Edit" in the "team-user-behat-pa1publicguardiangsigovuk" region
