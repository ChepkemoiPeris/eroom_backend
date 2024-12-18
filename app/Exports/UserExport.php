<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Fetch statistics like total users, active users, etc.
        return User::select('id', 'email', 'created_at')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Email',
            'Created At',
        ];
    }
}
