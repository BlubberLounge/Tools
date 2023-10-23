@extends('layouts.app')

@push('scripts')
    {{-- <script src="{{ mix('js/home.js') }}" defer></script> --}}
@endpush

@section('content')
<div class="container p-0 px-sm-5">
    <div class="sticky-top pt-3">
        <nav id="navbar-spy" class="nav nav-pills flex-row bg-body-tertiary rounded">
            <a href="#settingsNotifications" class="flex-sm-fill text-sm-center nav-link justify-center active">
                Benachrichtigungen
            </a>
            <a href="#settingsPrivacyAndSecurity" class="flex-sm-fill text-sm-center nav-link justify-center">
                Privatsphäre & Sicherheit
            </a>
            <a href="#settingsLanguage" class="flex-sm-fill text-sm-center nav-link justify-center">
                Sprache
            </a>
            <a href="#settingsAccount" class="flex-sm-fill text-sm-center nav-link justify-center">
                Konto
            </a>
        </nav>
    </div>

    <div class="px-4 mt-5" data-bs-spy="scroll" data-bs-target="#navbar-spy" data-bs-root-margin="0px 0px -10%" data-bs-smooth-scroll="true">
        {{--

            Notifications

        --}}
        <h4 id="settingsNotifications">
            Benachrichtigungen
        </h4>
        <p class="text-muted mb-5">
            Festlegen, wann und wie du benachrichtigt wirst <br>
            Wähle Push- und E-Mail-Benachrichtigungen aus, die du erhalten möchtest
        </p>

        <div class="row mb-4">
            <div class="col-3">
                Allgemein
            </div>
            <div class="col-9">
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Newsletter / Updates </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            Ich möchte immer auf dem aktuellen Stand sein und benachrichtigt werden, wenn es Neuigkeiten gibt.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-3">
                Geräte
            </div>
            <div class="col-9">
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Neues Gerät </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            Ich möchte benachrichtigt werden, wenn ich mich mit einem neuen Gerät anmelde.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-3">
                Dart
            </div>
            <div class="col-9">
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Dartspiel einladung </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            Ich möchte benachrichtigt werden wenn, ich zu einem Dartspiel eingeladen werde. (Empfohlen)
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Dartspielbericht </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            Ich möchte am ende eines Spieles einen kurzen Spielbericht erhalten.
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Wöchentliche Spielberichte </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            Ich möchte am ende jeder Woche eine Zusammenfassung meiner Dartspiele erhalten.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-3">
                Konto
            </div>
            <div class="col-9">
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Konto änderungen </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            Ich möchte benachrichtigt werden, wenn sich etwas an meinem Konto ändert (z.b. Benutzergruppe, Rechte, etc.).
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        {{--

            Privacy & Security

        --}}
        <h4 id="settingsPrivacyAndSecurity">
            Privatsphäre & Sicherheit
        </h4>
        <p class="text-muted mb-5">
            Festlegen, wann und wie du benachrichtigt wirst <br>
            Wähle Push- und E-Mail-Benachrichtigungen aus, die du erhalten möchtest
        </p>
        <div class="row mb-4">
            <div class="col-3">
                Allgemein
            </div>
            <div class="col-9">
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Profilbild ist öffentlich </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            Ich möchte das jeder Nutzer mein Profilbild sehen kann. (Empfohlen)
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Online status ist öffentlich </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            Ich möchte das jeder sehen kann das ich aktuell aktiv bin.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-3">
                Dart
            </div>
            <div class="col-9">
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Dartspiel </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            Ich möchte zu einem Dartspiel eingeladen werden können. (Empfohlen)
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Dart Statistiken </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            Ich möchte das jeder meine dart statistiken sehen kann. (Empfohlen)
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        {{--

            Language

        --}}
        <h4 id="settingsLanguage">
            Sprache
        </h4>
        <p class="text-muted mb-5">
            Festlegen, der Spracheinstellungen
        </p>
        <div class="row mb-4">
            <div class="col-3">
                Allgemein
            </div>
            <div class="col-9">
                <div class="row">
                    <div class="col p-0">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Small button group">
                            <button type="button" class="btn btn-outline-primary">Left</button>
                            <button type="button" class="btn btn-outline-primary">Middle</button>
                            <button type="button" class="btn btn-outline-primary">Right</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-3">
                Email
            </div>
            <div class="col-9">
                <div class="row">
                    <div class="col p-0">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Small button group">
                            <button type="button" class="btn btn-outline-primary">Left</button>
                            <button type="button" class="btn btn-outline-primary">Middle</button>
                            <button type="button" class="btn btn-outline-primary">Right</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        {{--

            Account

        --}}
        <h4 id="settingsAccount">
            Konto
        </h4>
        <p class="text-muted mb-5">
            Festlegen, von Konto relevanten Einstellungen
        </p>
        <div class="row mb-4">
            <div class="col-3">
                Allgemein
            </div>
            <div class="col-9">
                <div class="row mb-3">
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-warning"> Archiv anfordern </button>
                    </div>
                    <p class="text-muted">
                        Noch keine Funktion. Du kannst hier ein Archiv Deiner Daten anfordern. <br />
                        Mit dem Klick auf "Archiv anfordern" stimmst du zu, dass wir Dir Deine Benutzerdaten an die Adresse <span class="text-light">{{ Auth::user()->email }}</span> zusenden.
                    </p>
                </div>
                <div class="row">
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-danger"> Konto löschen </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
