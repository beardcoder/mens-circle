<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Tests\Unit\Service;

use BeardCoder\MensCircle\Utility\SlugUtility;
use PHPUnit\Framework\TestCase;

class SlugUtilityTest extends TestCase
{
    public function testSimpleSlugGeneration(): void
    {
        self::assertEquals('hello-world', SlugUtility::generate('Hello World'));
    }

    public function testGermanUmlauts(): void
    {
        self::assertEquals('maennerkreis-niederbayern', SlugUtility::generate('Männerkreis Niederbayern'));
    }

    public function testSpecialCharactersRemoved(): void
    {
        self::assertEquals('test-event-2024', SlugUtility::generate('Test Event! @2024'));
    }

    public function testMultipleSpacesCollapsed(): void
    {
        self::assertEquals('hello-world', SlugUtility::generate('Hello    World'));
    }

    public function testLeadingTrailingHyphensRemoved(): void
    {
        self::assertEquals('hello', SlugUtility::generate('  Hello  '));
    }

    public function testEmptyString(): void
    {
        self::assertEquals('', SlugUtility::generate(''));
    }

    public function testEszett(): void
    {
        self::assertEquals('strasse', SlugUtility::generate('Straße'));
    }

    public function testMixedCase(): void
    {
        self::assertEquals('mein-event-in-straubing', SlugUtility::generate('Mein Event in Straubing'));
    }
}
