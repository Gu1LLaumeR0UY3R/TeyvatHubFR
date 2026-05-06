<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            try {
                Schema::table('articles', fn (Blueprint $table) => $table->dropUnique(['slug']));
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('articles', fn (Blueprint $table) => $table->dropIndex('articles_slug_unique'));
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('articles', fn (Blueprint $table) => $table->dropIndex(['fid_admin']));
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('articles', fn (Blueprint $table) => $table->dropIndex('articles_fid_admin_index'));
            } catch (\Throwable $e) {
            }

            Schema::table('articles', function (Blueprint $table) {
                if (Schema::hasColumn('articles', 'id_article') && !Schema::hasColumn('articles', 'id')) {
                    $table->renameColumn('id_article', 'id');
                }
                if (Schema::hasColumn('articles', 'titre') && !Schema::hasColumn('articles', 'title')) {
                    $table->renameColumn('titre', 'title');
                }
                if (Schema::hasColumn('articles', 'fid_admin') && !Schema::hasColumn('articles', 'author_id')) {
                    $table->renameColumn('fid_admin', 'author_id');
                }
                if (Schema::hasColumn('articles', 'statut') && !Schema::hasColumn('articles', 'status')) {
                    $table->renameColumn('statut', 'status');
                }
            });

            Schema::table('articles', function (Blueprint $table) {
                $toDrop = [];
                if (Schema::hasColumn('articles', 'slug')) {
                    $toDrop[] = 'slug';
                }
                if (Schema::hasColumn('articles', 'extrait')) {
                    $toDrop[] = 'extrait';
                }
                if (Schema::hasColumn('articles', 'cover_image')) {
                    $toDrop[] = 'cover_image';
                }

                if (!empty($toDrop)) {
                    $table->dropColumn($toDrop);
                }
            });

            DB::table('articles')->where('status', 'publié')->update(['status' => 'published']);
            DB::table('articles')->where('status', 'brouillon')->update(['status' => 'draft']);

            Schema::table('articles', function (Blueprint $table) {
                if (!Schema::hasColumn('articles', 'type')) {
                    $table->string('type')->default('annonce');
                }
                if (!Schema::hasColumn('articles', 'is_pinned')) {
                    $table->boolean('is_pinned')->default(false);
                }
                if (!Schema::hasColumn('articles', 'pinned_until')) {
                    $table->timestamp('pinned_until')->nullable();
                }
                if (!Schema::hasColumn('articles', 'scheduled_at')) {
                    $table->timestamp('scheduled_at')->nullable();
                }
            });

            try {
                Schema::table('articles', fn (Blueprint $table) => $table->index('author_id'));
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('articles', fn (Blueprint $table) => $table->index('status'));
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('articles', fn (Blueprint $table) => $table->index('type'));
            } catch (\Throwable $e) {
            }

            return;
        }

        // ── 1. Supprimer les index avant de renommer les colonnes ────────
        try { Schema::table('articles', fn (Blueprint $t) => $t->dropUnique(['slug'])); } catch (\Exception $e) {}
        try { Schema::table('articles', fn (Blueprint $t) => $t->dropIndex('articles_fid_admin_index')); } catch (\Exception $e) {}

        // ── 2. Renommer les colonnes (syntaxe CHANGE COLUMN pour MariaDB) ──
        DB::statement('ALTER TABLE articles CHANGE COLUMN id_article id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE articles CHANGE COLUMN titre title VARCHAR(255) NOT NULL');
        DB::statement("ALTER TABLE articles CHANGE COLUMN fid_admin author_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE articles CHANGE COLUMN statut status ENUM('brouillon','publié') NOT NULL DEFAULT 'brouillon'");

        // ── 3. Supprimer les anciennes colonnes inutiles ─────────────────
        Schema::table('articles', function (Blueprint $table) {
            $cols = array_column(DB::select('DESCRIBE articles'), 'Field');
            $toDrop = array_filter(['slug', 'extrait', 'cover_image'], fn($c) => in_array($c, $cols));
            if ($toDrop) $table->dropColumn(array_values($toDrop));
        });

        // ── 4. Migrer les valeurs de status avant de changer l'enum ──────
        DB::statement("UPDATE articles SET status = 'published' WHERE status = 'publié'");
        DB::statement("UPDATE articles SET status = 'draft'     WHERE status = 'brouillon'");
        DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'");

        // ── 5. Ajouter les nouvelles colonnes ────────────────────────────
        Schema::table('articles', function (Blueprint $table) {
            $cols = array_column(DB::select('DESCRIBE articles'), 'Field');
            if (!in_array('type', $cols))
                $table->enum('type', ['patch_note', 'annonce', 'amelioration', 'questionnaire'])
                      ->default('annonce')
                      ->after('author_id');
            if (!in_array('is_pinned', $cols))
                $table->boolean('is_pinned')->default(false)->after('content');
            if (!in_array('pinned_until', $cols))
                $table->timestamp('pinned_until')->nullable()->after('is_pinned');
            if (!in_array('scheduled_at', $cols))
                $table->timestamp('scheduled_at')->nullable()->after('pinned_until');
            $table->index('author_id');
            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            try {
                Schema::table('articles', fn (Blueprint $table) => $table->dropIndex(['author_id']));
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('articles', fn (Blueprint $table) => $table->dropIndex(['status']));
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('articles', fn (Blueprint $table) => $table->dropIndex(['type']));
            } catch (\Throwable $e) {
            }

            Schema::table('articles', function (Blueprint $table) {
                $toDrop = [];
                if (Schema::hasColumn('articles', 'type')) {
                    $toDrop[] = 'type';
                }
                if (Schema::hasColumn('articles', 'is_pinned')) {
                    $toDrop[] = 'is_pinned';
                }
                if (Schema::hasColumn('articles', 'pinned_until')) {
                    $toDrop[] = 'pinned_until';
                }
                if (Schema::hasColumn('articles', 'scheduled_at')) {
                    $toDrop[] = 'scheduled_at';
                }

                if (!empty($toDrop)) {
                    $table->dropColumn($toDrop);
                }
            });

            DB::table('articles')->where('status', 'draft')->update(['status' => 'brouillon']);
            DB::table('articles')->where('status', 'published')->update(['status' => 'publié']);

            Schema::table('articles', function (Blueprint $table) {
                if (Schema::hasColumn('articles', 'status') && !Schema::hasColumn('articles', 'statut')) {
                    $table->renameColumn('status', 'statut');
                }
                if (Schema::hasColumn('articles', 'author_id') && !Schema::hasColumn('articles', 'fid_admin')) {
                    $table->renameColumn('author_id', 'fid_admin');
                }
                if (Schema::hasColumn('articles', 'title') && !Schema::hasColumn('articles', 'titre')) {
                    $table->renameColumn('title', 'titre');
                }
                if (Schema::hasColumn('articles', 'id') && !Schema::hasColumn('articles', 'id_article')) {
                    $table->renameColumn('id', 'id_article');
                }
            });

            Schema::table('articles', function (Blueprint $table) {
                if (!Schema::hasColumn('articles', 'slug')) {
                    $table->string('slug', 255)->nullable();
                }
                if (!Schema::hasColumn('articles', 'extrait')) {
                    $table->text('extrait')->nullable();
                }
                if (!Schema::hasColumn('articles', 'cover_image')) {
                    $table->string('cover_image', 500)->nullable();
                }
            });

            try {
                Schema::table('articles', fn (Blueprint $table) => $table->unique('slug'));
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('articles', fn (Blueprint $table) => $table->index('fid_admin'));
            } catch (\Throwable $e) {
            }

            return;
        }

        // ── Inverser dans l'ordre ────────────────────────────────────────
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['author_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'is_pinned', 'pinned_until', 'scheduled_at']);
        });

        DB::statement("UPDATE articles SET status = 'brouillon' WHERE status = 'draft'");
        DB::statement("UPDATE articles SET status = 'publié'    WHERE status = 'published'");
        DB::statement("ALTER TABLE articles CHANGE COLUMN status statut ENUM('brouillon','publié') NOT NULL DEFAULT 'brouillon'");

        DB::statement("ALTER TABLE articles CHANGE COLUMN author_id fid_admin BIGINT UNSIGNED NULL");
        DB::statement('ALTER TABLE articles CHANGE COLUMN title titre VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE articles CHANGE COLUMN id id_article BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');

        Schema::table('articles', function (Blueprint $table) {
            $table->string('slug', 255)->unique()->after('titre');
            $table->text('extrait')->nullable()->after('slug');
            $table->string('cover_image', 500)->nullable();
            $table->index('fid_admin');
        });
    }
};
