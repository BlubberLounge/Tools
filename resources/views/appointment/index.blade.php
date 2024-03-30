@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/events.js') }}"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script defer>
        document.addEventListener('DOMContentLoaded', function() {
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'dayGridMonth', // dayGridWeek
                themeSystem: 'bootstrap5',
                locale: 'de',
                firstDay: 1,
                weekNumbers: true,
                navLinks: true,
                dayMaxEvents: true,
                nowIndicator: true,
                slotMinTime: '10:00:00',
                slotMaxTime: '23:59:59',
                eventColor: '#ffb800',
                businessHours: {
                    daysOfWeek: [ 0, 1, 2, 3, 4, 5, 6 ],
                    startTime: '16:00:00',
                    endTime: '23:59:59',
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,dayGridMonth,multiMonthYear'
                },
                events: '/api/v1/appointment',
                eventDidMount: function(info) {
                    console.log(info.event.extendedProps);
                    // {description: "Lecture", department: "BioChemistry"}
                },
                eventClick: function(info) {
                    alert('Event: ' + info.event.title);
                    alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
                    alert('View: ' + info.view.type);

                    // change the border color just for fun
                    info.el.style.borderColor = 'red';
                }
            });

            calendar.render();
        });

    </script>
@endpush

@push('styles')

@endpush

@section('content')
<div class="container">
    <div id="section-timetable" class="container-timetable" style="max-width: 100vw; overflow-x: scroll;">
        <table id="timetable" class="timetable pt-2">
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th class="border-end border-0 border-3" colspan="{{ $timeTable['monthCut'] }}"> {{ now()->format('F') }} </th>
                    <th colspan="{{ 30-$timeTable['monthCut'] }}"> {{ now()->addMonth()->format('F') }} </th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    @foreach ($timeTable['head'] as $date)
                        <th> <span class="day">{{ $date->format('d') }}</span> {{ $date->format('D') }} </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($timeTable['body'] as $i => $dat)
                    <tr @class(['timetable-me' => $dat['user']->id === Auth::user()->id]) data-bl-timetable-user-id="{{ $dat['user']->id }}">
                        <td class="detectSticky"></td>
                        <td class="timeTableUser">{{ $dat['user']->id === Auth::user()->id ? '(Ich)' : (strlen($dat['user']->name) >= 10 ? substr($dat['user']->name, 0, 10).'...' : $dat['user']->name) }}</td>
                        @foreach ($dat['days'] as $day)
                            @if($dat['user']->id === Auth::user()->id)
                                <td data-bl-timetable-status="{{ $day->status }}" data-bl-timetable-date="{{ $day->date }}" data-bs-toggle="popover" data-bs-placement="top"></td>
                            @else
                                <td data-bl-timetable-status="{{ $day->status }}"></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach

                <tr>
                    <td></td>
                    <td class="timeTableUser"> Results </td>
                    @foreach ($timeTable['result'] as $result)
                        <td> {{ $result }} </td>
                    @endforeach
                </tr>
                <tr style="font-size: 1.2rem">
                    <td> </td>
                    <td> </td>
                    @foreach ($timeTable['result'] as $key => $result)
                        <td>
                            @if ($result >= ceil(count($timeTable['body']) / 2) && end($timeTable['body'])['days'][$key]->status == App\Enums\TimetableStatus::AVAILABLE)
                                <i class="fa-solid fa-calendar-plus"></i>
                            @endif
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    <div id='calendar'></div>
</div>
@endsection
