Feature: PROF team setup

  Scenario: team page
    Given I load the application status from "prof-users-uploaded"
    And I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings"
    # settings page
    And I click on "user-accounts"
    Then I should see the "team-user-behat-prof1publicguardiangsigovuk" region

  # VOTERS and controllers need to be updated with PROF role to have this working
  Scenario: named PROF logs in and adds PROF_ADMIN user
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    # add user - test form
    When I click on "add"
    And I press "team_member_account_save"
    Then the following fields should have an error:
      | team_member_account_firstname  |
      | team_member_account_lastname   |
      | team_member_account_email      |
      | team_member_account_roleName_0 |
      | team_member_account_roleName_1 |
#    # add user ADMIN invalid domain
#    When I fill in the following:
#      | team_member_account_firstname  | Markk Admin                               |
#      | team_member_account_lastname   | Yelloww                                   |
#      | team_member_account_email      | behat-prof1-admin@someotherdomain.gsi.gov.uk |
#      | team_member_account_roleName_0 | ROLE_PROF_ADMIN                             |
#    And I press "team_member_account_save"
#    Then the following fields should have an error:
#      | team_member_account_email      |
    # add valid user details
    When I fill in the following:
      | team_member_account_firstname  | Markk Admin                               |
      | team_member_account_lastname   | Yelloww                                   |
      | team_member_account_email      | behat-prof1-admin@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_0 | ROLE_PROF_ADMIN                             |
    And I press "team_member_account_save"
    Then the form should be valid
    Then I should see the "team-user-behat-prof1-adminpublicguardiangsigovuk" region

  Scenario: activate PROF_ADMIN user
    Given emails are sent from "deputy" area
    And I activate the user with password "Abcd1234"
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
    And I should see the "client-01000010" region
    # check I see all the users
    When I click on "org-settings, user-accounts"
    Then I should see the "team-user-behat-prof1publicguardiangsigovuk" region
    And I should see the "team-user-behat-prof1-adminpublicguardiangsigovuk" region

  Scenario: PROF_ADMIN logs in and adds PROF_TEAM_MEMBER with invalid email
    Given I am logged in as "behat-prof1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts, add"
    # add user ADMIN
    When I fill in the following:
      | team_member_account_firstname  | Robertt Team member                             |
      | team_member_account_lastname   | Blackk                                          |
      | team_member_account_email      | behat-prof1-team-member@@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_1 | ROLE_PROF_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the following fields should have an error:
      | team_member_account_email      |

  Scenario: PROF_ADMIN logs in and adds PROF_TEAM_MEMBER
    Given I am logged in as "behat-prof1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts, add"
    # add user ADMIN
    When I fill in the following:
      | team_member_account_firstname  | Robertt Team member                             |
      | team_member_account_lastname   | Blackk                                          |
      | team_member_account_email      | behat-prof1-team-member@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_1 | ROLE_PROF_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the form should be valid
    # check all 3 users are displayed
    Then I should see the "team-user-behat-prof1publicguardiangsigovuk" region
    Then I should see the "team-user-behat-prof1-adminpublicguardiangsigovuk" region
    Then I should see the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region

  Scenario: activate ROLE_PROF_TEAM_MEMBER user
    Given emails are sent from "deputy" area
    And I activate the user with password "Abcd1234"
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
    And I should see the "client-01000010" region
    # check I see all the users
    When I click on "org-settings, user-accounts"
    Then I should see the "team-user-behat-prof1publicguardiangsigovuk" region
    Then I should see the "team-user-behat-prof1-adminpublicguardiangsigovuk" region
    Then I should see the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region
    And I should not see the "add" link

  Scenario: PROF (named) logs in and edit users
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    Then I should not see "edit" in the "team-user-behat-prof1publicguardiangsigovuk" region
    # edit PROF named
    When I click on "edit" in the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region
    Then the following fields should have the corresponding values:
      | team_member_account_firstname | Robert Team member                              |
      | team_member_account_lastname  | Black                                           |
      | team_member_account_email     | behat-prof1-team-member@publicguardian.gsi.gov.uk |
      | team_member_account_jobTitle  | Solicitor helper                                |
      | team_member_account_phoneMain | 10000000003                                     |
    And I should not see a "team_member_account_roleName_0" element
    And I should not see a "team_member_account_roleName_1" element
    When I fill in the following:
      | team_member_account_firstname | Bobby Team member                               |
      | team_member_account_lastname  | BlackAndBlue                                    |
      | team_member_account_email     | behat-prof1-team-member@publicguardian.gsi.gov.uk |
      | team_member_account_jobTitle  | Helper solicitor                                |
      | team_member_account_phoneMain | +4410000000003                                  |
    And I press "team_member_account_save"
    Then the form should be valid
    And I should see "Bobby Team member" in the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region
    And I should see "BlackAndBlue" in the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region
    And I should see "Helper solicitor" in the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region
    And I should see "+4410000000003" in the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region
    # PROF named edits Admin, downgrade role into team member
    Given I save the application status into "prof-team-before-downgrading-admin"
    And I should see "Administrator" in the "team-user-behat-prof1-adminpublicguardiangsigovuk" region
    When I click on "edit" in the "team-user-behat-prof1-adminpublicguardiangsigovuk" region
    Then the "team_member_account_roleName_0" field should contain "ROLE_PROF_ADMIN"
    When I fill in "team_member_account_roleName_1" with "ROLE_PROF_TEAM_MEMBER"
    And I press "team_member_account_save"
    Then I should not see "Administrator" in the "team-user-behat-prof1-adminpublicguardiangsigovuk" region
    # restore Admin role
    And I load the application status from "prof-team-before-downgrading-admin"
    # check PROF named can edit team members
    When I click on "edit" in the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region
    Then the "team_member_account_roleName_1" field should contain "ROLE_PROF_TEAM_MEMBER"
    Then the response status code should be 200

  Scenario: PROF admin logs in and edit users
    Given I am logged in as "behat-prof1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    Then I should not see "Edit" in the "team-user-behat-prof1-adminpublicguardiangsigovuk" region
    And I should see "Edit" in the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region
    But I should not see "Edit" in the "team-user-behat-prof1publicguardiangsigovuk" region

  Scenario: PROF (named) deputy adds, then removes a PROF_ADMIN user
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    Then I should not see "Remove" in the "team-user-behat-prof1publicguardiangsigovuk" region
    When I click on "add"
    Then the response status code should be 200
    And I press "team_member_account_save"
    When I fill in the following:
      | team_member_account_firstname  | Adam Admin                               |
      | team_member_account_lastname   | Cyan                                   |
      | team_member_account_email      | behat-prof1-admin2@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_0 | ROLE_PROF_ADMIN                             |
    And I press "team_member_account_save"
    Then the form should be valid
    Then the response status code should be 200
    Then I should see the "team-user-behat-prof1-admin2publicguardiangsigovuk" region
    Then I should see "Remove" in the "team-user-behat-prof1-admin2publicguardiangsigovuk" region
    But I should not see "Remove" in the "team-user-behat-prof1publicguardiangsigovuk" region
    Then I click on "delete" in the "team-user-behat-prof1-admin2publicguardiangsigovuk" region
    Then the response status code should be 200
    # test cancel button on confirmation page
    When I click on "confirm-cancel"
    Then the response status code should be 200
    Then I click on "delete" in the "team-user-behat-prof1-admin2publicguardiangsigovuk" region
    Then the response status code should be 200
    # now confirm
    When I click on "confirm"
    Then the response status code should be 200
    Then I should not see the "team-user-behat-prof1-admin2publicguardiangsigovuk" region

  Scenario: PROF_ADMIN logs in, adds then removes a PROF_TEAM_MEMBER
    Given I am logged in as "behat-prof1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts, add"
    # add user team member
    When I fill in the following:
      | team_member_account_firstname  | Andy Team member                             |
      | team_member_account_lastname   | Team Member                                          |
      | team_member_account_email      | behat-prof1-team-member2@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_1 | ROLE_PROF_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the form should be valid
    # check all 3 users are displayed
    Then I should see the "team-user-behat-prof1publicguardiangsigovuk" region
    Then I should see the "team-user-behat-prof1-adminpublicguardiangsigovuk" region
    Then I should see the "team-user-behat-prof1-team-member2publicguardiangsigovuk" region
    Then I should see "Remove" in the "team-user-behat-prof1-team-member2publicguardiangsigovuk" region
    But I should not see "Remove" in the "team-user-behat-prof1-adminpublicguardiangsigovuk" region
    Then I click on "delete" in the "team-user-behat-prof1-team-member2publicguardiangsigovuk" region
    Then the response status code should be 200
    When I click on "confirm"
    Then the response status code should be 200
    Then I should not see the "team-user-behat-prof1-team-member2publicguardiangsigovuk" region

  Scenario: named PROF3 logs in, adds and activates PROF_ADMIN user
    Given I am logged in as "behat-prof3@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    And I click on "add"
    And I fill in the following:
      | team_member_account_firstname  | PROF3                                       |
      | team_member_account_lastname   | Admin                                     |
      | team_member_account_email      | behat-prof3-admin@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_0 | ROLE_PROF_ADMIN                             |
    And I press "team_member_account_save"
    Then the form should be valid
    Then I should see the "team-user-behat-prof3-adminpublicguardiangsigovuk" region
    When emails are sent from "deputy" area
    And I activate the user with password "Abcd1234"
    When I fill in the following:
      | user_details_jobTitle  | Solicitor assistant |
      | user_details_phoneMain | 20000000002         |
    And I press "user_details_save"
    Then the form should be valid
    And I should see the "client-03000001" region

  Scenario: PROF_ADMIN3 logs in, adds and activates PROF_TEAM_MEMBER
    Given I am logged in as "behat-prof3-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts, add"
    When I fill in the following:
      | team_member_account_firstname  | PROF3                                             |
      | team_member_account_lastname   | Team Member                                     |
      | team_member_account_email      | behat-prof3-team-member@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_1 | ROLE_PROF_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the form should be valid
    When emails are sent from "deputy" area
    And I activate the user with password "Abcd1234"
    When I fill in the following:
      | user_details_jobTitle  | Solicitor helper   |
      | user_details_phoneMain | 30000000003        |
    And I press "user_details_save"
    Then the form should be valid
    And I save the application status into "prof-team-users-complete"
    And I should see the "client-03000001" region

  Scenario: PROF_ADMIN3 logs in and edits PROF_TEAM_MEMBER using existing email address
    Given I am logged in as "behat-prof3@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    # edit PROF named
    When I click on "edit" in the "team-user-behat-prof3-team-memberpublicguardiangsigovuk" region
    And I fill in the following:
      | team_member_account_firstname | Edited PROF3                                |
      | team_member_account_lastname  | Edited Team Member                        |
      | team_member_account_email     | behat-prof3-admin@publicguardian.gsi.gov.uk |
    And I press "team_member_account_save"
    Then the following fields should have an error:
      | team_member_account_email |

  Scenario: PROF_ADMIN3 logs in and edits PROF_TEAM_MEMBER using existing email address for a deleted user
    Given I am logged in as "behat-prof3@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    # delete existing admin user
    And I click on "delete" in the "team-user-behat-prof3-adminpublicguardiangsigovuk" region
    And I click on "confirm"
    # edit PROF named
    When I click on "edit" in the "team-user-behat-prof3-team-memberpublicguardiangsigovuk" region
    And I fill in the following:
      | team_member_account_firstname | Edited PROF3                                |
      | team_member_account_lastname  | Edited Team Member                        |
      | team_member_account_email     | behat-prof3-admin@publicguardian.gsi.gov.uk |
    And I press "team_member_account_save"
    Then the form should be valid
    And I should see "behat-prof3-admin@publicguardian.gsi.gov.uk" in the "team-user-behat-prof3-adminpublicguardiangsigovuk" region
