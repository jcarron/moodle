@mod @mod_enhancedchoice
Feature: Restrict availability of the enhancedchoice module to a deadline
  In order to limit the time a student can mace a selection
  As a teacher
  I need to restrict answering to within a time period

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

  Scenario: Enable the enhancedchoice activity with a start deadline in the future
    Given I add a "EnhancedChoice" to section "1" and I fill the form with:
      | EnhancedChoice name | EnhancedChoice name |
      | Description | EnhancedChoice Description |
      | option[0] | Option 1 |
      | option[1] | Option 2 |
      | timeopen[enabled] | 1 |
      | timeopen[day] | 30 |
      | timeopen[month] | December |
      | timeopen[year] | 2037 |
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "EnhancedChoice name"
    Then I should see "This activity is not available until"

  Scenario: Enable the enhancedchoice activity with a start deadline in the past
    Given I add a "EnhancedChoice" to section "1" and I fill the form with:
      | EnhancedChoice name | EnhancedChoice name |
      | Description | EnhancedChoice Description |
      | option[0] | Option 1 |
      | option[1] | Option 2 |
      | timeopen[enabled] | 1 |
      | timeopen[day] | 30 |
      | timeopen[month] | December |
      | timeopen[year] | 2007 |
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "EnhancedChoice name"
    And "enhancedchoice_1" "radio" should exist
    And "enhancedchoice_2" "radio" should exist
    And "Save my enhancedchoice" "button" should exist

  Scenario: Enable the enhancedchoice activity with a end deadline in the future
    Given I add a "EnhancedChoice" to section "1" and I fill the form with:
      | EnhancedChoice name | EnhancedChoice name |
      | Description | EnhancedChoice Description |
      | option[0] | Option 1 |
      | option[1] | Option 2 |
      | timeclose[enabled] | 1 |
      | timeclose[day] | 30 |
      | timeclose[month] | December |
      | timeclose[year] | 2037 |
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "EnhancedChoice name"
    And "enhancedchoice_1" "radio" should exist
    And "enhancedchoice_2" "radio" should exist
    And "Save my enhancedchoice" "button" should exist

  Scenario: Enable the enhancedchoice activity with a end deadline in the past
    Given I add a "EnhancedChoice" to section "1" and I fill the form with:
      | EnhancedChoice name | EnhancedChoice name |
      | Description | EnhancedChoice Description |
      | option[0] | Option 1 |
      | option[1] | Option 2 |
      | timeclose[enabled] | 1 |
      | timeclose[day] | 30 |
      | timeclose[month] | December |
      | timeclose[year] | 2007 |
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "EnhancedChoice name"
    Then I should see "This activity closed on"
