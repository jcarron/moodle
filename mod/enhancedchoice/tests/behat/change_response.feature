@mod @mod_enhancedchoice
Feature: Teacher can choose whether to allow students to change their enhancedchoice response
  In order to allow students to change their enhancedchoice
  As a teacher
  I need to enable the option to change the enhancedchoice

  Scenario: Add a enhancedchoice activity and complete the activity as a student
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "EnhancedChoice" to section "1" and I fill the form with:
      | EnhancedChoice name | EnhancedChoice name |
      | Description | EnhancedChoice Description |
      | Allow enhancedchoice to be updated | No |
      | option[0] | Option 1 |
      | option[1] | Option 2 |
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I choose "Option 1" from "EnhancedChoice name" enhancedchoice activity
    Then I should see "Your selection: Option 1"
    And I should see "Your enhancedchoice has been saved"
    And "Save my enhancedchoice" "button" should not exist
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "EnhancedChoice name"
    And I follow "Edit settings"
    And I set the following fields to these values:
      | Allow enhancedchoice to be updated | Yes |
    And I press "Save and display"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "EnhancedChoice name"
    And I should see "Your selection: Option 1"
    And "Save my enhancedchoice" "button" should exist
    And "Remove my enhancedchoice" "link" should exist
    And I set the field "Option 2" to "1"
    And I press "Save my enhancedchoice"
    And I should see "Your enhancedchoice has been saved"
    And I should see "Your selection: Option 2"
