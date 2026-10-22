<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SysUser;
use App\Services\InitiatorFinancialService;

class TestInitiatorFinance extends Command
{
    /**
     * @var string
     */
    protected $signature = 'test:initiator-finance {userId}';

    /**
     * @var string
     */
    protected $description = 'Verify financial calculation logic for a specific initiator';

    /**
     * Execute the console command.
     */
    public function handle(InitiatorFinancialService $financialService)
    {
        $userId = $this->argument('userId');
        $user = SysUser::find($userId);

        if (!$user) {
            $this->error("User with ID $userId not found.");
            return 1;
        }

        if (!$user->isPaidInitiator()) {
            $this->error("User is not a Paid Initiator (Role: " . ($user->role->rolename ?? 'N/A') . ", Type: " . $user->financial_type . ").");
            return 1;
        }

        $this->info("--------------------------------------------------");
        $this->info("Testing Financial Calculation for: " . $user->fname . " " . $user->lname);
        $this->info("User ID: " . $user->sysuser_id);
        $this->info("--------------------------------------------------");

        $earnings = $financialService->calculateEarnings($user);

        $this->table(
            ['Category', 'Count/Basis', 'Earnings'],
            [
                ['Schools', ($earnings['breakdown']['schools']['count'] ?? 0) . ' Assigned', '₹' . number_format($earnings['breakdown']['schools']['earnings'] ?? 0, 2)],
                ['Students', ($earnings['breakdown']['students']['count'] ?? 0) . ' Registered', '₹' . number_format($earnings['breakdown']['students']['earnings'] ?? 0, 2)],
                ['Incentive', ($earnings['breakdown']['incentive']['percentage'] ?? 0) . '% of Rev', '₹' . number_format($earnings['breakdown']['incentive']['earnings'] ?? 0, 2)],
                ['Plan Based', ($earnings['breakdown']['plan']['percentage'] ?? 0) . '% of Rev', '₹' . number_format($earnings['breakdown']['plan']['earnings'] ?? 0, 2)],
                ['Performance Slabs', 'Fixed + Slab %', '₹' . number_format($earnings['breakdown']['slabs']['earnings'] ?? 0, 2)],
            ]
        );

        $this->info("--------------------------------------------------");
        $this->comment("Total Revenue Found: ₹" . number_format($earnings['total_revenue'] ?? 0, 2));
        $this->warn("TOTAL PAYOUT CALCULATION: ₹" . number_format($earnings['total'], 2));
        $this->info("--------------------------------------------------");

        return 0;
    }
}
