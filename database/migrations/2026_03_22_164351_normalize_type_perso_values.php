<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Récupérer l'ID de "Jouable" (si existe) ou "Personnage jouable"
        $jouableId = DB::table('type_perso')
            ->where('libelle_TP', 'Jouable')
            ->value('id_TP');

        $personnageJouableId = DB::table('type_perso')
            ->where('libelle_TP', 'Personnage jouable')
            ->value('id_TP');

        $jouableMinId = DB::table('type_perso')
            ->where('libelle_TP', 'jouable')
            ->value('id_TP');

        // Si "Jouable" n'existe pas mais "Personnage jouable" existe, renommer d'abord
        if (!$jouableId && $personnageJouableId) {
            DB::table('type_perso')
                ->where('id_TP', $personnageJouableId)
                ->update(['libelle_TP' => 'Jouable']);
            $jouableId = $personnageJouableId;
        }

        // Si "jouable" (minuscule) existe avec un ID différent, rediriger tous les personnages vers "Jouable"
        if ($jouableMinId && $jouableMinId !== $jouableId) {
            DB::table('personnage')
                ->where('fid_TP', $jouableMinId)
                ->update(['fid_TP' => $jouableId]);

            // Ensuite supprimer "jouable"
            DB::table('type_perso')
                ->where('id_TP', $jouableMinId)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: restaurer la valeur "Personnage jouable" si nécessaire
        DB::table('type_perso')
            ->where('libelle_TP', 'Jouable')
            ->update(['libelle_TP' => 'Personnage jouable']);
    }
};
