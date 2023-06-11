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

        $today = Carbon::today();

        $reservedPeople = DB::table('reservations')
        ->select('event_id',DB::raw('sum(number_of_people) as number_of_people'))
        ->whereNull('canceled_date')
        ->groupBy('event_id');

        $events = DB::table('events')
            ->leftJoinSub($reservedPeople,'reservedPeople',
            function($join){
                $join->on('events.id','=','reservedPeople.event_id');
            })
            ->whereDate('start_date','>',$today)
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

        $count = EventService::countEventDuplication(
            $request['event_date'],
            $request['start_time'],
            $request['end_time']
        );

        if($count > 0) {
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

        $users = $event->users;

        $reservations = [];

        foreach($users as $user){
            $reservedInfo = [
                'name'=>$user->name,
                'number_of_people'=>$user->pivot->number_of_people,
                'canceled_date'=>$user->pivot->canceled_date,
            ];

            array_push($reservations,$reservedInfo);
        }

        $eventDate = $event->event_date;
        $startTime = $event->start_time;
        $endTime = $event->end_time;
        return view('manager.events.show',
            compact('event','eventDate','startTime','endTime','users','reservations')
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {

        $event = Event::findOrFail($event->id);
        $today = Carbon::today()->format('Y年m月d日');

        if($event->eventDate < $today) return abort(404);

        $eventDate = $event->editEventDate;
        $startTime = $event->startTime;
        $endTime = $event->endTime;
        return view('manager.events.edit',
            compact('event','eventDate','startTime','endTime')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $count = EventService::countEventDuplication(
            $request['event_date'],
            $request['start_time'],
            $request['end_time']
        );

        if($count > 1) {
            $event = Event::findOrFail($event->id);
            $eventDate = $event->editEventDate;
            $startTime = $event->startTime;
            $endTime = $event->endTime;
            session()->flash('status','この時間帯は、すでに他のイベントで予約されております');
            return view('manager.events.edit',compact(
                'event','eventDate','startTime','endTime'
            ));
        }

        $startDate = EventService::joinDateAndTime($request['event_date'],$request['start_time']);
        $endDate = EventService::joinDateAndTime($request['event_date'],$request['end_time']);

        $event = Event::findOrFail($event->id);
        $event->fill($request->all());
        $event->start_date = $startDate;
        $event->end_date = $endDate;
        $event->save();

        session()->flash('status','更新OKです');
        return to_route('events.index');

    }


    public function past() {

        $today = Carbon::today();

        $reservedPeople = DB::table('reservations')
        ->select('event_id',DB::raw('sum(number_of_people) as number_of_people'))
        ->whereNull('canceled_date')
        ->groupBy('event_id');

        $events = DB::table('events')
        ->leftJoinSub($reservedPeople,'reservedPeople',
            function($join){
                $join->on('events.id','=','reservedPeople.event_id');
            })
        ->whereDate('start_date','<',$today)
        ->orderBy('start_date','desc')
        ->paginate(10);

        return view('manager.events.past',compact('events'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        //
    }
}
