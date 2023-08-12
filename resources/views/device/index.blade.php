@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <x-buttons.back urlAddition="#settingsDevice" />
    <section id="device-description" class="text-center" style="padding: 2rem 3rem 1rem 3rem;">
        <i class="fa-solid fa-computer" style="font-size: 4rem"></i>
        <h1>Geräte </h1>
        <p> Liste aller Geräte mit den du dich jemals eingeloggt hast </p>
    </section>
    <section id="device-list">
        @forelse ($devices as $device)
            <x-listItem.item-device :device="$device" />
        @empty
            <div class="row">
                Keine Geräte gefunden
            </div>
        @endforelse
    </section>
</div>
@endsection
