<?php

namespace App\Tests\Core\Capability;

use App\Core\Capability\LimitChecker;
use App\Core\Capability\PlanCapabilityCatalog;
use App\Core\Enum\Limit;
use App\Core\Enum\ServicePlan;
use App\Core\Exception\LimitExceededException;
use PHPUnit\Framework\TestCase;

final class LimitCheckerTest extends TestCase
{
    private LimitChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new LimitChecker(new PlanCapabilityCatalog());
    }

    public function testCommunityHasMaxActiveEventsLimit(): void
    {
        self::assertSame(
            10,
            $this->checker->getLimit(ServicePlan::Community, Limit::MaxActiveEvents),
        );
    }

    public function testProHasUnlimitedActiveEvents(): void
    {
        self::assertNull(
            $this->checker->getLimit(ServicePlan::Pro, Limit::MaxActiveEvents),
        );
    }

    public function testCommunityAllowsValueWithinLimit(): void
    {
        $this->checker->assertWithinLimit(
            ServicePlan::Community,
            Limit::MaxActiveEvents,
            currentValue: 9,
            increment: 1,
        );

        self::assertTrue(true);
    }

    public function testCommunityRejectsValueAboveLimit(): void
    {
        $this->expectException(LimitExceededException::class);

        $this->checker->assertWithinLimit(
            ServicePlan::Community,
            Limit::MaxActiveEvents,
            currentValue: 10,
            increment: 1,
        );
    }

    public function testProAllowsUnlimitedValue(): void
    {
        $this->checker->assertWithinLimit(
            ServicePlan::Pro,
            Limit::MaxActiveEvents,
            currentValue: 100000,
            increment: 1,
        );

        self::assertTrue(true);
    }
}
