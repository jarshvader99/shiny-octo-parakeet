<?php

namespace Database\Seeders;

use App\Models\ZipCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZipCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding ZIP codes...');

        // Get all ZIP codes from the static data
        $zipData = ZipCode::get_zips();

        $this->command->info('Found ' . count($zipData) . ' ZIP codes to seed');

        // Process in batches for better performance
        $batch = [];
        $batchSize = 500;
        $count = 0;

        foreach ($zipData as $zipCode => $coordinates) {
            $stateCode = $this->getStateFromZip($zipCode);

            $batch[] = [
                'zip_code' => $zipCode,
                'latitude' => $coordinates['lat'],
                'longitude' => $coordinates['lng'],
                'state_code' => $stateCode,
                'congressional_district' => null, // Will be filled in on first lookup
                'city' => null,
                'county' => null,
                'district_updated_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $count++;

            // Insert batch when it reaches the batch size
            if (count($batch) >= $batchSize) {
                DB::table('zip_codes')->insert($batch);
                $this->command->info("Inserted {$count} ZIP codes...");
                $batch = [];
            }
        }

        // Insert any remaining records
        if (!empty($batch)) {
            DB::table('zip_codes')->insert($batch);
        }

        $this->command->info("✓ Successfully seeded {$count} ZIP codes!");
    }

    /**
     * Derive state code from ZIP code using accurate ranges
     */
    private function getStateFromZip(string $zipCode): string
    {
        $zipNum = (int)substr($zipCode, 0, 3);

        return match(true) {
            $zipNum >= 350 && $zipNum <= 369 => 'AL',
            $zipNum >= 995 && $zipNum <= 999 => 'AK',
            $zipNum >= 850 && $zipNum <= 865 => 'AZ',
            $zipNum >= 716 && $zipNum <= 729 => 'AR',
            $zipNum >= 900 && $zipNum <= 961 => 'CA',
            $zipNum >= 800 && $zipNum <= 816 => 'CO',
            $zipNum >= 60 && $zipNum <= 69 => 'CT',
            $zipNum >= 197 && $zipNum <= 199 => 'DE',
            $zipNum >= 200 && $zipNum <= 205 => 'DC',
            $zipNum >= 320 && $zipNum <= 349 => 'FL',
            $zipNum >= 300 && $zipNum <= 319 => 'GA',
            $zipNum >= 967 && $zipNum <= 968 => 'HI',
            $zipNum >= 832 && $zipNum <= 838 => 'ID',
            $zipNum >= 600 && $zipNum <= 629 => 'IL',
            $zipNum >= 460 && $zipNum <= 479 => 'IN',
            $zipNum >= 500 && $zipNum <= 528 => 'IA',
            $zipNum >= 660 && $zipNum <= 679 => 'KS',
            $zipNum >= 400 && $zipNum <= 427 => 'KY',
            $zipNum >= 700 && $zipNum <= 714 => 'LA',
            $zipNum >= 39 && $zipNum <= 49 => 'ME',
            $zipNum >= 206 && $zipNum <= 219 => 'MD',
            $zipNum >= 10 && $zipNum <= 27 => 'MA',
            $zipNum >= 480 && $zipNum <= 499 => 'MI',
            $zipNum >= 550 && $zipNum <= 567 => 'MN',
            $zipNum >= 386 && $zipNum <= 397 => 'MS',
            $zipNum >= 630 && $zipNum <= 658 => 'MO',
            $zipNum >= 590 && $zipNum <= 599 => 'MT',
            $zipNum >= 680 && $zipNum <= 693 => 'NE',
            $zipNum >= 889 && $zipNum <= 898 => 'NV',
            $zipNum >= 30 && $zipNum <= 38 => 'NH',
            $zipNum >= 70 && $zipNum <= 89 => 'NJ',
            $zipNum >= 870 && $zipNum <= 884 => 'NM',
            $zipNum >= 100 && $zipNum <= 149 => 'NY',
            $zipNum >= 270 && $zipNum <= 289 => 'NC',
            $zipNum >= 580 && $zipNum <= 588 => 'ND',
            $zipNum >= 430 && $zipNum <= 458 => 'OH',
            $zipNum >= 730 && $zipNum <= 749 => 'OK',
            $zipNum >= 970 && $zipNum <= 979 => 'OR',
            $zipNum >= 150 && $zipNum <= 196 => 'PA',
            $zipNum >= 28 && $zipNum <= 29 => 'RI',
            $zipNum >= 290 && $zipNum <= 299 => 'SC',
            $zipNum >= 570 && $zipNum <= 577 => 'SD',
            $zipNum >= 370 && $zipNum <= 385 => 'TN',
            $zipNum >= 750 && $zipNum <= 799 => 'TX',
            $zipNum >= 840 && $zipNum <= 847 => 'UT',
            $zipNum >= 50 && $zipNum <= 59 => 'VT',
            $zipNum >= 220 && $zipNum <= 246 => 'VA',
            $zipNum >= 980 && $zipNum <= 994 => 'WA',
            $zipNum >= 247 && $zipNum <= 268 => 'WV',
            $zipNum >= 530 && $zipNum <= 549 => 'WI',
            $zipNum >= 820 && $zipNum <= 831 => 'WY',
            // Puerto Rico and territories
            $zipNum >= 6 && $zipNum <= 9 => 'PR',
            $zipNum >= 969 && $zipNum <= 969 => 'GU',
            $zipNum >= 96950 && $zipNum <= 96952 => 'MP',
            $zipNum >= 96960 && $zipNum <= 96970 => 'AS',
            default => 'XX',
        };
    }
}
