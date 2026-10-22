<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * The global "which students am I looking at" switch driven by the header
 * dropdown. It is stored in the session and read by the Student model's global
 * scope, so choosing School or Regular narrows every page at once.
 *
 * Returns null — meaning no filtering at all — unless a signed-in user who is
 * allowed to use the switch has actively chosen a cohort. That keeps the public
 * registration flow, queued jobs and console commands untouched.
 */
class ViewCohort
{
    public const SESSION_KEY = 'view_cohort';

    public const ALL = 'all';
    public const SCHOOL = 'school';
    public const REGULAR = 'regular';

    /**
     * The active cohort, or null when everything should be visible.
     */
    public static function current(): ?string
    {
        if (!self::isAvailable()) {
            return null;
        }

        $cohort = session(self::SESSION_KEY, self::ALL);

        return in_array($cohort, [self::SCHOOL, self::REGULAR], true) ? $cohort : null;
    }

    /**
     * What the dropdown should show as selected.
     */
    public static function selected(): string
    {
        return self::current() ?? self::ALL;
    }

    /**
     * Memoised for the request: the global scope calls this on every Student
     * query, and the role lookup behind it would otherwise be a query each time.
     */
    private static ?bool $available = null;

    /**
     * May the current user switch cohorts? School-side roles already only see
     * their own school, so the switch would mean nothing to them.
     */
    public static function isAvailable(): bool
    {
        if (self::$available !== null) {
            return self::$available;
        }

        if (app()->runningInConsole()) {
            return self::$available = false;
        }

        $user = Auth::user();

        return self::$available = $user !== null
            && method_exists($user, 'canRunRegistrationOps')
            && $user->canRunRegistrationOps();
    }

    /**
     * Drop the memoised answer — for tests, and after switching user.
     */
    public static function flush(): void
    {
        self::$available = null;
    }

    /**
     * Options for the header dropdown, in display order.
     */
    public static function options(): array
    {
        return [
            self::ALL => 'All Users',
            self::SCHOOL => 'School Users',
            self::REGULAR => 'Regular Users',
        ];
    }

    public static function label(?string $cohort = null): string
    {
        $cohort ??= self::selected();

        return self::options()[$cohort] ?? self::options()[self::ALL];
    }

    /**
     * Narrow a query over the student table to the active cohort. Columns are
     * table-qualified so it is safe inside joins.
     */
    public static function apply($query, ?string $cohort = null, string $table = 'student'): void
    {
        $cohort ??= self::current();

        if ($cohort === null) {
            return;
        }

        if ($cohort === self::SCHOOL) {
            $query->where(function ($q) use ($table) {
                $q->where("{$table}.registered_via_school_link", 1)
                    ->orWhereNotNull("{$table}.batch_id");
            });

            return;
        }

        $query->where("{$table}.registered_via_school_link", 0)
            ->whereNull("{$table}.batch_id");
    }
}
