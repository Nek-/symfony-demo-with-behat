<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
final class DemoContext implements Context
{
    /** @var Crawler|null */
    private $response;
    private Post $post;

    public function __construct(
        private KernelBrowser $client,
        private UserRepository $userRepository,
        private PostRepository $articleRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @When a demo scenario sends a request to :path
     * @Given I navigate to :path
     */
    public function aDemoScenarioSendsARequestTo(string $path): void
    {
        // Notice that response is now an instance of \Symfony\Component\DomCrawler\Crawler
        $this->response = $this->client->request('GET', $path);
    }

    /**
     * @Then the response should be received
     */
    public function theResponseShouldBeReceived(): void
    {
        assert($this->client->getResponse()->getStatusCode() === 200);
    }

    /**
     * @Then I should see the text :text
     */
    public function iShouldSeeTheText($text)
    {
        if (!str_contains($this->response->text(), $text)) {
            throw new \RuntimeException("Cannot find expected text '$text'");
        }
    }

    /**
     * @When I click on :link
     */
    public function iClickOn($link)
    {
        $this->response = $this->client->clickLink($link);
    }

    /**
     * @Given I am logged in as a user
     */
    public function iAmLoggedInAsAUser()
    {
        $user = $this->userRepository->findOneBy(['username' => 'john_user']);
        $this->client->loginUser($user);
    }

    /**
     * @Given I navigate on a post
     */
    public function iNavigateOnAPost()
    {
        $this->post = $this->articleRepository->findAll()[0];
        // Not storing it is fine, we just need to load the data
        $this->post->getComments()->count();
        $this->aDemoScenarioSendsARequestTo('/en/blog/posts/'.$this->post->getSlug());
    }

    /**
     * @Then the post should have a new comment
     */
    public function theArticleShouldHaveANewComment()
    {
        $previousCount = $this->post->getComments()->count();

        // Re-loading the post with fresh data
        $this->post = $this->articleRepository->findAll()[0];

        $currentCount = $this->post->getComments()->count();

        assert($previousCount+1 === $currentCount);
    }

    /**
     * @When I fill a comment with :content
     */
    public function iFillACommentWith($content)
    {
        $this->client->submitForm('Publish comment', [
            'comment[content]' => $content,
        ]);
        $this->response = $this->client->followRedirect();
    }
}
