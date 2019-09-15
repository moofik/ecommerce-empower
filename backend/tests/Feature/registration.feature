Feature: Registration

  @purgeDatabase
  Scenario: Test I can register as a new user and obtain a valid API token
    Given I set request header "Content-Type" to "application/json"
    When I send a POST request to "/api/register" with body:
    """
      {
          "username": "moofik",
          "password": "OurEcommerceIsTheBeast",
          "email": "moofik12@bautomate.com"
      }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON node username should exist

  Scenario: After that I can obtain API access token with this credentials
    Given I set request header "Content-Type" to "application/json"
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