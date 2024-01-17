<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MemberListingExport implements FromCollection, WithHeadings
{
    private $members;

    public function __construct($members)
    {
        $this->members = $members;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $records = $this->members->toArray();
        $result = array();
        
        foreach ($records['data'] as $record) {
            // Check if $record is an array and has the necessary properties
            $result[] = [
                'name' => $record['name'],
                'created_at' => Carbon::parse($record['created_at'])->format('Y-m-d'),
                'wallets_sum_balance' => $record['wallets_sum_balance'],
                'active_investment_amount' => $record['active_investment_amount'] ?? '0',
                'rank' => $record['rank']['name'],
                'kyc_approval' => $record['kyc_approval'],
            ];
            
        }
    
        return collect($result);
    }
    


    public function headings(): array
    {
        return [
            'Name',
            'Joining Date',
            'Wallet Balance',
            'Active Investment',
            'Rank',
            'Status',
        ];
    }
}
