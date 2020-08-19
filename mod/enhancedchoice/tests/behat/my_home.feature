@mod @mod_enhancedchoice
Feature: Test the display of the enhancedchoice module on my home
  In order to know my status in a enhancedchoice activity
  As a user
  I need to see it in My dashboard.

  Background:
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
    And I add a "EnhancedChoice" to section "1"
    And I expand all fieldsets
    And I set the following fields to these values:
      | EnhancedChoice name | Test enhancedchoice name |
      | Description | Test enhancedchoice description |
      | timeopen[enabled] | 1 |
      | timeclose[enabled] | 1 |
      | timeclose[day] | 1 |
      | timeclose[month] | January |
      | timeclose[year] | 2030 |
      | timeclose[hour] | 08 |
      | timeclose[minute] | 00 |
      | Allow enhancedchoice to be updated | No |
      | option[0] | Option 1 |
      | option[1] | Option 2 |
    And I press "Save and return to course"
    And I log out

  Scenario: View my home as a student after answering the enhancedchoice
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I choose "Option 1" from "Test enhancedchoice name" enhancedchoice activity
    And I should see "Your selection: Option 1"
    And I should see "Your enhancedchoice has been saved"
    And "Save my enhancedchoice" "button" should not exist
    And I log out

  Scenario: View my home as a teacher
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I choose "Option 1" from "Test enhancedchoice name" enhancedchoice activity
    And I should see "Your selection: Option 1"
    And I should see "Your enhancedchoice has been saved"
    And "Save my enhancedchoice" "button" should not exist
    And I log out
