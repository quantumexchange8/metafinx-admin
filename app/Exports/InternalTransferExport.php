<?php

namespace App\Exports;

use App\Models\Payment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InternalTransferExport implements FromCollection, WithHeadings
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
        $records = $this->query->where('transaction_type', 'InternalTransfer')->get();
        $result = array();
        foreach($records as $deposits){
            
            $result[] = array(
                'name' => $deposits->user->name,
                'email' => $deposits->user->email,
                'from_wallet' => $deposits->from_wallet->name,
                'to_wallet' => $deposits->to_wallet->name,
                'transaction_id' => $deposits->transaction_number,
                'date' => Carbon::parse($deposits->created_at)->format('Y-m-d'),
                'amount' =>  number_format((float)$deposits->amount, 2, '.', ''),
                'transaction_amount' =>  number_format((float)$deposits->transaction_amount, 2, '.', ''),
                'status' => $deposits->status,
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Transfer From',
            'Transfer To',
            'Transaction ID',
            'Date',
            'Amount',
            'Transfer Amount',
            'Status',
        ];
    }
}
