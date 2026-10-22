<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
use App\Models\District;
use App\Models\Taluko;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // Countries
    public function indexCountries()
    {
        $countries = Country::orderBy('cname')->paginate(15);
        return view('locations.countries.index', compact('countries'));
    }

    public function createCountry()
    {
        return view('locations.countries.create');
    }

    public function storeCountry(Request $request)
    {
        $validated = $request->validate([
            'cname' => 'required|string|max:100',
        ]);

        Country::create($validated);

        return redirect()->route('locations.countries.index')->with('success', 'Country created successfully.');
    }

    public function editCountry(Country $country)
    {
        return view('locations.countries.edit', compact('country'));
    }

    public function updateCountry(Request $request, Country $country)
    {
        $validated = $request->validate([
            'cname' => 'required|string|max:100',
        ]);

        $country->update($validated);

        return redirect()->route('locations.countries.index')->with('success', 'Country updated successfully.');
    }

    public function destroyCountry(Country $country)
    {
        if ($country->states()->count() > 0) {
            return redirect()->route('locations.countries.index')
                ->with('error', 'Cannot delete country that has states.');
        }

        $country->delete();

        return redirect()->route('locations.countries.index')->with('success', 'Country deleted successfully.');
    }

    // States
    public function indexStates()
    {
        $states = State::with('country')->orderBy('sname')->paginate(15);
        return view('locations.states.index', compact('states'));
    }

    public function createState()
    {
        $countries = Country::orderBy('cname')->get();
        return view('locations.states.create', compact('countries'));
    }

    public function storeState(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:ms_country,country_id',
            'sname' => 'required|string|max:100',
        ]);

        State::create($validated);

        return redirect()->route('locations.states.index')->with('success', 'State created successfully.');
    }

    public function editState(State $state)
    {
        $countries = Country::orderBy('cname')->get();
        return view('locations.states.edit', compact('state', 'countries'));
    }

    public function updateState(Request $request, State $state)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:ms_country,country_id',
            'sname' => 'required|string|max:100',
        ]);

        $state->update($validated);

        return redirect()->route('locations.states.index')->with('success', 'State updated successfully.');
    }

    public function destroyState(State $state)
    {
        if ($state->districts()->count() > 0) {
            return redirect()->route('locations.states.index')
                ->with('error', 'Cannot delete state that has districts.');
        }

        $state->delete();

        return redirect()->route('locations.states.index')->with('success', 'State deleted successfully.');
    }

    // Districts
    public function indexDistricts()
    {
        $districts = District::with('state.country')->orderBy('dname')->paginate(15);
        return view('locations.districts.index', compact('districts'));
    }

    public function createDistrict()
    {
        $states = State::with('country')->orderBy('sname')->get();
        return view('locations.districts.create', compact('states'));
    }

    public function storeDistrict(Request $request)
    {
        $validated = $request->validate([
            'state_id' => 'required|exists:ms_state,state_id',
            'dname' => 'required|string|max:100',
        ]);

        District::create($validated);

        return redirect()->route('locations.districts.index')->with('success', 'District created successfully.');
    }

    public function editDistrict(District $district)
    {
        $states = State::with('country')->orderBy('sname')->get();
        return view('locations.districts.edit', compact('district', 'states'));
    }

    public function updateDistrict(Request $request, District $district)
    {
        $validated = $request->validate([
            'state_id' => 'required|exists:ms_state,state_id',
            'dname' => 'required|string|max:100',
        ]);

        $district->update($validated);

        return redirect()->route('locations.districts.index')->with('success', 'District updated successfully.');
    }

    public function destroyDistrict(District $district)
    {
        if ($district->talukos()->count() > 0) {
            return redirect()->route('locations.districts.index')
                ->with('error', 'Cannot delete district that has talukos.');
        }

        $district->delete();

        return redirect()->route('locations.districts.index')->with('success', 'District deleted successfully.');
    }

    // Talukos
    public function indexTalukos()
    {
        $talukos = Taluko::with('district.state.country')->orderBy('tname')->paginate(15);
        return view('locations.talukos.index', compact('talukos'));
    }

    public function createTaluko()
    {
        $districts = District::with('state')->orderBy('dname')->get();
        return view('locations.talukos.create', compact('districts'));
    }

    public function storeTaluko(Request $request)
    {
        $validated = $request->validate([
            'dist_id' => 'required|exists:ms_dist,dist_id',
            'tname' => 'required|string|max:100',
        ]);

        Taluko::create($validated);

        return redirect()->route('locations.talukos.index')->with('success', 'Taluko created successfully.');
    }

    public function editTaluko(Taluko $taluko)
    {
        $districts = District::with('state')->orderBy('dname')->get();
        return view('locations.talukos.edit', compact('taluko', 'districts'));
    }

    public function updateTaluko(Request $request, Taluko $taluko)
    {
        $validated = $request->validate([
            'dist_id' => 'required|exists:ms_dist,dist_id',
            'tname' => 'required|string|max:100',
        ]);

        $taluko->update($validated);

        return redirect()->route('locations.talukos.index')->with('success', 'Taluko updated successfully.');
    }

    public function destroyTaluko(Taluko $taluko)
    {
        $schoolsCount = $taluko->schools()->count();
        $studentsCount = $taluko->students()->count();
        
        if ($schoolsCount > 0 || $studentsCount > 0) {
            return redirect()->route('locations.talukos.index')
                ->with('error', 'Cannot delete taluko that is assigned to schools or students.');
        }

        $taluko->delete();

        return redirect()->route('locations.talukos.index')->with('success', 'Taluko deleted successfully.');
    }

    // API endpoints for cascading dropdowns
    public function getStates($countryId)
    {
        $states = State::where('country_id', $countryId)->orderBy('sname')->get();
        return response()->json($states);
    }

    public function getDistricts($stateId)
    {
        $districts = District::where('state_id', $stateId)->orderBy('dname')->get();
        return response()->json($districts);
    }

    public function getTalukos($districtId)
    {
        $talukos = Taluko::where('dist_id', $districtId)->orderBy('tname')->get();
        return response()->json($talukos);
    }

    public function getDistrictDetails($districtId)
    {
        $district = District::find($districtId);
        return response()->json($district);
    }

    public function getTalukaDetails($talukaId)
    {
        $taluka = Taluko::with('district')->find($talukaId);
        return response()->json($taluka);
    }
}
