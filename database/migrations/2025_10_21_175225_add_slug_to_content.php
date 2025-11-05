<?php

declare(strict_types=1);

use App\Models\Tube\Content;
use App\Traits\ContentIdGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use ContentIdGenerator;

    public function up(): void
    {
        Schema::table('contents', static function (Blueprint $table) {
            $table->string('slug')
                ->nullable()
                ->after('file_hash');
        });

        $this->updateRecords();

        Schema::table('contents', static function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('contents', static function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }

    private function updateRecords(): void
    {
        Content::withTrashed()->get()->each(static function (Content $content) {
            $content->slug = self::generateUniqueId();
            $content->saveQuietly();
        });
    }
};
