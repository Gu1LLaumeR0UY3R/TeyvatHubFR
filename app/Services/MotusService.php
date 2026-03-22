<?php

namespace App\Services;

use App\Models\Arme;
use App\Models\Ennemi;
use App\Models\Nation;
use App\Models\Personnage;
use Illuminate\Support\Collection;

class MotusService
{
    public function getWordPool(): Collection
    {
        return collect()
            ->merge(Personnage::pluck('nom_perso'))
            ->merge(Arme::pluck('nom_arme'))
            ->merge(Ennemi::pluck('nom_ennemi'))
            ->merge(Nation::pluck('nom_region'))
            ->filter(fn(string $w) => mb_strlen($w) >= 3)
            ->unique()
            ->values();
    }

    public function getDailyWord(): string
    {
        $pool = $this->getWordPool();
        if ($pool->isEmpty()) {
            return 'Mondstadt';
        }
        $sorted = $pool->sort()->values()->toArray();
        $index  = abs(crc32(date('Y-m-d'))) % count($sorted);
        return $sorted[$index];
    }

    public function getRandomWord(): string
    {
        $pool = $this->getWordPool();
        return $pool->isEmpty() ? 'Teyvat' : $pool->random();
    }

    /**
     * Validate a guess against the target word.
     * Returns an array of ['letter' => string, 'status' => 'correct'|'present'|'absent']
     */
    public function validateGuess(string $guess, string $word): array
    {
        $guessNorm = $this->normalize($guess);
        $wordNorm  = $this->normalize($word);

        $guessChars = mb_str_split($guessNorm);
        $wordChars  = mb_str_split($wordNorm);
        $origChars  = mb_str_split($guess);

        $result   = array_fill(0, count($guessChars), null);
        $wordUsed = array_fill(0, count($wordChars), false);

        // First pass: exact positions
        foreach ($guessChars as $i => $char) {
            if (isset($wordChars[$i]) && $char === $wordChars[$i]) {
                $result[$i]   = 'correct';
                $wordUsed[$i] = true;
            }
        }

        // Second pass: wrong positions
        foreach ($guessChars as $i => $char) {
            if ($result[$i] !== null) {
                continue;
            }
            $found = false;
            foreach ($wordChars as $j => $wChar) {
                if (!$wordUsed[$j] && $char === $wChar) {
                    $result[$i]   = 'present';
                    $wordUsed[$j] = true;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[$i] = 'absent';
            }
        }

        return array_map(
            fn($char, $status) => ['letter' => $char, 'status' => $status],
            $origChars,
            $result
        );
    }

    public function normalize(string $word): string
    {
        $from = ['à','á','â','ã','ä','å','è','é','ê','ë','ì','í','î','ï',
                 'ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','ñ','ç','æ','œ'];
        $to   = ['a','a','a','a','a','a','e','e','e','e','i','i','i','i',
                 'o','o','o','o','o','u','u','u','u','y','y','n','c','ae','oe'];
        return str_replace($from, $to, mb_strtolower($word));
    }
}
