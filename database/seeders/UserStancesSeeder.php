<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\User;
use App\Models\UserStance;
use Illuminate\Database\Seeder;

class UserStancesSeeder extends Seeder
{
    /**
     * Stance types with equal distribution target (20% each)
     */
    protected array $stanceTypes = [
        'support',
        'oppose',
        'mixed',
        'undecided',
        'needs_more_info',
    ];

    /**
     * Sample reasons for each stance type - substantive and varied
     */
    protected array $reasonTemplates = [
        'support' => [
            "After reviewing the bill text and analyzing its potential economic impacts, I believe this legislation addresses a genuine need in our communities. The provisions for {topic} are well-structured and include appropriate safeguards. While no bill is perfect, the benefits outweigh the concerns.",
            "This bill represents a thoughtful approach to {topic}. I appreciate that it includes input from multiple stakeholders and provides clear implementation guidelines. The funding mechanisms appear sustainable and the oversight provisions are adequate.",
            "I support this legislation because it takes a balanced approach to {topic}. The bill acknowledges the complexity of the issue and provides flexibility for local implementation while maintaining federal standards. This is the kind of pragmatic policymaking we need.",
            "Having read through the full text and committee reports, I believe this bill would benefit my community. The focus on {topic} is timely and the proposed solutions are evidence-based. I encourage my representatives to vote in favor.",
            "This legislation addresses issues that directly affect families in my district. The provisions related to {topic} reflect concerns I've heard from neighbors and community members. It's not a perfect bill, but it moves us in the right direction.",
            "I support this bill based on its potential to improve {topic} in underserved communities. The allocation of resources seems fair and the accountability measures should ensure effective implementation.",
        ],
        'oppose' => [
            "While I understand the intent behind this legislation, I have significant concerns about its implementation. The provisions for {topic} lack sufficient detail and could lead to unintended consequences. I believe we need a more carefully crafted approach.",
            "After careful review, I cannot support this bill. The costs associated with {topic} provisions appear to outweigh the projected benefits, and the funding mechanisms seem unsustainable long-term. We should go back to the drawing board.",
            "I oppose this legislation because it doesn't adequately address the root causes of the problems it aims to solve. The approach to {topic} is superficial and may actually make things worse for the communities it's meant to help.",
            "This bill overreaches in its scope and could set problematic precedents. The {topic} provisions grant too much authority without sufficient checks and balances. I urge my representatives to vote no.",
            "My opposition stems from both practical and principled concerns. The {topic} measures are likely to be ineffective based on similar initiatives in other states, and the approach raises constitutional questions that haven't been adequately addressed.",
            "While the goals are laudable, this bill is not the right vehicle. The {topic} provisions are poorly written and could harm the very people they're meant to protect. Let's work on better legislation.",
        ],
        'mixed' => [
            "I have mixed feelings about this legislation. On one hand, the {topic} provisions address real needs in our communities. On the other hand, the implementation timeline seems rushed and the funding may be insufficient. I'd like to see amendments.",
            "This bill contains both promising elements and concerning ones. I appreciate the attention to {topic}, but I'm worried about the potential for uneven implementation across different regions. It's a complicated situation.",
            "I see valid arguments on both sides of this debate. The {topic} aspects of the bill are well-intentioned, but I question whether the proposed mechanisms will achieve the stated goals. More analysis is needed.",
            "My position is nuanced because the bill itself is complex. Some provisions related to {topic} are exactly what we need, while others seem like they were added without sufficient deliberation. I'd vote for it with reservations.",
            "I find myself agreeing with some aspects and disagreeing with others. The core approach to {topic} is sound, but the details need work. This is a case where the final vote will depend on any amendments that get added.",
            "This legislation represents a genuine attempt to address {topic}, and I respect that effort. However, I can't fully endorse it without modifications to the enforcement provisions and clearer definitions of key terms.",
        ],
        'undecided' => [
            "I'm still forming my opinion on this bill. The {topic} provisions raise important questions that I'm researching further. I'd like to hear more perspectives from experts and affected communities before taking a firm stance.",
            "I haven't made up my mind yet. While I initially leaned one way on the {topic} issues, recent analysis I've read has made me reconsider. I'm genuinely undecided and open to persuasion.",
            "This is a complex bill and I don't feel ready to take a position. The arguments for and against the {topic} measures both have merit. I'm waiting for more information about projected impacts.",
            "I'm undecided because I see this as a close call. The {topic} provisions could be beneficial or harmful depending on implementation. I want to see what amendments are proposed before forming a final opinion.",
            "I need more time to study this legislation. The {topic} aspects touch on issues I care about deeply, which is exactly why I want to be thorough in my analysis before committing to a stance.",
            "My position is genuinely uncertain. I've read arguments from both supporters and opponents, and I find myself persuaded by different points from each side. This is a bill that requires careful deliberation.",
        ],
        'needs_more_info' => [
            "I can't form an informed opinion without more details about how the {topic} provisions would be implemented. The bill text is vague on several key points and I'd like to see the regulatory framework before deciding.",
            "More information is needed before I can take a stance. Specifically, I want to understand the projected costs of the {topic} measures and how they compare to alternative approaches that have been proposed.",
            "I'm requesting additional information about this bill. The {topic} sections reference studies and data that I haven't been able to verify. Transparency about the evidence base would help me make a decision.",
            "Before taking a position, I need clarification on how this bill interacts with existing {topic} laws and regulations. The potential for conflicts or redundancy isn't clear from the current text.",
            "I need more context to evaluate this legislation properly. How does our approach to {topic} compare to what other countries or states have tried? What were the outcomes of those efforts?",
            "The bill summary doesn't provide enough detail for me to form an opinion. I'd specifically like more information about the {topic} enforcement mechanisms and the appeals process for affected parties.",
        ],
    ];

