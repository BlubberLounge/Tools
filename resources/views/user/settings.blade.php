@extends('layouts.app')

@push('scripts')
    {{-- <script src="{{ mix('js/home.js') }}" defer></script> --}}
@endpush

@section('content')
<div class="container p-0 px-sm-5">
    <div class="sticky-top pt-3">
        <nav id="navbar-spy" class="nav nav-pills flex-row bg-body-tertiary rounded">
            <a href="#settingsNotifications" class="flex-sm-fill text-sm-center nav-link">
                Benachrichtigungen
            </a>
            <a href="#settingsPrivacyAndSecurity" class="flex-sm-fill text-sm-center nav-link">
                Privatsphäre & Sicherheit
            </a>
            <a href="#settingsLanguage" class="flex-sm-fill text-sm-center nav-link">
                Sprache
            </a>
            <a href="#settingsAccount" class="flex-sm-fill text-sm-center nav-link active">
                Konto
            </a>
        </nav>
    </div>

    <div class="px-4 mt-5" data-bs-spy="scroll" data-bs-target="#navbar-spy" data-bs-root-margin="0px 0px -40%" data-bs-smooth-scroll="true">
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
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Allgemeine Benachrichtigungsenstellung #1 </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            blabla
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Allgemeine Benachrichtigungsenstellung #2 </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col p-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Allgemeine Benachrichtigungsenstellung #3 </label>
                        </div>
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
                            <label class="form-check-label" for="flexSwitchCheckDefault"> Dartspiel einladungen </label>
                        </div>
                        <p class="form-switch-description text-secondary">
                            blabla
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
                            Du erhälst am ende eines Spieles einen kurzen Spielbericht.
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
                            Du erhälst am ende jeder Woche eine Zusammenfassung deiner Dartspiele.
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
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                        <label class="form-check-label" for="flexSwitchCheckDefault"> Profilbild ist öffentlich </label>
                    </div>
                    <p class="form-switch-description text-secondary">
                        Dein Profilbild kann von jedem Nutzer gesehen werden. (Empfohlen)
                    </p>
                </div>
                <div class="row">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                        <label class="form-check-label" for="flexSwitchCheckDefault"> Dartspiel ende </label>
                    </div>
                </div>
                <div class="row">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                        <label class="form-check-label" for="flexSwitchCheckDefault"> Allgemeine Benachrichtigungsenstellung #3 </label>
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
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                        <label class="form-check-label" for="flexSwitchCheckDefault"> Allgemeine Benachrichtigungsenstellung #1 </label>
                    </div>
                </div>
                <div class="row">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                        <label class="form-check-label" for="flexSwitchCheckDefault"> Allgemeine Benachrichtigungsenstellung #2 </label>
                    </div>
                </div>
                <div class="row">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                        <label class="form-check-label" for="flexSwitchCheckDefault"> Allgemeine Benachrichtigungsenstellung #3 </label>
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
