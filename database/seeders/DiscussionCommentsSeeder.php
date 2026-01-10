<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Comment;
use App\Models\Discussion;
use App\Models\User;
use Illuminate\Database\Seeder;

class DiscussionCommentsSeeder extends Seeder
{
    /**
     * Discussion sections
     */
    protected $sections = [
        'key_questions',
        'arguments_for',
        'arguments_against',
        'impact_analysis',
        'general',
    ];

    /**
     * Comment templates by section type
     */
    protected $commentTemplates = [
        'key_questions' => [
            'Has anyone found information on how this bill would affect {topic}? I have been trying to understand the implementation timeline.',
            'What is the estimated cost to taxpayers for the {topic} provisions? The CBO score seems unclear on this point.',
            'Can someone explain how this interacts with existing {topic} laws? I am concerned about potential conflicts.',
            'Who are the main stakeholders pushing for or against the {topic} aspects? Following the money often helps understand motivation.',
            'What happens if states choose not to implement the {topic} requirements? Are there enforcement mechanisms?',
            'Has similar {topic} legislation been tried before? What were the outcomes?',
            'I am curious about the sunset provisions - does this bill have an expiration date for the {topic} programs?',
            'What amendments have been proposed to the {topic} sections? Any bipartisan modifications?',
        ],
        'arguments_for' => [
            'This bill addresses a real gap in {topic} policy. We have needed federal action on this for years, and the approach here is pragmatic.',
            'The {topic} provisions are well-researched and based on successful state-level programs. Scaling this nationally makes sense.',
            'I support this because it takes a balanced approach to {topic}. It is not perfect, but it moves us forward without overreaching.',
            'The economic analysis shows positive returns on the {topic} investment. This is fiscal responsibility, not just spending.',
            'As someone who works in {topic}, I can confirm this bill addresses real problems we face daily. The provisions are practical.',
            'This represents compromise - neither side got everything they wanted on {topic}, but that is how good legislation works.',
            'The accountability measures in the {topic} sections are strong. This is not a blank check - there is real oversight built in.',
            'Compared to alternative proposals, this approach to {topic} is more likely to actually get implemented effectively.',
        ],
        'arguments_against' => [
            'The {topic} provisions create an unfunded mandate that will burden state budgets. Where is the money supposed to come from?',
            'This bill sounds good on paper, but the {topic} implementation is completely unrealistic given current infrastructure.',
            'I am concerned about the precedent this sets for federal involvement in {topic}. This should remain a state issue.',
            'The {topic} measures will hurt small businesses disproportionately. Large corporations can absorb these costs; Main Street cannot.',
            'There is no evidence that this approach to {topic} actually works. We are legislating based on ideology, not outcomes.',
            'The {topic} provisions have unintended consequences that were not adequately considered in committee.',
            'This is a band-aid solution to {topic} that does not address root causes. We will be back here in five years with the same problems.',
            'The regulatory burden created by the {topic} sections will cost more to implement than any benefits we will see.',
        ],
        'impact_analysis' => [
            'Looking at the CBO analysis, the {topic} provisions are projected to cost billions over 10 years. Worth noting the uncertainty range.',
            'State-level data from similar {topic} programs suggests we can expect about a 15-20 percent improvement in targeted outcomes.',
            'The job impact estimates for {topic} vary widely - industry says losses, economists say gains. The truth is probably in between.',
            'Rural communities will be affected differently by the {topic} measures than urban areas. Implementation needs to account for this.',
            'Based on my analysis of the {topic} provisions, the primary beneficiaries would be middle-income households in suburban districts.',
            'The environmental impact of the {topic} sections is significant - this could meaningfully reduce emissions according to EPA projections.',
            'Healthcare cost projections for {topic} suggest savings in the long term, but short-term costs will be higher during transition.',
            'The {topic} provisions will likely create thousands of jobs in implementation, though some existing jobs may be displaced.',
        ],
        'general' => [
            'I have been following this progress through committee. The {topic} sections were significantly amended from the original version.',
            'Does anyone know when the floor vote is scheduled? I want to contact my representative before they vote on {topic}.',
            'Interesting to see bipartisan support on the {topic} provisions. That is rare these days and gives me some hope.',
            'I attended a town hall where my rep discussed this bill. Their take on {topic} was surprisingly nuanced.',
            'For those wanting to dig deeper into {topic}, the committee report has good background information starting on page 47.',
            'The media coverage of this bill has been frustrating - they are missing the real story on {topic} entirely.',
            'Reminder that we can track amendments and votes on Congress.gov. The {topic} sections have had several proposed changes.',
            'Whatever your position on {topic}, I appreciate that this discussion has been civil and substantive. Democracy in action.',
        ],
    ];

