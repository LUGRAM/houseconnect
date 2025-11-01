<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Property, Appointment, Payment, Invoice, Expense};
use App\Settings\SystemSetting;
use Spatie\Permission\Models\Role;
use App\Enums\PaymentStatus;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        app(SystemSetting::class)->fill([
            'app_name' => 'HouseConnect',
            'app_email' => 'support@houseconnect.local',
            'support_phone' => '+24100000000',
            'currency' => 'XAF',
            'default_locale' => 'fr',
            'maintenance_mode' => false,
            'max_property_photos' => 10,
            'commission_rate' => 5.0,
            'visit_price_default' => 1000,
            'allow_multi_language' => true,
        ])->save();
    

        // Rôles
        $roles = ['admin', 'bailleur', 'client'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Super Admin
        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@houseconnect.com',
            'phone' => '0700000000',
            'password' => Hash::make('admin1234'),
        ]);
        $admin->assignRole('admin');

        // Utilisateur multi-rôles
        $multi = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0711223344',
            'password' => Hash::make('password'),
        ]);
        $multi->assignRole(['client', 'bailleur']);

        // Bailleurs avec propriétés
        $bailleurs = User::factory(5)->create();
        foreach ($bailleurs as $bailleur) {
            $bailleur->assignRole('bailleur');
            $properties = Property::factory(3)->create(['user_id' => $bailleur->id]);

            foreach ($properties as $property) {
                Expense::factory(2)->create(['property_id' => $property->id, 'user_id' => $bailleur->id]);
                Appointment::factory(1)->create([
                    'property_id' => $property->id,
                    'user_id' => $multi->id,
                ]);
            }

    
        // Clients
        $clients = User::factory(5)->create();
        foreach ($clients as $client) {
            $client->assignRole('client');
            $payment = Payment::factory()->create([
                'user_id' => $client->id,
                'amount' => fake()->numberBetween(1000, 5000),
                'status' => PaymentStatus::SUCCESS->value,
            ]);
            Invoice::factory()->create([
                'user_id' => $client->id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'status' => 'paid',
            ]);
        }

        $this->command->info('Base HouseConnect peuplée avec succès.');
    }
}
}