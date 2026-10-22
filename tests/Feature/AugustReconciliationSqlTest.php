<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Executes the reconciliation queries in docs/sql/august-payment-reconciliation.sql
 * against the real migrated schema, so a wrong table or column name is caught
 * here rather than in phpMyAdmin against production.
 *
 * Only the database-only sections (1, 1b, 2, 3) run; section 4 uses MySQL-only
 * syntax (STR_TO_DATE, KEY in CREATE TABLE) and a staging table.
 */
class AugustReconciliationSqlTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconciliation_queries_execute_against_the_real_schema()
    {
        $sql = file_get_contents(base_path('docs/sql/august-payment-reconciliation.sql'));

        // Everything before section 4 is portable enough to run here.
        $portable = substr($sql, 0, strpos($sql, '-- 4. Compare against the Razorpay payments export'));

        $statements = array_filter(array_map('trim', explode(';', $portable)), function ($s) {
            $s = preg_replace('/^\s*--.*$/m', '', $s);
            return trim($s) !== '';
        });

        $this->assertGreaterThanOrEqual(4, count($statements), 'Expected the four database-only queries');

        foreach ($statements as $statement) {
            // Throws if a table or column does not exist.
            DB::select($statement);
        }
    }
}
