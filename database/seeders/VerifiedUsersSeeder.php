<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ZipCode;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VerifiedUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 300 verified users equally distributed among all 50 states (6 per state).
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        $this->command->info('Creating 300 verified users across 50 states...');

        // Sample ZIP codes for each state (one representative ZIP per state)
        $stateZips = [
            'AL' => ['35203', '35801', '36602', '35401', '36104', '35124'], // Birmingham, Huntsville, Mobile, Tuscaloosa, Montgomery, Pelham
            'AK' => ['99501', '99701', '99801', '99508', '99502', '99504'], // Anchorage, Fairbanks, Juneau
            'AZ' => ['85001', '85701', '85281', '85201', '85251', '85301'], // Phoenix, Tucson, Tempe, Mesa, Scottsdale, Glendale
            'AR' => ['72201', '72701', '72401', '71601', '72032', '72076'], // Little Rock, Fayetteville, Jonesboro, Pine Bluff
            'CA' => ['90001', '94102', '92101', '95814', '90802', '94601'], // LA, SF, San Diego, Sacramento, Long Beach, Oakland
            'CO' => ['80202', '80903', '80525', '81001', '80301', '80012'], // Denver, Colorado Springs, Fort Collins, Pueblo, Boulder, Aurora
            'CT' => ['06103', '06510', '06604', '06901', '06702', '06360'], // Hartford, New Haven, Bridgeport, Stamford, Waterbury, Norwich
            'DE' => ['19801', '19901', '19711', '19720', '19713', '19702'], // Wilmington, Dover, Newark, New Castle, Bear
            'FL' => ['33101', '32801', '33602', '32301', '33301', '32202'], // Miami, Orlando, Tampa, Tallahassee, Fort Lauderdale, Jacksonville
            'GA' => ['30303', '31401', '30901', '31201', '30601', '30040'], // Atlanta, Savannah, Augusta, Macon, Athens, Cumming
            'HI' => ['96801', '96813', '96720', '96732', '96740', '96766'], // Honolulu, Hilo, Kahului, Kailua-Kona
            'ID' => ['83702', '83814', '83201', '83301', '83501', '83651'], // Boise, Coeur d'Alene, Pocatello, Twin Falls, Lewiston, Nampa
            'IL' => ['60601', '62701', '61602', '61101', '60505', '62002'], // Chicago, Springfield, Peoria, Rockford, Aurora, Alton
            'IN' => ['46201', '46801', '46601', '47901', '47374', '46402'], // Indianapolis, Fort Wayne, South Bend, Lafayette, Richmond, Gary
            'IA' => ['50309', '52401', '52801', '51101', '50010', '50613'], // Des Moines, Cedar Rapids, Davenport, Sioux City, Ames, Cedar Falls
            'KS' => ['66101', '67202', '66044', '66801', '67401', '66502'], // Kansas City, Wichita, Lawrence, Emporia, Salina, Manhattan
            'KY' => ['40202', '40507', '42101', '41011', '40361', '42001'], // Louisville, Lexington, Bowling Green, Covington, Paris, Paducah
            'LA' => ['70112', '70801', '71101', '70501', '70001', '71201'], // New Orleans, Baton Rouge, Shreveport, Lafayette, Metairie, Monroe
            'ME' => ['04101', '04401', '04240', '04103', '04210', '04330'], // Portland, Bangor, Lewiston, South Portland, Auburn, Augusta
            'MD' => ['21201', '20901', '21502', '21740', '21401', '20850'], // Baltimore, Silver Spring, Cumberland, Hagerstown, Annapolis, Rockville
            'MA' => ['02101', '01101', '01602', '02601', '01854', '02138'], // Boston, Springfield, Worcester, Barnstable, Lowell, Cambridge
            'MI' => ['48201', '49503', '48602', '48933', '48503', '49001'], // Detroit, Grand Rapids, Saginaw, Lansing, Flint, Kalamazoo
            'MN' => ['55401', '55101', '55802', '56001', '55901', '56301'], // Minneapolis, St Paul, Duluth, Mankato, Rochester, St Cloud
            'MS' => ['39201', '39501', '38801', '39440', '39301', '38655'], // Jackson, Gulfport, Tupelo, Laurel, Meridian, Oxford
            'MO' => ['63101', '64101', '65801', '65201', '63301', '65401'], // St Louis, Kansas City, Springfield, Columbia, St Charles, Rolla
            'MT' => ['59601', '59101', '59401', '59801', '59701', '59901'], // Helena, Billings, Great Falls, Missoula, Butte, Kalispell
            'NE' => ['68102', '68502', '68847', '68901', '69101', '68005'], // Omaha, Lincoln, Kearney, Hastings, North Platte, Bellevue
            'NV' => ['89101', '89501', '89701', '89801', '89015', '89002'], // Las Vegas, Reno, Carson City, Elko, Henderson, Boulder City
            'NH' => ['03101', '03301', '03801', '03060', '03431', '03304'], // Manchester, Concord, Portsmouth, Nashua, Keene, Bow
            'NJ' => ['07102', '08608', '07030', '08901', '07601', '08002'], // Newark, Trenton, Hoboken, New Brunswick, Hackensack, Cherry Hill
            'NM' => ['87101', '88001', '87501', '88201', '87301', '87401'], // Albuquerque, Las Cruces, Santa Fe, Roswell, Gallup, Farmington
            'NY' => ['10001', '14201', '12207', '13201', '10701', '11201'], // NYC, Buffalo, Albany, Syracuse, Yonkers, Brooklyn
            'NC' => ['27601', '28202', '27401', '27101', '28801', '28401'], // Raleigh, Charlotte, Greensboro, Winston-Salem, Asheville, Wilmington
            'ND' => ['58102', '58501', '58201', '58701', '58301', '58401'], // Fargo, Bismarck, Grand Forks, Minot, Devils Lake, Jamestown
            'OH' => ['43215', '44101', '45202', '43604', '44308', '45402'], // Columbus, Cleveland, Cincinnati, Toledo, Akron, Dayton
            'OK' => ['73102', '74101', '73069', '74401', '73401', '74008'], // Oklahoma City, Tulsa, Norman, Muskogee, Lawton, Bixby
            'OR' => ['97201', '97401', '97301', '97701', '97501', '97030'], // Portland, Eugene, Salem, Bend, Medford, Gresham
            'PA' => ['19102', '15222', '18101', '17101', '19601', '16501'], // Philadelphia, Pittsburgh, Allentown, Harrisburg, Reading, Erie
            'RI' => ['02903', '02860', '02840', '02886', '02861', '02893'], // Providence, Pawtucket, Newport, Warwick, Cranston
            'SC' => ['29201', '29401', '29601', '29801', '29501', '29403'], // Columbia, Charleston, Greenville, Aiken, Florence
            'SD' => ['57101', '57701', '57401', '57350', '57042', '57078'], // Sioux Falls, Rapid City, Aberdeen, Huron, Madison, Vermillion
            'TN' => ['37201', '38103', '37902', '37402', '37040', '38301'], // Nashville, Memphis, Knoxville, Chattanooga, Clarksville, Jackson
            'TX' => ['77001', '78201', '75201', '79901', '76001', '78401'], // Houston, San Antonio, Dallas, El Paso, Arlington, Corpus Christi
            'UT' => ['84101', '84601', '84401', '84321', '84003', '84057'], // Salt Lake City, Provo, Ogden, Logan, American Fork, Orem
            'VT' => ['05401', '05602', '05701', '05301', '05201', '05001'], // Burlington, Montpelier, Rutland, Brattleboro, Bennington, White River Junction
            'VA' => ['23219', '23510', '22901', '24011', '20190', '22401'], // Richmond, Norfolk, Charlottesville, Roanoke, Reston, Fredericksburg
            'WA' => ['98101', '99201', '98401', '98801', '98225', '99301'], // Seattle, Spokane, Tacoma, Wenatchee, Bellingham, Pasco
            'WV' => ['25301', '26003', '25401', '26501', '25701', '26101'], // Charleston, Wheeling, Martinsburg, Morgantown, Huntington, Parkersburg
            'WI' => ['53202', '53703', '54301', '53081', '54901', '54401'], // Milwaukee, Madison, Green Bay, Sheboygan, Oshkosh, Wausau
            'WY' => ['82001', '82601', '82801', '82901', '82414', '82070'], // Cheyenne, Casper, Sheridan, Rock Springs, Cody, Laramie
        ];

        // Congressional districts by state (simplified - uses state-01 format)
        $usersCreated = 0;

        foreach ($stateZips as $stateCode => $zips) {
            for ($i = 0; $i < 6; $i++) {
                $zip = $zips[$i % count($zips)];
                
                // Generate a congressional district (simplified)
                $districtNum = ($i % 3) + 1; // Cycles through districts 1-3
                $district = "{$stateCode}-" . str_pad($districtNum, 2, '0', STR_PAD_LEFT);

                User::create([
                    'name' => $faker->name(),
                    'email' => $faker->unique()->safeEmail(),
                    'email_verified_at' => now(),
                    'password' => Hash::make('CRazyhorse21@!'),
                    'remember_token' => Str::random(10),
                    'zip_code' => $zip,
                    'congressional_district' => $district,
                    'guidelines_accepted_at' => $faker->boolean(70) ? now() : null, // 70% have accepted guidelines
                ]);

                $usersCreated++;
            }

            $this->command->info("Created 6 users in {$stateCode}");
        }

        $this->command->info("✓ Successfully created {$usersCreated} verified users across 50 states!");
    }
}
