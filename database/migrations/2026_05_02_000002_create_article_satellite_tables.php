<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patch_note_meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->string('version', 20)->comment('ex: 1.2.3 — format semver');
            $table->date('release_date');
            $table->json('changelog')->comment('{"added":[],"fixed":[],"removed":[]}');

            $table->foreign('article_id')
                  ->references('id')->on('articles')
                  ->onDelete('cascade');
        });

        Schema::create('improvement_meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->enum('planning_status', ['prevu', 'en_cours', 'annule', 'livre'])->default('prevu');
            $table->unsignedInteger('upvotes_count')->default(0);

            $table->foreign('article_id')
                  ->references('id')->on('articles')
                  ->onDelete('cascade');
        });

        Schema::create('improvement_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('improvement_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('voted_at')->useCurrent();

            $table->foreign('improvement_id')
                  ->references('id')->on('improvement_meta')
                  ->onDelete('cascade');
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->unique(['improvement_id', 'user_id']); // anti-double vote
        });

        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->string('notification_email');
            $table->timestamp('closes_at')->nullable();

            $table->foreign('article_id')
                  ->references('id')->on('articles')
                  ->onDelete('cascade');
        });

        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('survey_id');
            $table->enum('type', ['qcm', 'checkbox', 'text', 'rating', 'boolean']);
            $table->string('label');
            $table->unsignedInteger('position')->default(0);
            $table->json('options')->nullable()->comment('Pour qcm/checkbox : ["Option A","Option B"]');

            $table->foreign('survey_id')
                  ->references('id')->on('surveys')
                  ->onDelete('cascade');
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('survey_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('submitted_at')->useCurrent();

            $table->foreign('survey_id')
                  ->references('id')->on('surveys')
                  ->onDelete('cascade');
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->unique(['survey_id', 'user_id']); // une seule réponse par utilisateur
        });

        Schema::create('survey_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('response_id');
            $table->unsignedBigInteger('question_id');
            $table->json('value');

            $table->foreign('response_id')
                  ->references('id')->on('survey_responses')
                  ->onDelete('cascade');
            $table->foreign('question_id')
                  ->references('id')->on('survey_questions')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
        Schema::dropIfExists('improvement_votes');
        Schema::dropIfExists('improvement_meta');
        Schema::dropIfExists('patch_note_meta');
    }
};
