<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // Status changes an owner may make through a save; every other change goes through approve().
    protected function isOwnerStatusTransition(?string $from, ?string $to): bool
    {
        $allowed = [
            'Draft Target'         => ['Target Submitted'],
            'Target Approved'      => ['Draft Accomplishment', 'Accomplishment Submitted'],
            'Draft Accomplishment' => ['Accomplishment Submitted'],
        ];

        return in_array($to, $allowed[$from] ?? [], true);
    }
}
