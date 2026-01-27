@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/settings.js'])
@endpush

@section('content')
<div class="container max-w-4xl mx-auto px-4">
    <div class="sticky top-0 z-10 pt-3 pb-2 bg-[var(--tw-body-bg)]">
        <nav id="navbar-spy" class="flex flex-wrap gap-2 p-2 bg-[var(--tw-body-tertiary-bg)] rounded-lg">
            <a href="#settingsNotifications" class="flex-1 text-center px-3 py-2 text-sm rounded-md hover:bg-primary hover:text-white transition-colors">
                Benachrichtigungen
            </a>
            <a href="#settingsPrivacyAndSecurity" class="flex-1 text-center px-3 py-2 text-sm rounded-md hover:bg-primary hover:text-white transition-colors">
                Privatsphäre & Sicherheit
            </a>
            <a href="#settingsDesign" class="flex-1 text-center px-3 py-2 text-sm rounded-md opacity-50 cursor-not-allowed">
                Design
            </a>
            <a href="#settingsPresets" class="flex-1 text-center px-3 py-2 text-sm rounded-md hover:bg-primary hover:text-white transition-colors">
                Voreinstellungen
            </a>
            <a href="#settingsLanguage" class="flex-1 text-center px-3 py-2 text-sm rounded-md opacity-50 cursor-not-allowed">
                Sprache
            </a>
            <a href="#settingsAccount" class="flex-1 text-center px-3 py-2 text-sm rounded-md hover:bg-primary hover:text-white transition-colors">
                Konto
            </a>
        </nav>
    </div>

    <div class="mt-6">
        {{-- Notifications --}}
        <section class="mb-10">
            <h4 id="settingsNotifications" class="text-xl font-semibold mb-2">
                Benachrichtigungen
            </h4>
            <p class="text-[var(--tw-muted-color)] mb-6">
                Festlegen, wann und wie du benachrichtigt wirst <br>
                Wähle Push- und E-Mail-Benachrichtigungen aus, die du erhalten möchtest
            </p>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Allgemein</div>
                    <div class="md:col-span-3">
                        <x-form.settings.input-switch
                            id="receiveNewsletter"
                            label="Newsletter / Updates"
                            description="Ich möchte immer auf dem aktuellen Stand sein und benachrichtigt werden, wenn es Neuigkeiten gibt."
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Geräte</div>
                    <div class="md:col-span-3">
                        <x-form.settings.input-switch
                            id="receiveNewDeviceLogin"
                            label="Neues Gerät"
                            description="Ich möchte benachrichtigt werden, wenn ich mich mit einem neuen Gerät anmelde."
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Dart</div>
                    <div class="md:col-span-3">
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

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Konto</div>
                    <div class="md:col-span-3">
                        <x-form.settings.input-switch
                            id="receiveAccountChanges"
                            label="Konto änderungen"
                            description="Ich möchte benachrichtigt werden, wenn sich etwas an meinem Konto ändert (z.b. Benutzergruppe, Rechte, etc.)." />
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-[var(--tw-border-color)] my-8">

        {{-- Privacy & Security --}}
        <section class="mb-10">
            <h4 id="settingsPrivacyAndSecurity" class="text-xl font-semibold mb-2">
                Privatsphäre & Sicherheit
            </h4>
            <p class="text-[var(--tw-muted-color)] mb-6">
                Festlegen, wann und wie du benachrichtigt wirst <br>
                Wähle Push- und E-Mail-Benachrichtigungen aus, die du erhalten möchtest
            </p>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Allgemein</div>
                    <div class="md:col-span-3">
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

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Dart</div>
                    <div class="md:col-span-3">
                        <x-form.settings.input-switch
                            id="dartGameInvitation"
                            label="Dartspiel"
                            description="Ich möchte zu einem Dartspiel eingeladen werden können. (Empfohlen)" />

                        <x-form.settings.input-switch
                            id="dartGameInvitationAutoAccept"
                            label="Dartspiel automatisch akzeptieren"
                            description="Dartspieleinladungen werden automatisch akzeptiert, ohne dass ich eine E-Mail erhalten oder sie manuell bestätigen muss." />

                        <x-form.settings.input-switch
                            id="isDartGameStatisticPublic"
                            label="Dart Statistiken"
                            description="Ich möchte das jeder meine dart statistiken sehen kann. (Empfohlen)" />
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-[var(--tw-border-color)] my-8">

        {{-- Design --}}
        <section class="mb-10">
            <h4 id="settingsDesign" class="text-xl font-semibold mb-2">
                Design
            </h4>
            <p class="text-[var(--tw-muted-color)] mb-6">
                Festlegen, wie die Oberfläche aussehen soll
            </p>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Theme</div>
                    <div class="md:col-span-3">
                        <x-form.settings.info-text
                            description="Dieses Feature ist derzeit in Entwicklung und wird in Kürze verfügbar sein. Freue dich auf aufregende Updates!" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Modus</div>
                    <div class="md:col-span-3">
                        <x-form.settings.info-text
                            description="siehe Sidebar" />
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-[var(--tw-border-color)] my-8">

        {{-- Presets --}}
        <section class="mb-10">
            <h4 id="settingsPresets" class="text-xl font-semibold mb-2">
                Voreinstellungen
            </h4>
            <p class="text-[var(--tw-muted-color)] mb-6">
                Festlegen, von werten die standardmäßig verwendet werden.
            </p>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Dart</div>
                    <div class="md:col-span-3">
                        <input type="text" id="dartGameTitle" class="form-control" placeholder="{{ Auth::user()->getGameTitle() }} - keine Funktion">
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-[var(--tw-border-color)] my-8">

        {{-- Language --}}
        <section class="mb-10">
            <h4 id="settingsLanguage" class="text-xl font-semibold mb-2">
                Sprache
            </h4>
            <p class="text-[var(--tw-muted-color)] mb-6">
                Festlegen, der Spracheinstellungen
            </p>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Allgemein</div>
                    <div class="md:col-span-3">
                        <x-form.settings.info-text
                            description="siehe Sidebar" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Email</div>
                    <div class="md:col-span-3">
                        <x-form.settings.info-text
                            description="Dieses aufregende Feature ist für die Zukunft geplant, jedoch aktuell noch nicht verfügbar. Wir arbeiten hart daran, um es baldmöglichst für dich bereitzustellen. Freue dich auf zukünftige Updates, die diese Funktion integrieren werden!" />
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-[var(--tw-border-color)] my-8">

        {{-- Account --}}
        <section class="mb-10">
            <h4 id="settingsAccount" class="text-xl font-semibold mb-2">
                Konto
            </h4>
            <p class="text-[var(--tw-muted-color)] mb-6">
                Festlegen, von Konto relevanten Einstellungen
            </p>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-sm font-medium text-[var(--tw-muted-color)]">Allgemein</div>
                    <div class="md:col-span-3 space-y-6">
                        <div>
                            <button type="button" class="btn btn-outline-warning mb-2">Archiv anfordern</button>
                            <p class="text-sm text-[var(--tw-muted-color)]">
                                Noch keine Funktion. Du kannst hier ein Archiv Deiner Daten anfordern.<br />
                                Mit dem Klick auf "Archiv anfordern" stimmst du zu, dass wir Dir Deine Benutzerdaten an die Adresse <span class="text-white">{{ Auth::user()->email }}</span> zusenden.
                            </p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-danger mb-2">Konto löschen</button>
                            <p class="text-sm text-[var(--tw-muted-color)]">
                                Bist du sicher, dass du unsere großartige Plattform verlassen möchtest?<br />
                                Bedenke, dass du mit dem Löschen deines Kontos dauerhaft den Zugriff auf all die nützlichen Werkzeuge und Ressourcen verlierst, die wir bieten.
                                Du würdest damit auch die Möglichkeit einbüßen, von unseren zukünftigen Innovationen zu profitieren.<br />
                                Bitte überlege sorgfältig, bevor du diesen Schritt gehst.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
