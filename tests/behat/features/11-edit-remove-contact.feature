Feature: edit/remove contact

    @deputy
    Scenario: edit remove contact
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "client-home"
        And I click on "report-n2"
        And I follow "tab-contacts"
        And the URL should match "/report/\d+/contacts"
        And I click on "contact-n1"
        Then the following fields should have the corresponding values:
            | contact_contactName | Andy White |
            | contact_relationship | GP |
            | contact_explanation | I owe him money |
            | contact_address | 45 Noth Road |
            | contact_postcode | N2 5JF |
            | contact_country | GB |
        And I click on "cancel-edit"
        And the URL should match "/report/\d+/contacts"
        And I click on "contact-n1"
        When I fill in the following:
            | contact_contactName |  |
            | contact_relationship |  |
            | contact_explanation |  |
            | contact_address |  |
            | contact_postcode |  |
        And I press "contact_save"
        Then the following fields should have an error:
            | contact_contactName |
            | contact_relationship |
            | contact_explanation |
            | contact_address |  |
            | contact_postcode |
        When I add the following contact:
            | contactName | Andy Brown |
            | relationship | brother  |
            | explanation | no explanation |
            | address | 46 Noth Road |  |  | N2 5JF | GB |
        And the URL should match "/report/\d+/contacts"
        Then I should see "Andy Brown" in the "list-contacts" region
        And I should see "46 Noth Road" in the "list-contacts" region
        And I click on "contact-n1"
        And I click on "delete-confirm"
        And the URL should match "/report/\d+/contacts/delete-confirm/\d+#delete-confirm"
        And I click on "delete-confirm-cancel"
        And the URL should match "/report/\d+/contacts/edit/\d+#edit-\d+"
        And I click on "delete-confirm"
        And I click on "delete"
        And the URL should match "/report/\d+/contacts"


