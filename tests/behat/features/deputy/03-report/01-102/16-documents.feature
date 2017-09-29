Feature: Report documents

  @deputy
  Scenario: No documents to attach
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "report-start, edit-documents, start"
    # chose "no documents"
    Then the URL should match "report/\d+/documents/step/1"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_1 | no |
    # check no documents in summary page
    Then the URL should match "report/\d+/documents/summary"
    And I should not see the region "document-list"
    And I should see "Edit" in the "provided-documentation" region

  @deputy
  Scenario: Upload PDF documents
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "report-start, edit-documents"
    Then the URL should match "report/\d+/documents/summary"
    And I should see "Edit" in the "provided-documentation" region
    When I click on "edit"
    # chose "yes documents"
    Then the URL should match "report/\d+/documents/step/1"
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_0 | yes |
    # check empty file error
    When I attach the file "empty-file.pdf" to "report_document_upload_file"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_file   |

    # check not a pdf file error
    When I attach the file "not-a-pdf.pdf" to "report_document_upload_file"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_file   |

    # check vba-eicar file error
    When I attach the file "pdf-doc-vba-eicar-dropper.pdf" to "report_document_upload_file"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_file   |

    # check good pdf gets uploaded
    When I attach the file "good.pdf" to "report_document_upload_file"
    And I click on "attach-file"
    Then the form should be valid
    And each text should be present in the corresponding region:
      | good.pdf        | document-list |

    # check duplicate file error
    When I attach the file "good.pdf" to "report_document_upload_file"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_file   |
    And I save the application status into "documents-before-delete"

  @deputy
  Scenario: Delete document
    Given I load the application status from "documents-before-delete"
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "report-start, edit-documents"
    # chose "yes documents"
    Then the URL should match "report/\d+/documents/summary"
    And I save the current URL as "summary-page"
    When I click on "delete-documents-button" in the "document-list" region
    Then the URL should match "/documents/\d+/delete"
    # test cancel button on confirmation page
    When I click on "confirm-cancel"
    Then I go to the URL previously saved as "summary-page"
    And I click on "delete-documents-button" in the "document-list" region
    Then the response status code should be 200
    # delete this time
    And I click on "document-delete"
    Then the URL should match "/report/\d+/documents/step/1"
    # chose "no documents" to make report submittable
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_0 | no |


  @deputy
  Scenario: Upload image documents
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-start, edit-documents"
    Then the URL should match "report/\d+/documents/summary"
    And I should see "Edit" in the "provided-documentation" region
    When I click on "edit"
    # chose "yes documents"
    Then the URL should match "report/\d+/documents/step/1"
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_0 | yes |

    # check good png gets uploaded
    When I attach the file "good.png" to "report_document_upload_file"
    And I click on "attach-file"
    Then the form should be valid
    And each text should be present in the corresponding region:
      | good.png        | document-list |

    # check good jpg gets uploaded
    When I attach the file "good.jpg" to "report_document_upload_file"
    And I click on "attach-file"
    Then the form should be valid
    And each text should be present in the corresponding region:
      | good.jpg        | document-list |

    # check duplicate file error
    When I attach the file "good.png" to "report_document_upload_file"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_file   |

  @deputy
    Scenario: Upload file1.pdf
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
      And I click on "report-start, edit-documents"
      Then the URL should match "report/\d+/documents/summary"
      When I click on "edit" in the "provided-documentation" region
    # chose "yes documents"
      Then the URL should match "report/\d+/documents/step/1"
      And the step with the following values CAN be submitted:
        | document_wishToProvideDocumentation_0 | yes |
    # check empty file error
      When I attach the file "file1.pdf" to "report_document_upload_file"
      And I click on "attach-file"
      Then the form should be valid
      #And I attach the file "file2.pdf" to "report_document_upload_file"
      #And I click on "attach-file"
      #Then the form should be valid
      Then each text should be present in the corresponding region:
        | file1.pdf        | document-list |
      #  | file2.pdf        | document-list |
