@mod @mod_enhancedchoice
Feature: Add enhancedchoice activity
  In order to ask questions as a enhancedchoice of multiple responses
  As a teacher
  I need to add enhancedchoice activities to courses

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
      | option[0] | Option 1 |
      | option[1] | Option 2 |
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I choose "Option 1" from "EnhancedChoice name" enhancedchoice activity
    Then I should see "Your selection: Option 1"
    And I should see "Your enhancedchoice has been saved"
