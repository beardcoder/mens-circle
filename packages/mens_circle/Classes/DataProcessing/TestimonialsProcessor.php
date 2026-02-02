<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\DataProcessing;

use BeardCoder\MensCircle\Domain\Repository\TestimonialRepository;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

final class TestimonialsProcessor implements DataProcessorInterface
{
    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string, mixed> $processedData
     * @return array<string, mixed>
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $as = $processorConfiguration['as'] ?? 'testimonials';
        $processedData[$as] = $this->testimonialRepository->findApproved();

        return $processedData;
    }
}
