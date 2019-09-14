Feature: Authentication

  @purgeDatabase
  Scenario: Test obtaining JWT token procedure is valid
    Given there is an user with username "moofik" and password "OurEcommerceIsTheBeast"
    And I set request header "Content-Type" to "application/json"
    When I send a POST request to "/api/login_check" with body:
    """
      {
          "username": "moofik",
          "password": "OurEcommerceIsTheBeast"
      }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node token should exist

  Scenario: Test authentication does not work with wrong password
    Given I set request header "Content-Type" to "application/json"
    When I send a POST request to "/api/login_check" with body:
    """
      {
          "username": "moofik",
          "password": "invalid password"
      }
    """
    Then the response status code should be 401
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON should be equal to:
    """
      {
          "detail": "Bad credentials.",
          "status": 401,
          "type": "about:blank",
          "title": "Unauthorized"
      }
    """

