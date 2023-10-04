<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\User;


class UserRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $registeredUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(object $notifiable, User $registeredUser)
    {
        $this->afterCommit();
        $this->registeredUser = $registeredUser;
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
        return (new MailMessage)
                    ->priority(2)
                    ->subject('A new user has just registered')
                    ->line($this->registeredUser->full_name . ' has just registered.')
                    ->action('BlubberLounge Tools', route('invitation.index'))
                    ->line('<img border=0 width=1 alt="" height=1 src="'. route('mail-tracker.t', $this->id) .'" />');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->registeredUser->full_name .' just registered',
            'level' => 2
        ];
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return true;
    }
}