    /**
     * Topics to substitute into reason templates
     */
    protected array $topics = [
        'healthcare access',
        'economic development',
        'environmental protection',
        'education funding',
        'infrastructure investment',
        'tax policy',
        'public safety',
        'housing affordability',
        'workforce development',
        'technology regulation',
        'veterans services',
        'small business support',
        'agricultural policy',
        'transportation',
        'energy independence',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating evenly distributed stances across all bills...');

        $users = User::whereNotNull('zip_code')->get();
        $bills = Bill::all();

        if ($users->isEmpty()) {
            $this->command->error('No users found. Run VerifiedUsersSeeder first.');
            return;
        }

        if ($bills->isEmpty()) {
            $this->command->error('No bills found. Sync bills first with: php artisan bills:sync');
            return;
        }

        $this->command->info("Found {$users->count()} users and {$bills->count()} bills");

        $stancesCreated = 0;
        $usersArray = $users->shuffle()->values();

        foreach ($bills as $billIndex => $bill) {
            // Determine how many users will have stances on this bill (50-80% participation)
            $participationRate = rand(50, 80) / 100;
            $participatingUsers = $usersArray->take((int) ($usersArray->count() * $participationRate));

            // Shuffle stance types for even distribution
            $stanceIndex = 0;

            foreach ($participatingUsers as $user) {
                // Check if user already has a stance on this bill
                $existingStance = UserStance::where('user_id', $user->id)
                    ->where('bill_id', $bill->id)
                    ->exists();

                if ($existingStance) {
                    continue;
                }

                // Cycle through stance types for even distribution
                $stance = $this->stanceTypes[$stanceIndex % count($this->stanceTypes)];
                $stanceIndex++;

                // Generate a realistic reason
                $reason = $this->generateReason($stance);

                UserStance::create([
                    'user_id' => $user->id,
                    'bill_id' => $bill->id,
                    'stance' => $stance,
                    'reason' => $reason,
                    'zip_code' => $user->zip_code,
                    'congressional_district' => $user->congressional_district,
                    'revision' => 1,
                    'bill_version_id' => $bill->latestVersion()?->id,
                ]);

                $stancesCreated++;
            }

            if (($billIndex + 1) % 10 === 0) {
                $this->command->info("Processed " . ($billIndex + 1) . " bills...");
            }
        }

        $this->command->info("✓ Successfully created {$stancesCreated} stances across {$bills->count()} bills!");

        // Show distribution stats
        $this->showDistribution();
    }

    /**
     * Generate a realistic reason for a stance
     */
    protected function generateReason(string $stance): string
    {
        $templates = $this->reasonTemplates[$stance];
        $template = $templates[array_rand($templates)];
        $topic = $this->topics[array_rand($this->topics)];

        return str_replace('{topic}', $topic, $template);
    }

    /**
     * Show the distribution of stances
     */
    protected function showDistribution(): void
    {
        $this->command->newLine();
        $this->command->info('Stance Distribution:');

        $total = UserStance::count();

        foreach ($this->stanceTypes as $stance) {
            $count = UserStance::where('stance', $stance)->count();
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $this->command->line("  {$stance}: {$count} ({$percentage}%)");
        }
    }
}
