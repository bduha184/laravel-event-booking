<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class EventService
{
    public static function countEventDuplication($eventDate, $startTime, $endTime)
    {

        return DB::table('events')
        ->whereDate('start_date', $eventDate)
        ->whereTime('end_date', '>', $startTime)
        ->whereTime('start_date', '<', $endTime)
        ->count();


    }
    public static function joinDateAndTime($date, $time) {
        $join = $date . "" . $time;
        $dataTime = Carbon::createFromFormat('Y-m-d H:i', $join);

        return $dataTime;
    }
}
