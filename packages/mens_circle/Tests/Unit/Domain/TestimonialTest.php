<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Tests\Unit\Domain;

use BeardCoder\MensCircle\Domain\Model\Testimonial;
use PHPUnit\Framework\TestCase;

class TestimonialTest extends TestCase
{
    private Testimonial $testimonial;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testimonial = new Testimonial();
    }

    public function testNewTestimonialIsNotApproved(): void
    {
        self::assertFalse($this->testimonial->isApproved());
    }

    public function testAuthorNameGetterSetter(): void
    {
        $this->testimonial->setAuthorName('Max Mustermann');
        self::assertEquals('Max Mustermann', $this->testimonial->getAuthorName());
    }

    public function testContentGetterSetter(): void
    {
        $content = 'Dies ist ein tolles Erlebnis gewesen!';
        $this->testimonial->setContent($content);
        self::assertEquals($content, $this->testimonial->getContent());
    }

    public function testGetQuoteAliasForGetContent(): void
    {
        $content = 'Test quote content';
        $this->testimonial->setContent($content);
        self::assertEquals($content, $this->testimonial->getQuote());
    }

    public function testRoleGetterSetter(): void
    {
        $this->testimonial->setRole('Teilnehmer');
        self::assertEquals('Teilnehmer', $this->testimonial->getRole());
    }

    public function testIsApprovedGetterSetter(): void
    {
        self::assertFalse($this->testimonial->isApproved());
        $this->testimonial->setIsApproved(true);
        self::assertTrue($this->testimonial->isApproved());
    }

    public function testCreatedAtIsSetInConstructor(): void
    {
        self::assertInstanceOf(\DateTimeInterface::class, $this->testimonial->getCreatedAt());
    }
}

