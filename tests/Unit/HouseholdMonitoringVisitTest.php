<?php

namespace Tests\Unit;

use App\Models\HouseholdMonitoringVisit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HouseholdMonitoringVisitTest extends TestCase
{
    #[DataProvider('bloodPressureAssessmentProvider')]
    public function test_blood_pressure_assessment_uses_requested_ranges(string $reading, ?string $expectedLabel): void
    {
        $assessment = HouseholdMonitoringVisit::bloodPressureAssessment($reading);

        if ($expectedLabel === null) {
            $this->assertNull($assessment);
            return;
        }

        $this->assertNotNull($assessment);
        $this->assertSame($expectedLabel, $assessment['label']);
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function bloodPressureAssessmentProvider(): array
    {
        return [
            'low' => ['85/55', 'Low Blood Pressure'],
            'normal' => ['110/70', 'Normal Blood Pressure'],
            'normal upper boundary' => ['120/80', 'Normal Blood Pressure'],
            'systolic lower boundary' => ['90/60', 'Normal Blood Pressure'],
            'pre-high' => ['130/85', 'Pre-high Blood Pressure'],
            'systolic pre-high boundary' => ['121/80', 'Pre-high Blood Pressure'],
            'diastolic pre-high boundary' => ['120/81', 'Pre-high Blood Pressure'],
            'high' => ['150/95', 'High Blood Pressure'],
            'high threshold' => ['141/91', 'High Blood Pressure'],
            'mixed low and normal uses higher risk' => ['85/70', 'Normal Blood Pressure'],
            'mixed normal and high uses higher risk' => ['120/95', 'High Blood Pressure'],
            'pre-high upper boundary fills gap' => ['140/90', 'Pre-high Blood Pressure'],
            'invalid systolic' => ['301/80', null],
            'invalid diastolic' => ['120/201', null],
            'invalid format' => ['not-a-reading', null],
        ];
    }

    public function test_very_high_readings_remain_high_and_are_flagged_as_crisis(): void
    {
        $assessment = HouseholdMonitoringVisit::bloodPressureAssessment('181/121');

        $this->assertNotNull($assessment);
        $this->assertSame('High Blood Pressure', $assessment['label']);
        $this->assertTrue($assessment['is_crisis']);
    }
}
