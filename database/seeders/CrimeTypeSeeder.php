<?php

namespace Database\Seeders;

use App\Models\CrimeType;
use Illuminate\Database\Seeder;

class CrimeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Theft',
                'description' => 'Property theft incidents',
                'severity_level' => 'medium'
            ],
            [
                'name' => 'Assault',
                'description' => 'Physical assault cases',
                'severity_level' => 'high'
            ],
            [
                'name' => 'Vandalism',
                'description' => 'Property damage or defacement',
                'severity_level' => 'low'
            ],
            [
                'name' => 'Breaking and Entering',
                'description' => 'Forced entry into properties',
                'severity_level' => 'high'
            ]
        ];

        foreach ($types as $type) {
            CrimeType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}