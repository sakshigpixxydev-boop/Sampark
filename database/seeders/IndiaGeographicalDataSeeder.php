<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\State;
use App\Models\District;
use App\Models\Taluko;

class IndiaGeographicalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countryId = DB::table('ms_country')->where('cname', 'India')->value('country_id');

        if (!$countryId) {
            $this->command->error("Country 'India' not found. Please populate countries first.");
            return;
        }

        $csvFile = __DIR__ . '/data/indian-geographical-data.csv';

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found at: $csvFile");
            return;
        }

        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header

        // Cache to minimize DB queries
        // State Name -> State ID
        $statesCache = State::where('country_id', $countryId)->pluck('state_id', 'sname')->toArray();

        // District Name -> District ID (keyed by state_id to avoid name collisions across states if any)
        // For simplicity and given the CSV structure, we might need a composite key or just look up carefully.
        // Let's build a nested cache: State ID -> [District Name -> District ID]
        $districtsCache = [];
        $existingDistricts = District::all(); // Load all might be heavy if massive, but for thousands it's okay-ish. 
        // Optimization: Load districts for relevant states only if needed, but let's just optimize on the fly.
        foreach ($existingDistricts as $dist) {
            $districtsCache[$dist->state_id][$dist->dname] = $dist->dist_id;
        }

        $this->command->info('Importing geographical data...');

        $row = 0;
        DB::beginTransaction();

        try {
            while (($data = fgetcsv($file)) !== false) {
                $row++;
                // CSV columns: sr., State, District, Taluka
                // Index 1: State
                // Index 2: District
                // Index 3: Taluka

                $stateName = trim($data[1] ?? '');
                $districtName = trim($data[2] ?? '');
                $talukaName = trim($data[3] ?? '');

                if (empty($stateName)) {
                    continue;
                }

                // 1. Process State
                if (isset($statesCache[$stateName])) {
                    $stateId = $statesCache[$stateName];
                } else {
                    $state = State::create([
                        'country_id' => $countryId,
                        'sname' => $stateName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stateId = $state->state_id;
                    $statesCache[$stateName] = $stateId;
                }

                if (empty($districtName)) {
                    continue;
                }

                // 2. Process District
                if (isset($districtsCache[$stateId][$districtName])) {
                    $districtId = $districtsCache[$stateId][$districtName];
                } else {
                    $district = District::create([
                        'state_id' => $stateId,
                        'dname' => $districtName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $districtId = $district->dist_id;
                    $districtsCache[$stateId][$districtName] = $districtId;
                }

                if (empty($talukaName)) {
                    continue;
                }

                // 3. Process Taluka
                // We won't cache Talukas entirely as they are the leaf nodes and we insert them mostly.
                // But to allow re-running, we check existence.
                // Assuming unique taluka name per district.
                Taluko::firstOrCreate(
                    [
                        'dist_id' => $districtId,
                        'tname' => $talukaName,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                if ($row % 500 == 0) {
                    $this->command->info("Processed $row rows...");
                }
            }

            DB::commit();
            $this->command->info("Import completed successfully! Total rows: $row");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error at row $row: " . $e->getMessage());
        } finally {
            fclose($file);
        }
    }
}
