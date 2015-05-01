Feature: edit user details

    @deputy
    Scenario: edit user details
         Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
         Then I should be on "client/show"
         And I click on "my-details"
         And I click on "edit-user-details"
         Then I should be on "user/edit-your-details#edit-your-details"
         Then the following fields should have the corresponding values:
             | user_details_firstname | John |
             | user_details_lastname | Doe |
             | user_details_address1 | 102 Petty France |
             | user_details_address2 | MOJ |
             | user_details_address3 | London |
             | user_details_addressPostcode | SW1H 9AJ |
             | user_details_addressCountry | GB |
             | user_details_phoneHome | 020 3334 3555  |
             | user_details_phoneWork | 020 1234 5678  |
         When I fill in the following:
            | user_details_firstname |  |
            | user_details_lastname |  |
            | user_details_address1 | |
            | user_details_addressPostcode | |
            | user_details_addressCountry | |
            | user_details_phoneHome |   |
        And I press "user_details_save"
        Then the following fields should have an error:
            | user_details_firstname |
            | user_details_lastname |
            | user_details_address1 |
            | user_details_addressPostcode |
            | user_details_addressCountry |
            | user_details_phoneHome |
        And I press "user_details_save"
        Then the form should contain an error
        When I fill in the following:
           | user_details_firstname | Paul |
           | user_details_lastname | Jamie |
           | user_details_address1 | 103 Petty France |
           | user_details_address2 | MOJDS |
           | user_details_address3 | London |
           | user_details_addressPostcode | SW1H 9AA |
           | user_details_addressCountry | GB |
           | user_details_phoneHome | 020 3334 3556  |
           | user_details_phoneWork | 020 1234 5679  |
       And I press "user_details_save"
       Then the form should not contain an error
       Then I should be on "user"
       Then I should see "Paul Jamie" in the "my-details" region
       And I should see "103 Petty France" in the "my-details" region
       And I should see "020 3334 3556" in the "my-details" region
       And I should see "020 1234 5679" in the "my-details" region
       And I should see "behat-user@publicguardian.gsi.gov.uk" in the "my-details" region
            