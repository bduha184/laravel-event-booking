<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = DB::table('events')
            ->orderBy('start_date', 'asc')
            ->paginate(10);
        return view('manager.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {


        return view('manager.events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {

        $check = EventService::checkEventDuplication(
            $request['event_date'],
            $request['start_time'],
            $request['end_time']
        );

        if($check) {
            session()->flash('status','この時間帯は、すでに他のイベントで予約されております');
            return view('manager.events.create');
        }

        $startDate = EventService::joinDateAndTime($request['event_date'],$request['start_time']);
        $endDate = EventService::joinDateAndTime($request['event_date'],$request['end_time']);

        Event::create([
            'name' => $request['event_name'],
            'information' => $request['information'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'max_people' => $request['max_people'],
            'is_visible' => $request['is_visible'],
        ]);
        session()->flash('status','登録OKです');
        return to_route('events.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {

        $event = Event::findOrFail($event->id);

        $eventDate = $event->event_date;
        $startTime = $event->start_time;
        $endTime = $event->end_time;
        return view('manager.events.show',
            compact('event','eventDate','startTime','endTime')
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        //
    }
}
