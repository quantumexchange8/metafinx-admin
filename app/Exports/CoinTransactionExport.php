<?php

namespace App\Exports;

use App\Models\CoinPayment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;

class CoinTransactionExport implements FromCollection
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
            // dd($record->from_coin->address);
            $result[] = array(
                'name' => $record->user->name,
                'wallet' => $record->from_wallet->name ?? ($record->from_coin ? $record->from_coin->address : null),
                'transaction_id' => $record->transaction_number,
                'date' => Carbon::parse($record->created_at)->format('Y-m-d'),
                'amount' => number_format((float)$record->amount, 2, '.', ''),
                'unit' =>  number_format((float)$record->unit, 2, '.', ''),
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Wallet',
            'Transaction ID',
            'Date',
            'Paid',
            'Amount (Unit)',
        ];
    }
}
