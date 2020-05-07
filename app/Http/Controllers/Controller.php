<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $anomalyStatic = [
        'Size'  => ['V', 'L', 'M', 'S'],
        'Class' => ['Hisec', 'Lowsec', 'Nullsec', 'Deadly, C6', 'Dangerous, C4-C5', 'Unknown, C1-C3', 'Thera'],
    ];
    protected $anomalyDynamic = [
        'Mass'  => ['not yet (over 50%)', 'not critical (between 50% and 10%)', 'critical (less than 10%)'],
        'Time'  => ['not yet (24h+)', 'beginning to decay (4h-24h)', 'reaching the end (<4h)'],
    ];
}
