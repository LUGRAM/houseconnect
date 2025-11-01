<?php

namespace App\Observers;

use App\Models\Property;
use Illuminate\Support\Facades\Log;

class PropertyObserver
{
    /**
     * Log à la création d’un bien immobilier
     */
    public function created(Property $property): void
    {
        Log::info('Bien créé', [
            'property_id' => $property->id,
            'owner_id' => $property->user_id,
            'title' => $property->title,
        ]);
    }

    /**
     * Log et action à la mise à jour d’un bien
     */
    public function updated(Property $property): void
    {
        if ($property->wasChanged('is_validated') && $property->is_validated) {
            Log::info('Bien validé par un administrateur', [
                'property_id' => $property->id,
                'title' => $property->title,
            ]);
        }

        if ($property->wasChanged('price')) {
            Log::info('Prix du bien modifié', [
                'property_id' => $property->id,
                'new_price' => $property->price,
            ]);
        }
    }

    /**
     * Log à la suppression d’un bien
     */
    public function deleted(Property $property): void
    {
        Log::warning('Bien supprimé', [
            'property_id' => $property->id,
            'title' => $property->title,
        ]);
    }
}
