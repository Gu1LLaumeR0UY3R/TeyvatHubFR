<?php

use App\Models\Photo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nettoie les anciennes valeurs legacy de photoable_type.
     *
     * Avant : App\\Models\\Personnage, App\\Models\\Nation, ...) 
     * Après  : personnage, nation, arme, ...
     */
    public function up(): void
    {
        $rows = DB::table('photo')->select('id_photo', 'photoable_type')->get();

        foreach ($rows as $row) {
            $normalized = Photo::normalizeMorphType((string) $row->photoable_type);

            if ($normalized !== $row->photoable_type) {
                DB::table('photo')
                    ->where('id_photo', $row->id_photo)
                    ->update(['photoable_type' => $normalized]);
            }
        }
    }

    public function down(): void
    {
        // Aucun retour automatique possible, car on ne peut pas reconstruire
        // précisément les anciennes valeurs complètes d'un morphMap historique.
        // Cette migration est volontairement unidirectionnelle.
    }
};
