<?php

namespace App\Services;

use App\Models\UserTransaction;

class UserTransactionService
{
    public function createTransaction($user, $amount, $transaction_type)
    {
        return UserTransaction::create([
            'user_id' => $user->id,
            'transaction_type' => $transaction_type,
            'amount' => $amount,
        ]);
    }

    public function calculateUserTotalBalance($user)
    {
        // return $user->transactions()
        // ->selectRaw("
        //     SUM(CASE WHEN transaction_type = 'credited' THEN amount ELSE 0 END) -
        //     SUM(CASE WHEN transaction_type = 'debited' THEN amount ELSE 0 END)
        //     AS balance
        // ")
        // ->value('balance') ?? 0;
        $user_total_credited_amount = $user->transactions()
            ->where('transaction_type', 'credited')
            ->sum('amount');

        $user_total_debited_amount = $user->transactions()
            ->where('transaction_type', 'debited')
            ->sum('amount');

        return $user_total_credited_amount - $user_total_debited_amount;
    }
}
