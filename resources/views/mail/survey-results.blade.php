<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 0 auto; color: #1f2937; }
        h1 { font-size: 1.4rem; color: #111827; border-bottom: 2px solid #e5e7eb; padding-bottom: .5rem; }
        h2 { font-size: 1rem; color: #374151; margin: 1.5rem 0 .4rem; }
        .meta { color: #6b7280; font-size: .85rem; margin-bottom: 1.5rem; }
        .question { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: .5rem; padding: 1rem; margin-bottom: 1rem; }
        .q-label { font-weight: 600; margin-bottom: .5rem; }
        .q-type { display: inline-block; font-size: .75rem; background: #e0e7ff; color: #3730a3; border-radius: 999px; padding: .1rem .5rem; margin-bottom: .5rem; }
        .answer { font-size: .9rem; padding: .2rem 0; border-bottom: 1px solid #f3f4f6; }
        .answer:last-child { border-bottom: none; }
        .footer { margin-top: 2rem; font-size: .8rem; color: #9ca3af; }
    </style>
</head>
<body>

<h1>Résultats du questionnaire</h1>
<p class="meta">
    Article : <strong>{{ $survey->article->title }}</strong><br>
    Fermé le : {{ $survey->closes_at?->format('d/m/Y H:i') ?? 'toujours ouvert' }}<br>
    Nombre de réponses : <strong>{{ $survey->responses()->count() }}</strong>
</p>

@foreach($aggregated as $item)
    <div class="question">
        <span class="q-type">{{ $item['type'] }}</span>
        <div class="q-label">{{ $item['label'] }}</div>

        @if(in_array($item['type'], ['qcm', 'checkbox']))
            @foreach($item['tally'] as $option => $count)
                <div class="answer">{{ $option }} — <strong>{{ $count }}</strong> réponse(s)</div>
            @endforeach
        @elseif($item['type'] === 'rating')
            <div class="answer">Moyenne : <strong>{{ number_format($item['average'], 1) }} / 5</strong> ({{ $item['count'] }} votes)</div>
        @elseif($item['type'] === 'boolean')
            <div class="answer">Oui : <strong>{{ $item['yes'] }}</strong> — Non : <strong>{{ $item['no'] }}</strong></div>
        @elseif($item['type'] === 'text')
            @foreach($item['answers'] as $ans)
                <div class="answer">{{ $ans }}</div>
            @endforeach
        @endif
    </div>
@endforeach

<p class="footer">Cet e-mail a été généré automatiquement par TeyvatHub.</p>
</body>
</html>
