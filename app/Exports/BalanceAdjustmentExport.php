<?php

namespace App\Exports;

use App\Models\BalanceAdjustment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BalanceAdjustmentExport implements FromCollection, WithHeadings
{
    private $query;

    public function __construct($query, $type)
    {
        $this->query = $query;
        $this->type = $type;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        $records = $this->query->get();
        $result = array();

        if ($this->type == 'WalletAdjustment') {
            foreach($records as $record){
                $result[] = array(
                    'name' => $record->user->name,
                    'email' => $record->user->email,
                    'date' => Carbon::parse($record->created_at)->format('Y-m-d'),
                    'before_adjustment' =>  number_format((float)$record->old_balance, 2, '.', ''),
                    'amount' =>  number_format((float)$record->amount, 2, '.', ''),
                    'after_adjustment' =>  number_format((float)$record->new_balance, 2, '.', ''),
                    'status' => $record->description,
                );
            }
        }
        elseif ($this->type == 'InternalTransfer') {
            foreach($records as $record){
                $result[] = array(
                    'name' => $record->user->name,
                    'email' => $record->user->email,
                    'date' => Carbon::parse($record->created_at)->format('Y-m-d'),
                    'to_name' => $record->to_user->name,
                    'old_balance' =>  number_format((float)$record->old_balance, 2, '.', ''),
                    'amount' =>  number_format((float)$record->amount, 2, '.', ''),
                    'current_balance' =>  number_format((float)$record->new_balance, 2, '.', ''),
                    'status' => $record->description,
                );
            }
        }

        return collect($result);
    }

    public function headings(): array
    {

        if ($this->type == 'WalletAdjustment') {
            return [
                'Name',
                'Email',
                'Date',
                'Before Adjustment',
                'Adjust Amount',
                'After Adjustment',
                'Remark',
            ];
        }
        elseif ($this->type == 'InternalTransfer') {
            return [
                'Name',
                'Email',
                'Date',
                'Transfer To',
                'Old Balance',
                'Transfer Amount',
                'Current Balance',
                'Remark',
            ];
        }
    
    }
}
