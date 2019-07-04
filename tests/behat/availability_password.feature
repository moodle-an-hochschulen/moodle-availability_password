@availability @availability_password @javascript
Feature: When a teacher configures a password restriction a student cannot access the activity until they have entered the correct password

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "admin"
    And I set the following administration settings values:
      | enableavailability | 1 |
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add a "page" to section "1"
    And I set the following fields to these values:
      | Name         | Restricted page          |
      | Description  | Need to enter a password |
      | Page content | Some page content        |
    And I expand all fieldsets
    And I click on "Add restriction" "button"
    And I click on "Password" "button" in the "Add restriction..." "dialogue"
    And I set the field "Password" to "Testing123"
    And I press "Save and return to course"
    And I log out

  Scenario: A student attempts to enter a password to access the page activity
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "Restricted page"
    And I should see "Not available unless: You enter the correct password"

    When I click on "Restricted page" "text"
    Then I should not see "Some page content"
    And I click on "Cancel" "button" in the "Password protection" "dialogue"
    And I should see "Not available unless: You enter the correct password"

    When I click on "You enter the correct password" "text"
    And I set the field "availability_password_input" to "Guess 1"
    And I press "Submit"
    Then I should see "Password incorrect"
    And I click on "Cancel" "button" in the "Password protection" "dialogue"
    And I click on "Restricted page" "text"
    And I should not see "Some page content"
    And I click on "Cancel" "button" in the "Password protection" "dialogue"
    And I should see "Not available unless: You enter the correct password"

    When I click on "You enter the correct password" "text"
    And I set the field "availability_password_input" to "Testing123"
    And I press "Submit"
    And I wait to be redirected
    Then I should see "Some page content"

  Scenario: A student attempts to enter a password to access the page activity with setting availability_password | remember set to "Until the user logs out"
    Given the following config values are set as admin:
      | config   | value     | plugin                |
      | remember | session   | availability_password |

    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "Restricted page"
    And I should see "Not available unless: You enter the correct password"

    When I click on "You enter the correct password" "text"
    And I set the field "availability_password_input" to "Testing123"
    And I press "Submit"
    And I wait to be redirected
    Then I should see "Some page content"
    And I log out

    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "Restricted page"
    And I should see "Not available unless: You enter the correct password"
    And I click on "Restricted page" "text"
    Then I should see "You enter the correct password"
