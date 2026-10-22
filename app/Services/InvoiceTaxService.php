<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Student;

/**
 * Single source of truth for the invoice GST breakdown.
 *
 * The split is always DERIVED from the student's state at read time; nothing
 * is read from the invoice table. Place of supply of the seller is Gujarat, so:
 *
 *   - student in Gujarat        -> CGST 9% + SGST 9%   (intra-state)
 *   - student in any other      -> IGST 18%            (inter-state)
 *     Indian state, or a state
 *     we hold with no country
 *   - student explicitly in a   -> no GST              (export of service)
 *     non-Indian country
 *
 * Note the deliberate difference from the older behaviour: a student whose
 * country_id is NULL is treated as Indian and taxed on their state, rather
 * than falling through to "no GST". That NULL-country fallthrough is why some
 * Gujarat invoices historically exported as 2500.00 / 0 / 0 / 0 instead of
 * 2118.64 / 190.68 / 190.68.
 */
class InvoiceTaxService
{
    public const INDIA_COUNTRY_ID = 1;
    public const GUJARAT_STATE_ID = 25;
    public const GST_RATE = 0.18;
    public const HALF_GST_RATE = 0.09;

    /**
     * True when the student's country is set AND is India.
     */
    public function isIndia(?Student $student): bool
    {
        return (bool) ($student && ($student->country_id == self::INDIA_COUNTRY_ID
            || ($student->country && (strtolower($student->country->cname) == 'india'
                || strtolower($student->country->cname) == 'ind'))));
    }

    /**
     * True only when we positively know the student is outside India.
     *
     * A missing country is NOT treated as foreign - it is treated as India, so
     * that the state still drives the tax split.
     */
    public function isForeign(?Student $student): bool
    {
        if (!$student) {
            return false;
        }

        $hasCountry = $student->country_id !== null || $student->country !== null;

        return $hasCountry && !$this->isIndia($student);
    }

    /**
     * Place of supply is Gujarat, so a Gujarat student is intra-state.
     */
    public function isGujarat(?Student $student): bool
    {
        return (bool) ($student && ($student->state_id == self::GUJARAT_STATE_ID
            || ($student->state && str_contains(strtolower($student->state->sname), 'gujarat'))));
    }

    /**
     * Derive the GST split from a GST-inclusive gross amount.
     *
     * @return array{taxable: float, sgst: float, cgst: float, igst: float}
     */
    public function compute(float $totalAmount, bool $isForeign, bool $isGujarat): array
    {
        if ($isForeign) {
            // Export of service - no GST, so the whole amount is taxable value.
            return ['taxable' => $totalAmount, 'sgst' => 0.0, 'cgst' => 0.0, 'igst' => 0.0];
        }

        $baseAmount = $totalAmount / (1 + self::GST_RATE);

        if ($isGujarat) {
            return [
                'taxable' => $baseAmount,
                'sgst' => $baseAmount * self::HALF_GST_RATE,
                'cgst' => $baseAmount * self::HALF_GST_RATE,
                'igst' => 0.0,
            ];
        }

        return [
            'taxable' => $baseAmount,
            'sgst' => 0.0,
            'cgst' => 0.0,
            'igst' => $baseAmount * self::GST_RATE,
        ];
    }

    /**
     * Resolve the tax figures for an invoice from its student's state.
     *
     * @return array{taxable: float, sgst: float, cgst: float, igst: float, total: float, is_india: bool, is_gujarat: bool, is_foreign: bool}
     */
    public function resolve(Invoice $invoice): array
    {
        return $this->resolveForStudent($invoice->student, floatval($invoice->total_due));
    }

    /**
     * @return array{taxable: float, sgst: float, cgst: float, igst: float, total: float, is_india: bool, is_gujarat: bool, is_foreign: bool}
     */
    public function resolveForStudent(?Student $student, float $totalAmount): array
    {
        $isForeign = $this->isForeign($student);
        $isGujarat = $this->isGujarat($student);

        return $this->compute($totalAmount, $isForeign, $isGujarat) + [
            'total' => $totalAmount,
            // The PDF template switches between "TAX INVOICE" and "EXPORT
            // INVOICE" on this flag, so it stays a positive statement about
            // the student rather than the negation of is_foreign.
            'is_india' => !$isForeign,
            'is_gujarat' => $isGujarat,
            'is_foreign' => $isForeign,
        ];
    }

    /**
     * Taxable value of a single line item, on the same basis as the invoice.
     *
     * @param  array{is_foreign: bool}  $tax
     */
    public function itemTaxable(InvoiceItem $item, array $tax): float
    {
        $lineAmount = floatval($item->line_amount);

        return !empty($tax['is_foreign']) ? $lineAmount : $lineAmount / (1 + self::GST_RATE);
    }
}
