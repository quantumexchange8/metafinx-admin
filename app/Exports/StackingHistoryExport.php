<?php

namespace App\Exports;

use App\Models\InvestmentSubscription;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;

class StackingHistoryExport implements FromCollection
{
    private $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): \Illuminate\Support\Collection
    {
        $records = $this->query->get();
        $result = array();
        foreach($records as $record){
            $result[] = array(
                'name' => $record->user->name,
                'email' => $record->user->email,
                'subscription_id' => $record->subscription_number,
                'investment_plan' => $record->investment_plan->name,
                'date' => Carbon::parse($record->created_at)->format('Y-m-d'),
                'expired_date' => $record->expired_date,
                'amount' =>  number_format((float)$record->amount, 2, '.', ''),
                'status' => $record->status,
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'ID Number',
            'Investment Plan',
            'Subscription Date',
            'Expired Date',
            'Amount',
            'Status',
        ];
    }
}
