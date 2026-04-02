<?php

namespace App\Console\Commands;

use App\Models\Elements;
use App\Models\Personnage;
use App\Models\TypeArme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RepairCharacterData extends Command
{
    protected $signature = 'repair:characters-data';

    protected $description = 'Corrige les elements et types d\'armes des personnages via la source API teyvat-dev';

    public function handle(): int
    {
        $this->info('Demarrage de la correction des personnages...');

        $apiCharacters = $this->fetchCharactersFromApi();
        if (empty($apiCharacters)) {
            $this->error('Aucune donnee personnage recuperable depuis l\'API.');
            return self::FAILURE;
        }

        $elementCache = $this->buildElementCache();
        $weaponTypeCache = $this->buildWeaponTypeCache();

        $stats = [
            'characters_seen' => 0,
            'element_updated' => 0,
            'weapon_type_updated' => 0,
            'no_api_match' => 0,
            'element_unresolved' => 0,
            'weapon_type_unresolved' => 0,
        ];
        $unresolvedElements = [];
        $unresolvedWeaponTypes = [];

        foreach (Personnage::query()->get() as $personnage) {
            if (!$personnage instanceof Personnage) {
                continue;
            }

            $stats['characters_seen']++;
            $payload = $apiCharacters[$personnage->slug] ?? null;
            if (!$payload) {
                $stats['no_api_match']++;
                continue;
            }

            $dirty = false;

            $elementLabel = $payload['element']['name'] ?? $payload['element'] ?? null;
            $elementId = $this->resolveElementId($elementLabel, $elementCache);
            if ($elementId === null) {
                $stats['element_unresolved']++;
                $unresolvedElements[] = $personnage->slug.' => '.(is_scalar($elementLabel) ? (string) $elementLabel : 'null');
            } elseif ($personnage->fid_element !== $elementId) {
                $personnage->fid_element = $elementId;
                $stats['element_updated']++;
                $dirty = true;
            }

            $weaponLabel = $payload['weapon_type']['name'] ?? $payload['weapon_type'] ?? null;
            $weaponTypeId = $this->resolveWeaponTypeId($weaponLabel, $weaponTypeCache);
            if ($weaponTypeId === null) {
                $stats['weapon_type_unresolved']++;
                $unresolvedWeaponTypes[] = $personnage->slug.' => '.(is_scalar($weaponLabel) ? (string) $weaponLabel : 'null');
            } elseif ($personnage->fid_TArmes !== $weaponTypeId) {
                $personnage->fid_TArmes = $weaponTypeId;
                $stats['weapon_type_updated']++;
                $dirty = true;
            }

            if ($dirty) {
                $personnage->save();
            }
        }

        $this->line('---');
        $this->info('Correction terminee.');
        foreach ($stats as $k => $v) {
            $this->line($k.': '.$v);
        }

        if (!empty($unresolvedElements)) {
            $this->warn('Elements non resolus:');
            foreach ($unresolvedElements as $line) {
                $this->line(' - '.$line);
            }
        }

        if (!empty($unresolvedWeaponTypes)) {
            $this->warn('Types d\'armes non resolus:');
            foreach ($unresolvedWeaponTypes as $line) {
                $this->line(' - '.$line);
            }
        }

        return self::SUCCESS;
    }

    private function fetchCharactersFromApi(): array
    {
        $base = 'https://teyvat-dev.vercel.app/api';
        $response = Http::timeout(30)->retry(2, 500)->get("{$base}/characters");

        if (!$response->successful()) {
            return [];
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            return [];
        }

        $bySlug = [];
        foreach ($payload as $character) {
            if (!is_array($character) || empty($character['name'])) {
                continue;
            }
            $bySlug[Str::slug((string) $character['name'])] = $character;
        }

        return $bySlug;
    }

    private function buildElementCache(): array
    {
        $cache = [];
        foreach (Elements::query()->get() as $element) {
            $cache[$this->normalizeLabel((string) $element->libelle_element)] = $element->id_element;
        }

        return $cache;
    }

    private function buildWeaponTypeCache(): array
    {
        $cache = [];
        foreach (TypeArme::query()->get() as $type) {
            $cache[$this->normalizeLabel((string) $type->libelle_TArme)] = $type->id_TArmes;
        }

        return $cache;
    }

    private function resolveElementId(mixed $rawValue, array $cache): ?int
    {
        if (!is_string($rawValue) || trim($rawValue) === '') {
            return null;
        }

        $value = $this->normalizeLabel($rawValue);
        if (isset($cache[$value])) {
            return $cache[$value];
        }

        $aliases = [
            'anemo' => ['anemo'],
            'geo' => ['geo'],
            'electro' => ['electro'],
            'dendro' => ['dendro'],
            'hydro' => ['hydro'],
            'pyro' => ['pyro'],
            'cryo' => ['cryo'],
        ];

        foreach ($aliases as $source => $candidates) {
            if ($value !== $source) {
                continue;
            }
            foreach ($candidates as $candidate) {
                $normalizedCandidate = $this->normalizeLabel($candidate);
                if (isset($cache[$normalizedCandidate])) {
                    return $cache[$normalizedCandidate];
                }
            }
        }

        foreach ($cache as $label => $id) {
            if (str_contains($label, $value) || str_contains($value, $label)) {
                return $id;
            }
        }

        return null;
    }

    private function resolveWeaponTypeId(mixed $rawValue, array $cache): ?int
    {
        if (!is_string($rawValue) || trim($rawValue) === '') {
            return null;
        }

        $value = $this->normalizeLabel($rawValue);
        if (isset($cache[$value])) {
            return $cache[$value];
        }

        $aliases = [
            'one-handed sword' => ['sword', 'epee'],
            'sword' => ['sword', 'epee'],
            'claymore' => ['claymore', 'espadon'],
            'polearm' => ['polearm', 'lance'],
            'catalyst' => ['catalyst', 'catalyseur'],
            'bow' => ['bow', 'arc'],
        ];

        foreach ($aliases as $source => $candidates) {
            if ($value !== $source) {
                continue;
            }
            foreach ($candidates as $candidate) {
                $normalizedCandidate = $this->normalizeLabel($candidate);
                if (isset($cache[$normalizedCandidate])) {
                    return $cache[$normalizedCandidate];
                }
            }
        }

        foreach ($cache as $label => $id) {
            if (str_contains($label, $value) || str_contains($value, $label)) {
                return $id;
            }
        }

        return null;
    }

    private function normalizeLabel(string $value): string
    {
        return Str::lower(trim(Str::ascii($value)));
    }
}
