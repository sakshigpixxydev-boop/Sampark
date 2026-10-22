<?php

namespace App\Services;

use App\Models\FeePlan;
use App\Models\DiscountRule;

class FeePlanService
{
    private static $feePlanCache = [];
    private static $discountCache = [];
    /**
     * Calculate fees for a student based on semester batch
     */
    public function calculateFees($studentData, $semesterBatchId, $specificFeePlanId = null)
    {
        $schoolId = $studentData['school_id'] ?? null;
        $standardId = $studentData['std_id'] ?? null;

        // Get base fee from batch if no specific fee plan
        $batch = \App\Models\SemesterBatch::find($semesterBatchId);
        $baseFee = $batch ? ($batch->fees ?? 0) : 0;

        if ($specificFeePlanId) {
            $cacheKey = "spec_{$specificFeePlanId}";
            if (isset(self::$feePlanCache[$cacheKey])) {
                $feePlans = self::$feePlanCache[$cacheKey];
            } else {
                $feePlans = FeePlan::where('fee_plan_id', $specificFeePlanId)->get();
                self::$feePlanCache[$cacheKey] = $feePlans;
            }
        } else {
            $feePlans = $this->getApplicableFeePlans($semesterBatchId, $schoolId, $standardId);
        }

        // Calculate subtotal: If fee plans exist, use them. Otherwise use base batch fee.
        $subtotal = $feePlans->count() > 0 ? $feePlans->sum('amount') : $baseFee;

        // Apply discounts if applicable
        $discountTotal = $this->calculateDiscounts($studentData, $schoolId, $subtotal);

        // Calculate total due
        $totalDue = $subtotal - $discountTotal;

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'total_due' => max(0, $totalDue), // Ensure non-negative
            'fee_plans' => $feePlans->toArray(),
        ];
    }

    /**
     * Get applicable fee plans for a student
     */
    private function getApplicableFeePlans($semesterBatchId, $schoolId, $standardId)
    {
        $cacheKey = "{$semesterBatchId}_{$schoolId}_{$standardId}";
        if (isset(self::$feePlanCache[$cacheKey])) {
            return self::$feePlanCache[$cacheKey];
        }

        $query = FeePlan::where(function ($q) use ($semesterBatchId) {
                $q->where('sembatches_id', $semesterBatchId)
                  ->orWhereHas('semesterBatches', function ($q2) use ($semesterBatchId) {
                      $q2->where('fee_plan_sembatches.sembatches_id', $semesterBatchId);
                  });
            })
            ->where(function($q) use ($schoolId) {
                if ($schoolId) {
                    $q->where('school_id', $schoolId)
                      ->orWhereNull('school_id');
                } else {
                    $q->whereNull('school_id');
                }
            })
            ->where(function ($q) {
                // Check validity dates
                $q->where('valid_from', '<=', now())
                    ->where(function ($q2) {
                    $q2->whereNull('valid_to')
                        ->orWhere('valid_to', '>=', now());
                });
            });

        return self::$feePlanCache[$cacheKey] = $query->get();
    }

    /**
     * Calculate applicable discounts
     */
    private function calculateDiscounts($studentData, $schoolId, $subtotal)
    {
        $totalDiscount = 0;

        // Apply school-level discounts
        if ($schoolId) {
            if (isset(self::$discountCache[$schoolId])) {
                $schoolDiscounts = self::$discountCache[$schoolId];
            } else {
                $schoolDiscounts = DiscountRule::where('school_id', $schoolId)
                    ->where('active', true)
                    ->where('valid_from', '<=', now())
                    ->where(function ($query) {
                        $query->whereNull('valid_to')
                            ->orWhere('valid_to', '>=', now());
                    })
                    ->get();
                self::$discountCache[$schoolId] = $schoolDiscounts;
            }

            foreach ($schoolDiscounts as $discount) {
                $totalDiscount += $this->applyDiscount($discount, $subtotal);
            }
        }

        return $totalDiscount;
    }

    /**
     * Apply a single discount rule
     */
    private function applyDiscount($discountRule, $subtotal)
    {
        if ($discountRule->discount_type === 'FLAT') {
            return $discountRule->discount_value;
        } else { // PERCENT
            return ($subtotal * $discountRule->discount_value / 100);
        }
    }

    /**
     * Calculate total fees for a batch of students
     */
    public function calculateBatchTotal($students, $semesterBatchId, $specificFeePlanId = null)
    {
        $totalFees = 0;
        $feePerStudent = null;

        foreach ($students as $student) {
            $fee = $student['calculated_fee'] ?? 0;
            $totalFees += $fee;

            if ($feePerStudent === null) {
                $feePerStudent = $fee;
            }
        }

        return [
            'total_fees' => $totalFees,
            'fee_per_student' => $feePerStudent,
            'student_count' => count($students),
        ];
    }
}