    /**
     * Reply templates (responses to comments)
     */
    protected $replyTemplates = [
        'That is a good point about {topic}. I had not considered that angle.',
        'I respectfully disagree - the data on {topic} actually suggests the opposite conclusion.',
        'Building on your point about {topic}, I would add that implementation will be key to success.',
        'The source I found on {topic} supports what you are saying. Here is another perspective to consider.',
        'I think you are oversimplifying the {topic} issue. It is more nuanced than that.',
        'Great question about {topic}. From what I understand, the bill addresses this in Section 4.',
        'This is exactly the kind of substantive {topic} discussion we need more of.',
        'I was skeptical at first, but your argument about {topic} is persuasive.',
        'You raise valid concerns about {topic}, but I think the benefits still outweigh the costs.',
        'Has anyone from the {topic} industry weighed in on this? Their perspective would be valuable.',
        'The {topic} point is well-taken. I have updated my stance based on this discussion.',
        'I appreciate the civil tone here. We can disagree about {topic} without being disagreeable.',
    ];

    /**
     * Topics to substitute
     */
    protected $topics = [
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
        $this->command->info('Creating discussions and comments for all bills...');

        $users = User::whereNotNull('zip_code')->get();
        $bills = Bill::all();

        if ($users->isEmpty()) {
            $this->command->error('No users found. Run VerifiedUsersSeeder first.');
            return;
        }

        if ($bills->isEmpty()) {
            $this->command->error('No bills found. Sync bills first.');
            return;
        }

        $this->command->info("Found {$users->count()} users and {$bills->count()} bills");

        $discussionsCreated = 0;
        $commentsCreated = 0;

        foreach ($bills as $billIndex => $bill) {
            // Create discussions for each section
            foreach ($this->sections as $section) {
                $discussion = Discussion::firstOrCreate(
                    [
                        'bill_id' => $bill->id,
                        'section' => $section,
                    ],
                    [
                        'comment_count' => 0,
                        'last_activity_at' => now()->subDays(rand(0, 30)),
                    ]
                );

                if ($discussion->wasRecentlyCreated) {
                    $discussionsCreated++;
                }

                // Create 3-8 top-level comments per discussion
                $numComments = rand(3, 8);
                $commentUsers = $users->shuffle()->take($numComments);

                foreach ($commentUsers as $user) {
                    $comment = $this->createComment($discussion, $user, $section, $bill);
                    $commentsCreated++;

                    // 50% chance of having 1-3 replies
                    if (rand(0, 100) > 50) {
                        $numReplies = rand(1, 3);
                        $replyUsers = $users->shuffle()->take($numReplies);

                        foreach ($replyUsers as $replyUser) {
                            // Don't reply to yourself
                            if ($replyUser->id === $user->id) {
                                continue;
                            }

                            $this->createReply($discussion, $replyUser, $comment, $bill);
                            $commentsCreated++;
                        }
                    }
                }

                // Update discussion comment count
                $discussion->update([
                    'comment_count' => $discussion->comments()->count(),
                ]);
            }

            if (($billIndex + 1) % 10 === 0) {
                $this->command->info("Processed " . ($billIndex + 1) . " bills...");
            }
        }

        $this->command->info("✓ Created {$discussionsCreated} discussions and {$commentsCreated} comments!");
        $this->showStats();
    }

    /**
     * Create a top-level comment
     */
    protected function createComment(Discussion $discussion, User $user, string $section, Bill $bill): Comment
    {
        $templates = $this->commentTemplates[$section];
        $template = $templates[array_rand($templates)];
        $content = $this->substituteTopics($template);

        return Comment::create([
            'discussion_id' => $discussion->id,
            'user_id' => $user->id,
            'parent_id' => null,
            'content' => $content,
            'bill_version_id' => $bill->latestVersion()?->id,
            'depth' => 0,
            'helpful_count' => rand(0, 15),
            'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            'updated_at' => now()->subDays(rand(0, 30)),
        ]);
    }

    /**
     * Create a reply to a comment
     */
    protected function createReply(Discussion $discussion, User $user, Comment $parent, Bill $bill): Comment
    {
        $template = $this->replyTemplates[array_rand($this->replyTemplates)];
        $content = $this->substituteTopics($template);

        return Comment::create([
            'discussion_id' => $discussion->id,
            'user_id' => $user->id,
            'parent_id' => $parent->id,
            'content' => $content,
            'bill_version_id' => $bill->latestVersion()?->id,
            'depth' => $parent->depth + 1,
            'helpful_count' => rand(0, 8),
            'created_at' => $parent->created_at->addHours(rand(1, 48)),
            'updated_at' => $parent->created_at->addHours(rand(1, 48)),
        ]);
    }

    /**
     * Substitute topic placeholders
     */
    protected function substituteTopics(string $template): string
    {
        $topic = $this->topics[array_rand($this->topics)];
        return str_replace('{topic}', $topic, $template);
    }

    /**
     * Show distribution stats
     */
    protected function showStats(): void
    {
        $this->command->newLine();
        $this->command->info('Comments by Section:');

        foreach ($this->sections as $section) {
            $count = Comment::whereHas('discussion', function ($q) use ($section) {
                $q->where('section', $section);
            })->count();

            $label = ucwords(str_replace('_', ' ', $section));
            $this->command->line("  {$label}: {$count}");
        }

        $this->command->newLine();
        $avgPerBill = round(Comment::count() / max(Bill::count(), 1), 1);
        $this->command->info("Average comments per bill: {$avgPerBill}");
    }
}
