<?php

namespace App\Console\Commands;

use App\Models\Offer;
use App\Models\Trade;
use App\Models\OfferHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ForceResetDatabaseOffer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:offers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Offer::where('id', '!=', 0)->delete();

        Trade::where('id', '!=', 0)->delete();

        OfferHistory::where('id', '!=', 0)->delete();

        Artisan::call('cache:clear');
    }
}
