<?php

namespace App\Notifications;

use App\Models\Bill;
use App\Models\BillEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillAmendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Bill $bill,
        public BillEvent $event,
        public string $changeType = 'amendment'
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = match($this->changeType) {
            'amendment' => "Bill Updated: {$this->bill->identifier}",
            'vote' => "Vote Scheduled: {$this->bill->identifier}",
            'status' => "Status Changed: {$this->bill->identifier}",
            default => "Update on {$this->bill->identifier}",
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line("A bill you're following has been updated:")
            ->line("**{$this->bill->short_title ?? $this->bill->title}**")
            ->line($this->event->description ?? $this->event->event_type)
            ->action('View Bill Details', route('bills.show', $this->bill))
            ->line('You can adjust your notification preferences for this bill in your profile.')
            ->salutation('— Congressional Consensus Platform');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'bill_id' => $this->bill->id,
            'bill_identifier' => $this->bill->identifier,
            'bill_title' => $this->bill->short_title ?? $this->bill->title,
            'event_id' => $this->event->id,
            'event_type' => $this->event->event_type,
            'event_description' => $this->event->description,
            'change_type' => $this->changeType,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
            //
        ];
    }
}
