<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Spcr;
use App\Models\SpcrEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpcrSeeder extends Seeder
{
    public function run()
    {
        // Find a user who is a division head
        $divisionHead = User::whereIn('id', function($query) {
            $query->select('head')->from('division');
        })->first();

        if (!$divisionHead) {
            $divisionHead = User::first();
        }

        // Create a sample SPCR
        $spcr = Spcr::create([
            'userid' => $divisionHead->id,
            'division_id' => $divisionHead->division,
            'year' => 2025,
            'semester' => 1,
            'division_head_id' => $divisionHead->id,
            'status' => 'Submitted',
            'core_dist' => 50,
            'support_dist' => 10,
            'strategic_dist' => 40,
        ]);

        // CORE entries
        $spcr->entries()->create([
            'category' => 'core',
            'output' => 'Provision of technical support to all departments.',
            'success_indicator' => 'All support tickets resolved within 24 hours.',
            'accountability' => 'IT Support Team, Mark Admin',
            'actual_accomplishment' => 'Resolved 500+ tickets.',
            'accomplishment_rate' => '100%',
            'quantity_rating' => 5,
            'efficiency_rating' => 4,
            'timeliness_rating' => 5,
            'average_rating' => 4.67,
            'remarks' => 'Outstanding performance by the team.'
        ]);

        // SUPPORT entries
        $spcr->entries()->create([
            'category' => 'support',
            'output' => 'Maintenance of office equipment.',
            'success_indicator' => 'Equipment downtime less than 5% per month.',
            'accountability' => 'General Services Office',
            'actual_accomplishment' => 'Maintenance performed monthly.',
            'accomplishment_rate' => '95%',
            'quantity_rating' => 4,
            'efficiency_rating' => 5,
            'timeliness_rating' => 4,
            'average_rating' => 4.33,
            'remarks' => 'One equipment had a major breakdown.'
        ]);

        // STRATEGIC entries
        $spcr->entries()->create([
            'category' => 'strategic',
            'output' => 'Digital transformation of HR records.',
            'success_indicator' => '50% of 201 files digitized.',
            'accountability' => 'HR Division, IT Team',
            'actual_accomplishment' => '40% digitized.',
            'accomplishment_rate' => '80%',
            'quantity_rating' => 3,
            'efficiency_rating' => 4,
            'timeliness_rating' => 3,
            'average_rating' => 3.33,
            'remarks' => 'Scanner breakdown delayed the process.'
        ]);

        // Final Rating Calculation
        $coreAvg = 4.67;
        $supportAvg = 4.33;
        $strategicAvg = 3.33;
        $final = ($coreAvg * 0.5) + ($supportAvg * 0.1) + ($strategicAvg * 0.4);
        
        $spcr->final_average_rating = $final;
        $spcr->final_rating = $final;
        $spcr->final_rating_adjective = 'Very Satisfactory';
        $spcr->save();
    }
}
