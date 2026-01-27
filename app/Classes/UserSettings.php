<?php

namespace App\Classes;

use App\Models\User;

class UserSettings
{
    protected User $user;
    protected array $settings;

    /**
     * Default settings values.
     */
    protected static array $defaults = [
        // Design
        'darkMode' => 'auto',

        // Notifications
        'receiveNewsletter' => false,
        'receiveNewDeviceLogin' => false,
        'receiveDartGameInvitation' => true,
        'receiveDartGameReport' => true,
        'receiveDartGameWeeklyReport' => false,
        'receiveAccountChanges' => true,

        // Privacy and Security
        'isProfilePicturePublic' => true,
        'isOnlineStatusPublic' => false,
        'dartGameInvitation' => true,
        'dartGameInvitationAutoAccept' => true,
        'isDartGameStatisticPublic' => false,

        // Presets
        'defaultDartGameTitle' => '',

        // Language
        'language' => 'de',
        'languageEmail' => 'de',
    ];

    public function __construct(User $user)
    {
        $this->user = $user;
        $raw = $user->getRawOriginal('settings');

        if (is_string($raw)) {
            $this->settings = json_decode($raw, true) ?? [];
        } elseif (is_array($raw)) {
            $this->settings = $raw;
        } else {
            $this->settings = [];
        }
    }

    /**
     * Get a setting value.
     * Returns an object with a 'value' property for compatibility with old laraconfig API.
     */
    public function get(string $key): ?object
    {
        if (array_key_exists($key, $this->settings)) {
            return (object) ['value' => $this->settings[$key]];
        }

        // Use class defaults
        if (array_key_exists($key, self::$defaults)) {
            return (object) ['value' => self::$defaults[$key]];
        }

        return null;
    }

    /**
     * Get a setting value directly.
     */
    public function value(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        }

        // Use class defaults if no explicit default provided
        if ($default === null && array_key_exists($key, self::$defaults)) {
            return self::$defaults[$key];
        }

        return $default;
    }

    /**
     * Set a setting value.
     */
    public function set(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
        $this->save();
    }

    /**
     * Check if a setting exists.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->settings);
    }

    /**
     * Remove a setting.
     */
    public function forget(string $key): void
    {
        unset($this->settings[$key]);
        $this->save();
    }

    /**
     * Get all settings.
     */
    public function all(): array
    {
        return $this->settings;
    }

    /**
     * Save settings to the database.
     */
    protected function save(): void
    {
        $this->user->setRawAttributes(
            array_merge($this->user->getAttributes(), ['settings' => json_encode($this->settings)])
        );
        $this->user->save();
    }
}
