<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SystemSetting extends Settings
{
    public string $app_name;
    public string $app_email;
    public string $support_phone;
    public string $currency;
    public string $default_locale;
    public bool $maintenance_mode;
    public int $max_property_photos;
    public float $commission_rate;
    public float $visit_price_default;
    public bool $allow_multi_language;

    /**
     * Groupe logique de paramètres dans la table "settings"
     */
    public static function group(): string
    {
        return 'system';
    }

    /**
     * Fournit les valeurs par défaut
     */
    public static function defaults(): array
    {
        return [
            'app_name' => 'HouseConnect',
            'app_email' => 'support@houseconnect.com',
            'support_phone' => '+241070000000',
            'currency' => 'XAF',
            'default_locale' => 'fr',
            'maintenance_mode' => false,
            'max_property_photos' => 10,
            'commission_rate' => 5.0,
            'visit_price_default' => 5000.0,
            'allow_multi_language' => true,
        ];
    }
}
