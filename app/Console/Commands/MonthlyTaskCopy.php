<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
//        $newEntry = [];
        foreach ($tickets as $ticket){
            $newRecord = $ticket->replicate();
            $newRecord->is_repeat = 0;
            $newRecord->rating = null;
            $newRecord->review_comment = '';
            $newRecord->status_id = 1;
            $newRecord->target_date = $ticket->target_date->addMonth();
            $newRecord->reviewer_target_date = $ticket->reviewer_target_date->addMonth();
            $newRecord->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $newRecord->save();
//            $newEntry[] = $newRecord;
        }
//        Ticket::insert([$newEntry]);
//        dd($newEntry);

        return Command::SUCCESS;
    }
}
