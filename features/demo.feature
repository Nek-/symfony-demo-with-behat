# This file contains a user story for demonstration only.
# Learn how to get started with Behat and BDD on Behat's website:
# http://behat.org/en/latest/quick_start.html

Feature:
    In order to prove that the Behat Symfony extension is correctly installed
    As a user
    I want to have a demo scenario

    Scenario: It receives a response from Symfony's kernel
        When a demo scenario sends a request to "/"
        Then the response should be received
        Then I should see the text "Welcome to the Symfony Demo application"

    Scenario: I navigate to Symfony demo
        Given I navigate to "/"
        When I click on "Browse application"
        Then I should see the text "Symfony Demo"

    @database
    Scenario: I comment a post
        Given I am logged in as a user
        And I navigate on a post
        When I fill a comment with "Some random words"
        Then the post should have a new comment
        And I should see the text "Some random words"
