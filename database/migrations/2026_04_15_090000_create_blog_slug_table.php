<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_slug', function (Blueprint $table): void {
            $table->increments('id_blog_slug');
            $table->string('slug_base', 120)->unique();
            $table->timestamps();
        });

        $now = now();
        DB::table('blog_slug')->insert([
            ['slug_base' => 'actu', 'created_at' => $now, 'updated_at' => $now],
            ['slug_base' => 'guide', 'created_at' => $now, 'updated_at' => $now],
            ['slug_base' => 'build', 'created_at' => $now, 'updated_at' => $now],
            ['slug_base' => 'event', 'created_at' => $now, 'updated_at' => $now],
            ['slug_base' => 'maj', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_slug');
    }
};