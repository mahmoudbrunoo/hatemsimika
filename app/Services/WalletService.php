<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    /** شحن رصيد */
    public function credit(User $user, float $amount, string $type, ?string $note = null, ?Model $reference = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('المبلغ غير صالح');
        }

        return $this->apply($user, $amount, $type, $note, $reference);
    }

    /** خصم رصيد */
    public function debit(User $user, float $amount, string $type, ?string $note = null, ?Model $reference = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('المبلغ غير صالح');
        }

        return $this->apply($user, -$amount, $type, $note, $reference);
    }

    protected function apply(User $user, float $delta, string $type, ?string $note, ?Model $reference): WalletTransaction
    {
        return DB::transaction(function () use ($user, $delta, $type, $note, $reference) {
            $user = User::lockForUpdate()->findOrFail($user->id);
            $before = (float) $user->balance;
            $after = round($before + $delta, 2);

            if ($after < 0) {
                throw new RuntimeException('رصيد المحفظة غير كافي');
            }

            $user->update(['balance' => $after]);

            return WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $delta,
                'balance_before' => $before,
                'balance_after' => $after,
                'type' => $type,
                'note' => $note,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
            ]);
        });
    }
}
