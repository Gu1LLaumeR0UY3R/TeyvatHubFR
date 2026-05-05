<?php

namespace App\Http\Controllers;

use App\Mail\SurveyResultsMail;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SurveyResponseController extends Controller
{
    public function store(Request $request, Survey $survey): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Vérifier si le questionnaire est fermé
        if ($survey->isClosed()) {
            return back()->with('error', 'Ce questionnaire est fermé.');
        }

        // Vérifier l'unicité (une seule réponse par utilisateur)
        $alreadyAnswered = SurveyResponse::where('survey_id', $survey->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyAnswered) {
            return back()->with('error', 'Vous avez déjà répondu à ce questionnaire.');
        }

        // Validation dynamique selon les types de questions
        $rules = [];
        $survey->load('questions');
        foreach ($survey->questions as $question) {
            $key = "answers.q{$question->id}";
            $rules[$key] = match ($question->type) {
                'qcm'     => 'required|string|max:255',
                'checkbox'=> 'required|array|min:1',
                'text'    => 'required|string|max:2000',
                'rating'  => 'required|integer|min:1|max:5',
                'boolean' => 'required|boolean',
                default   => 'required',
            };
        }
        $validated = $request->validate($rules);

        // Créer la réponse
        $response = SurveyResponse::create([
            'survey_id'    => $survey->id,
            'user_id'      => $user->id,
            'submitted_at' => now(),
        ]);

        // Créer les réponses par question avec sanitisation XSS
        foreach ($survey->questions as $question) {
            $raw = $validated['answers']["q{$question->id}"] ?? null;

            // Sanitisation selon le type
            $value = match ($question->type) {
                'text' => htmlspecialchars(strip_tags((string)$raw), ENT_QUOTES, 'UTF-8'),
                'qcm'  => htmlspecialchars(strip_tags((string)$raw), ENT_QUOTES, 'UTF-8'),
                'checkbox' => array_map(
                    fn($o) => htmlspecialchars(strip_tags($o), ENT_QUOTES, 'UTF-8'),
                    (array)$raw
                ),
                default => $raw, // rating et boolean : valeurs numériques/booléennes, pas de XSS
            };

            SurveyAnswer::create([
                'response_id' => $response->id,
                'question_id' => $question->id,
                'value'       => $value,
            ]);
        }

        // Envoyer les résultats agrégés par mail si le questionnaire est maintenant fermé
        // ou si c'est la première réponse (optionnel — ici on envoie à chaque réponse)
        $this->sendResultsMail($survey);

        return back()->with('success', 'Merci pour votre participation !');
    }

    /**
     * Agrège toutes les réponses et envoie le mail de résultats.
     */
    private function sendResultsMail(Survey $survey): void
    {
        $survey->load(['questions.answers']);
        $aggregated = [];

        foreach ($survey->questions as $question) {
            $answers = $question->answers->map(fn($a) => $a->value)->toArray();
            $item = ['type' => $question->type, 'label' => $question->label];

            switch ($question->type) {
                case 'qcm':
                    $tally = array_count_values(array_map(fn($v) => is_array($v) ? '' : (string)$v, $answers));
                    $item['tally'] = $tally;
                    break;
                case 'checkbox':
                    $flat = array_merge(...array_map(fn($v) => is_array($v) ? $v : [], $answers));
                    $item['tally'] = array_count_values($flat);
                    break;
                case 'rating':
                    $nums = array_filter($answers, 'is_numeric');
                    $item['average'] = count($nums) > 0 ? array_sum($nums) / count($nums) : 0;
                    $item['count']   = count($nums);
                    break;
                case 'boolean':
                    $item['yes'] = count(array_filter($answers, fn($v) => $v === true || $v === 1 || $v === '1'));
                    $item['no']  = count($answers) - $item['yes'];
                    break;
                case 'text':
                    $item['answers'] = array_map(fn($v) => is_string($v) ? $v : '', $answers);
                    break;
            }

            $aggregated[] = $item;
        }

        Mail::to($survey->notification_email)->send(new SurveyResultsMail($survey, $aggregated));
    }
}
