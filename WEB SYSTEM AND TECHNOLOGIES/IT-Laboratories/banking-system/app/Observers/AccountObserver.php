<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Transaction;
use App\Notifications\LowBalanceNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

class AccountObserver
{
    public function created(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $account = Account::whereKey($transaction->account_id)
                ->lockForUpdate()
                ->firstOrFail();

            $amount = (float) $transaction->amount;

            if (!is_finite($amount) || $amount <= 0) {
                throw new RuntimeException(
                    'Transaction amount must be positive.'
                );
            }

            if ($transaction->type === 'deposit') {
                $account->balance =
                    (float) $account->balance + $amount;
            } elseif (
                in_array(
                    $transaction->type,
                    ['withdrawal', 'transfer'],
                    true
                )
            ) {
                $newBalance =
                    (float) $account->balance - $amount;

                if (
                    $newBalance < 0
                    || $newBalance < (float) $account->minimum_balance
                ) {
                    throw new RuntimeException(
                        'Insufficient funds. The minimum balance must be maintained.'
                    );
                }

                $account->balance = $newBalance;
            } else {
                throw new RuntimeException(
                    'Unsupported transaction type.'
                );
            }

            $account->save();

            if (
                $transaction->type === 'withdrawal'
                && (float) $account->balance
                    <= (float) $account->minimum_balance
            ) {
                $accountId = $account->id;

                DB::afterCommit(function () use ($accountId) {
                    $freshAccount = Account::with('user')
                        ->find($accountId);

                    if (!$freshAccount || !$freshAccount->user) {
                        return;
                    }

                    $user = $freshAccount->user;

                    $alreadyNotified = $user->notifications()
                        ->where(
                            'type',
                            LowBalanceNotification::class
                        )
                        ->whereNull('read_at')
                        ->where(
                            'data->account_id',
                            $freshAccount->id
                        )
                        ->exists();

                    if (!$alreadyNotified) {
                        Notification::send(
                            $user,
                            new LowBalanceNotification($freshAccount)
                        );
                    }
                });
            }
        });
    }
}
