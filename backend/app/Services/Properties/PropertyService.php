<?php

namespace App\Services\Properties;

use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PropertyService
{
    public function create(array $data): Property
    {
        $data['user_id'] = Auth::id();
        $data['is_validated'] = false;

        $property = Property::create($data);

        Log::info('Nouveau bien soumis', [
            'user_id' => Auth::id(),
            'property_id' => $property->id,
        ]);

        return $property;
    }

    public function update(Property $property, array $data): Property
    {
        if ($property->user_id !== Auth::id()) {
            throw new HttpException(403, 'Accès refusé.');
        }

        if ($property->is_validated) {
            throw new HttpException(403, 'Impossible de modifier un bien déjà validé.');
        }

        $property->update($data);

        Log::info('Bien mis à jour', ['property_id' => $property->id]);

        return $property;
    }

    public function delete(Property $property): void
    {
        if ($property->user_id !== Auth::id()) {
            throw new HttpException(403, 'Accès refusé.');
        }

        $property->delete();
    }
}
