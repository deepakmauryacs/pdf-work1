<?php
// database/migrations/2025_08_27_100001_create_doc_links_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('doc_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('slug', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('allow_download')->nullable();
            $table->unsignedInteger('max_views')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('doc_links');
    }
};
