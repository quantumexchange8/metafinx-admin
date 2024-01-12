<?php

namespace App\Console\Commands;

use App\Models\Wallet;
use App\Models\WalletNet;
use Illuminate\Console\Command;

class GetDailyWalletBalanceCommand extends Command
{
    protected $signature = 'get:daily-wallet-balance';

    protected $description = 'Get daily wallet balance';

    public function handle(): void
    {
        $wallets = Wallet::all();

        foreach ($wallets as $wallet) {
            WalletNet::create([
                'user_id' => $wallet->user_id,
                'wallet_id' => $wallet->id,
                'total_balance' => $wallet->balance,
            ]);
        }
    }
}
