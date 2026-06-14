<?php

namespace App\Tests\Core\Capability;

use App\Core\Capability\FeatureChecker;
use App\Core\Capability\PlanCapabilityCatalog;
use App\Core\Enum\Feature;
use App\Core\Enum\ServicePlan;
use App\Core\Exception\FeatureNotAvailableException;
use PHPUnit\Framework\TestCase;

final class FeatureCheckerTest extends TestCase
{
    private FeatureChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new FeatureChecker(new PlanCapabilityCatalog());
    }

    public function testCommunityHasBasicEventFeature(): void
    {
        self::assertTrue($this->checker->isEnabled(
            ServicePlan::Community,
            Feature::EventBasic,
        ));
    }

    public function testCommunityDoesNotHaveCustomEventFormFeature(): void
    {
        self::assertFalse($this->checker->isEnabled(
            ServicePlan::Community,
            Feature::EventCustomForm,
        ));
    }

    public function testProHasCustomEventFormFeature(): void
    {
        self::assertTrue($this->checker->isEnabled(
            ServicePlan::Pro,
            Feature::EventCustomForm,
        ));
    }

    public function testAssertEnabledThrowsWhenFeatureIsNotAvailable(): void
    {
        $this->expectException(FeatureNotAvailableException::class);

        $this->checker->assertEnabled(
            ServicePlan::Community,
            Feature::EventCustomForm,
        );
    }
}
