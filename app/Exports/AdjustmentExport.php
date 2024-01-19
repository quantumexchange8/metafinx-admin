<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdjustmentExport implements FromCollection, WithHeadings
{
    private $query;
    private $type;

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

        if ($this->type == 'WalletAdjustment' || 'AssetAdjustment') {
            foreach($records as $record){
                
                $result[] = array(
                    'name' => $record->user->name,
                    'email' => $record->user->email,
                    'date' => Carbon::parse($record->created_at)->format('Y-m-d'),
                    'type' => $record->transaction_type,
                    // 'before_amount' => number_format((float)$record->old_amount, 2, '.', ''),
                    'amount' => number_format((float)$record->transaction_amount, 2, '.', ''),
                    'wallet_amount' => number_format((float)$record->new_wallet_amount, 2, '.', ''),
                    'coin_amount' => number_format((float)$record->new_coin_amount, 2, '.', ''),
                    'Remark' => $record->description,
                    );
            }
        }
        return collect($result);
    }

    public function headings(): array
    {

        if ($this->type == 'WalletAdjustment' || 'AssetAdjustment') {
            return [
                'Name',
                'Email',
                'Date',
                'Type',
                'Adjust Amount',
                'Wallet Amount',
                'Coin Adjustment',
                'Remark',
            ];
        }
    }
}
