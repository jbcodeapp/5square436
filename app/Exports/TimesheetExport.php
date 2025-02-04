<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketHour;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;

class TimesheetExport implements FromCollection, WithHeadings, WithPreCalculateFormulas
{
    protected array $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function headings(): array
    {
        return [
            '#',
            'Ticket',
            'Project',
            'Rating',
            'Review',
            'Responsible',
            'Responsible Estimation',
            'Responsible Actual',
            'Responsible TargetDate',
            'Reviewer',
            'Reviewer Estimation',
            'Reviewer Actual',
            'Reviewer TargetDate',
            'Status',
        ];
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $collection = collect();

//        $hours = TicketHour::where('user_id', auth()->user()->id)
//            ->whereBetween('created_at', [$this->params['start_date'], $this->params['end_date']])
//            ->get();

        $query = Ticket::query();

        $query->when($this->params['status_id'] != null, function ($q) {
            if($this->params['status_id'] == '' || $this->params['status_id'] == 0){
                return $q->whereIn('status_id', '=', [1,2,3,4,5]);
            }
            return $q->where('status_id', '=', $this->params['status_id']);
        });
        $query->when($this->params['user_id'] != null, function ($q) {
            return $q->where('responsible_id', '=', $this->params['user_id']);
        });
        $query->when($this->params['project_id'] != null, function ($q) {
            return $q->where('project_id', '=', $this->params['project_id']);
        });
        $query->whereBetween('created_at', [$this->params['start_date'], $this->params['end_date']]);
        $hours = $query->get();

        $my_rating = [];
        foreach ($hours as $item) {
            $my_rating[] = $item->rating;
            $collection->push([
                '#' => $item->code,
                'ticket' => $item->name,
                'project' => $item->project->name,
//                'details' => $item->content,

                // review
                'rating' => ($item->rating) ? $item->rating.' Star' : 0,
                'review' => $item->review_comment,

                // respoinsible
                'responsible' => $item->responsible->name,
                'responsibleEstimation(hr)' => number_format($item->estimation, 2, '.', ','),
                'responsibleActualTime(hr)' => $item->totalLoggedHours,
                'responsibleTargetDate' => $item->target_date->format(__('Y-m-d')),

                //review
                'reviewer' => $item->owner->name,
                'reviewerEstimation(hr)' => $item->reviewer_estimation,
                'reviewerActualTime(hr)' => $item->totalReviewerLoggedHours,
                'reviewerTargetDate' => $item->reviewer_target_date->format(__('Y-m-d')),

                'status' => $item->status->name,

//                'user' => $item->user->name,
//                'time' => $item->forHumans,
//                'hours' => number_format($item->value, 2, ',', ' '),
//                'activity' => $item->activity ? $item->activity->name : '-',
//                'date' => $item->created_at->format(__('Y-m-d g:i A')),
            ]);
        }

        $avg = (array_sum($my_rating) == 0 || count($hours)== 0)
            ? 0
            : number_format(array_sum($my_rating)/count($hours),2);

        $extendedArr = [
            $collection,
            [' ',' ','Average rating:', $avg]
        ];

        return new Collection($extendedArr);
       // return $collection;
    }
}
