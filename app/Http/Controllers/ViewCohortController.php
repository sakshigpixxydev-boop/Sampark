<?php

namespace App\Http\Controllers;

use App\Support\ViewCohort;
use Illuminate\Http\Request;

/**
 * Sets the header's All / School / Regular selection for the session.
 */
class ViewCohortController extends Controller
{
    public function switch(Request $request, string $cohort)
    {
        if (!ViewCohort::isAvailable()) {
            abort(403);
        }

        if (!array_key_exists($cohort, ViewCohort::options())) {
            abort(404);
        }

        session([ViewCohort::SESSION_KEY => $cohort]);

        return back();
    }
}
