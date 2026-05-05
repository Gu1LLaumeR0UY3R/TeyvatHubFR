<?php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishScheduledArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Article::where('status', 'draft')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->each(function (Article $article) {
                $article->update([
                    'status'       => 'published',
                    'published_at' => now(),
                ]);
            });
    }
}
