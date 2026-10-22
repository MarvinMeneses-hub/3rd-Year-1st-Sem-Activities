<?php

namespace App\Notifications;

use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowBalanceNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Account $account
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Account Balance Alert')
            ->greeting('Hello ' . ($notifiable->name ?? 'Account Holder') . '!')
            ->line('Your bank account balance has reached its minimum balance limit.')
            ->line('Account Number: ' . $this->account->account_number)
            ->line('Current Balance: ₱' . number_format((float) $this->account->balance, 2))
            ->line('Minimum Balance: ₱' . number_format((float) $this->account->minimum_balance, 2))
            ->line('Please review your account activity before making another withdrawal.')
            ->salutation('Banking System');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'account_id' => $this->account->id,
            'account_number' => $this->account->account_number,
            'balance' => (float) $this->account->balance,
            'minimum_balance' => (float) $this->account->minimum_balance,
            'message' => 'Your account balance has reached its minimum balance limit.',
        ];
    }
}