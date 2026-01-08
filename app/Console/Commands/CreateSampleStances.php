<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\User;
use App\Models\UserStance;
use Illuminate\Console\Command;

class CreateSampleStances extends Command
{
    protected $signature = 'stances:sample {bill_id}';
    protected $description = 'Create sample stances for testing geographic visualization';

    public function handle()
    {
        $billId = $this->argument('bill_id');
        $bill = Bill::find($billId);

        if (!$bill) {
            $this->error("Bill {$billId} not found");
            return 1;
        }

        // Sample ZIP codes from different states (ensuring at least 5 per state)
        $zipCodes = [
            // California (90001-90089)
            '90001', '90210', '90405', '94102', '94103', '94104', '92101', '92102',
            // Texas (75001-75089)
            '75201', '75202', '75203', '78701', '78702', '78703', '77002', '77003',
            // Florida (33101-33199)
            '33101', '33102', '33130', '32801', '32802', '32803', '32804', '32805',
            // New York (10001-10199)
            '10001', '10002', '10003', '10004', '10005', '10006', '11201', '11202',
        ];

        $stances = ['support', 'oppose', 'mixed', 'undecided', 'needs_more_info'];
        $created = 0;

        foreach ($zipCodes as $zipCode) {
            // Try to find existing test user or create new one
            $email = 'test_' . $zipCode . '@example.com';
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => 'Test User ' . $zipCode,
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'zip_code' => $zipCode,
                    'congressional_district' => 'Sample District',
                ]);
            }

            // Skip if this user already has a stance for this bill
            if (UserStance::where('user_id', $user->id)->where('bill_id', $billId)->exists()) {
                continue;
            }

            // Random stance with varied reasoning lengths
            $stance = $stances[array_rand($stances)];
            $reason = $this->generateReasoning($stance);

            UserStance::create([
                'user_id' => $user->id,
                'bill_id' => $billId,
                'stance' => $stance,
                'reason' => $reason,
                'zip_code' => $zipCode,
            ]);

            $created++;
        }

        $this->info("Created {$created} sample stances for Bill {$billId}");
        $this->info("Geographic visualization should now be visible!");

        return 0;
    }

    private function generateReasoning($stance): string
    {
        $reasons = [
            'support' => [
                'This legislation addresses a critical need in our community and provides necessary funding for infrastructure improvements.',
                'I support this bill because it includes provisions for environmental protection while promoting economic growth.',
                'The bill\'s approach to healthcare access aligns with the needs of working families in my district.',
            ],
            'oppose' => [
                'This bill places an undue burden on small businesses and could lead to job losses in our region.',
                'I oppose this legislation because it lacks sufficient oversight mechanisms and accountability measures.',
                'The funding allocation priorities in this bill do not address the most pressing needs of our constituents.',
            ],
            'mixed' => [
                'While I support the bill\'s goals for education funding, I have concerns about the implementation timeline and resource allocation.',
                'The environmental provisions are strong, but the economic impact section needs more detailed analysis.',
                'I appreciate the intent behind this legislation but believe several amendments are needed before it can be fully effective.',
            ],
            'undecided' => [
                'I need more information about the long-term fiscal impact before taking a position on this legislation.',
                'The bill has both positive and negative aspects that require further consideration and community input.',
            ],
            'needs_more_info' => [
                'The technical details of this bill are complex and I need more time to review the full text and expert analyses.',
                'I am waiting for additional clarity on how this legislation would be implemented in our state.',
            ],
        ];

        return $reasons[$stance][array_rand($reasons[$stance])];
    }
}
