<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonthlyTaskCopy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monthly:task_copy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repeated task copy every month';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tickets = Ticket::where('is_repeat',1)
            ->get();
        $addname = "(".date('My').")";
//        $newEntry = [];
        foreach ($tickets as $i => $ticket){
            $newRecord = $ticket->replicate();
            $newRecord->name = $ticket->name." ".$addname;
            $newRecord->is_repeat = 0;
            $newRecord->rating = null;
            $newRecord->review_comment = '';
            $newRecord->status_id = 1;
            $newRecord->target_date = $ticket->target_date->addMonth();
            $newRecord->reviewer_target_date = $ticket->reviewer_target_date->addMonth();
            $newRecord->created_at = Carbon::now()->format('Y-m-d H:i:s');
            Log::debug(json_encode($newRecord));
            $newRecord->save();
            if ($i > 0 && $i % 10 == 0) {
                sleep(2); // sleep for 2 sec
            }
//            $newEntry[] = $newRecord;
        }
//        Ticket::insert([$newEntry]);
//        dd($newEntry);

        return Command::SUCCESS;
    }
}
