Feature: Pizzeria receives and completes pizza order

  Scenario: a pizzeria can receive a pizza order
    Given I have a pizzeria
    When I order a pizza at the pizzeria
    Then the pizza should be enlisted on the pizzeria

  Scenario: a pizzeria can complete a pizza order
    Given I have a pizzeria
    And I have ordered a pizza at the pizzeria
    When the pizza is completed
    Then the pizza should not be enlisted on the pizzeria

  Scenario: a pizzeria can not complete a pizza order that was not enlisted
    Given I have a pizzeria
    Then the pizzeria should not be able to complete a pizza