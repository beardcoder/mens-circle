<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Tests\Unit\Domain;

use BeardCoder\MensCircle\Domain\Model\NewsletterSubscription;
use PHPUnit\Framework\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    private NewsletterSubscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscription = new NewsletterSubscription();
    }

    public function testNewSubscriptionHasTokens(): void
    {
        self::assertNotEmpty($this->subscription->getConfirmationToken());
        self::assertNotEmpty($this->subscription->getUnsubscribeToken());
        self::assertNotEqual(
            $this->subscription->getConfirmationToken(),
            $this->subscription->getUnsubscribeToken(),
        );
    }

    public function testNewSubscriptionIsNotConfirmed(): void
    {
        self::assertFalse($this->subscription->isConfirmed());
        self::assertNull($this->subscription->getConfirmedAt());
    }

    public function testConfirmSetsConfirmedAndTimestamp(): void
    {
        $this->subscription->confirm();
        self::assertTrue($this->subscription->isConfirmed());
        self::assertNotNull($this->subscription->getConfirmedAt());
        self::assertInstanceOf(\DateTimeInterface::class, $this->subscription->getConfirmedAt());
    }

    public function testEmailGetterSetter(): void
    {
        $this->subscription->setEmail('test@example.com');
        self::assertEquals('test@example.com', $this->subscription->getEmail());
    }
}
