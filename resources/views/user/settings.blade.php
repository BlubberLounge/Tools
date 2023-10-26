@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/settings.js') }}" defer></script>
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
            <a href="#settingsDesign" class="flex-sm-fill text-sm-center nav-link justify-center disabled">
                Design
            </a>
            <a href="#settingsPresets" class="flex-sm-fill text-sm-center nav-link justify-center active">
                Voreinstellungen
            </a>
            <a href="#settingsLanguage" class="flex-sm-fill text-sm-center nav-link justify-center disabled">
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
                <x-form.settings.input-switch
                    id="receiveNewsletter"
                    label="Newsletter / Updates"
                    description="Ich möchte immer auf dem aktuellen Stand sein und benachrichtigt werden, wenn es Neuigkeiten gibt."
                />
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-3">
                Geräte
            </div>
            <div class="col-9">
                <x-form.settings.input-switch
                    id="receiveNewDeviceLogin"
                    label="Neues Gerät"
                    description="Ich möchte benachrichtigt werden, wenn ich mich mit einem neuen Gerät anmelde."
                />
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-3">
                Dart
            </div>
            <div class="col-9">
                <x-form.settings.input-switch
                    id="receiveDartGameInvitation"
                    label="Dartspiel einladung"
                    description="Ich möchte benachrichtigt werden wenn, ich zu einem Dartspiel eingeladen werde. (Empfohlen)" />

                <x-form.settings.input-switch
                    id="receiveDartGameReport"
                    label="Dartspielbericht"
                    description="Ich möchte am ende eines Spieles einen kurzen Spielbericht erhalten." />

                <x-form.settings.input-switch
                    id="receiveDartGameWeeklyReport"
                    label="Wöchentliche Spielberichte"
                    description="Ich möchte am ende jeder Woche eine Zusammenfassung meiner Dartspiele erhalten." />
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-3">
                Konto
            </div>
            <div class="col-9">
                <x-form.settings.input-switch
                    id="receiveAccountChanges"
                    label="Konto änderungen"
                    description="Ich möchte benachrichtigt werden, wenn sich etwas an meinem Konto ändert (z.b. Benutzergruppe, Rechte, etc.)." />
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
                <x-form.settings.input-switch
                    id="isProfilePicturePublic"
                    label="Profilbild ist öffentlich"
                    description="Ich möchte das jeder Nutzer mein Profilbild sehen kann. (Empfohlen)" />

                <x-form.settings.input-switch
                    id="isOnlineStatusPublic"
                    label="Online status ist öffentlich"
                    description="Ich möchte das jeder sehen kann das ich aktuell aktiv bin." />
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-3">
                Dart
            </div>
            <div class="col-9">
                <x-form.settings.input-switch
                    id="dartGameInvitation"
                    label="Dartspiel"
                    description="Ich möchte zu einem Dartspiel eingeladen werden können. (Empfohlen)" />

                <x-form.settings.input-switch
                    id="isDartGameStatisticPublic"
                    label="Dart Statistiken"
                    description="Ich möchte das jeder meine dart statistiken sehen kann. (Empfohlen)" />
            </div>
        </div>
        <hr>

        {{--

            Design

        --}}
        <h4 id="settingsDesign">
            Design
        </h4>
        <p class="text-muted mb-5">
            Festlegen, wie die Oberfläche aussehen soll
        </p>
        <div class="row mb-4">
            <div class="col-3">
                Theme
            </div>
            <div class="col-9">
                <x-form.settings.info-text
                    description="Dieses Feature ist derzeit in Entwicklung und wird in Kürze verfügbar sein. Freue dich auf aufregende Updates!" />
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-3">
                Modus
            </div>
            <div class="col-9">
                <x-form.settings.info-text
                    description="siehe Sidebar" />
            </div>
        </div>
        <hr>

        {{--

            Presets

        --}}
        <h4 id="settingsPresets">
            Voreinstellungen
        </h4>
        <p class="text-muted mb-5">
            Festlegen, von werten die standardmäßig verwendet werden.
        </p>
        <div class="row mb-4">
            <div class="col-3">
                Dart
            </div>
            <div class="col-9">
                <div class="row">
                    <div class="col">
                        <input type="text" id="dartGameTitle" class="form-control" placeholder="{{ Auth::user()->getGameTitle() }} - keine Funktion">
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
                <x-form.settings.info-text
                    description="siehe Sidebar" />
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-3">
                Email
            </div>
            <div class="col-9">
                <x-form.settings.info-text
                    description="Dieses aufregende Feature ist für die Zukunft geplant, jedoch aktuell noch nicht verfügbar.
                    Wir arbeiten hart daran, um es baldmöglichst für dich bereitzustellen. Freue dich auf zukünftige Updates, die diese Funktion integrieren werden!" />
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
                    <div class="col-auto mb-2">
                        <button type="button" class="btn btn-outline-warning"> Archiv anfordern </button>
                    </div>
                    <p class="text-secondary">
                        Noch keine Funktion. Du kannst hier ein Archiv Deiner Daten anfordern. <br />
                        Mit dem Klick auf "Archiv anfordern" stimmst du zu, dass wir Dir Deine Benutzerdaten an die Adresse <span class="text-light">{{ Auth::user()->email }}</span> zusenden.
                    </p>
                </div>
                <div class="row">
                    <div class="col-auto mb-2">
                        <button type="button" class="btn btn-outline-danger"> Konto löschen </button>
                    </div>
                    <p class="text-secondary">
                        Bist du sicher, dass du unsere großartige Plattform verlassen möchtest? <br />
                        Bedenke, dass du mit dem Löschen deines Kontos dauerhaft den Zugriff auf all die nützlichen <br /> Werkzeuge und Ressourcen verlierst, die wir bieten.
                        Du würdest damit auch die Möglichkeit <br /> einbüßen, von unseren zukünftigen Innovationen zu profitieren. <br />
                        Bitte überlege sorgfältig, bevor du diesen Schritt gehst.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
