<?php
// database/migrations/2025_08_27_100000_create_documents_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('original_name');
            $table->string('storage_path'); // storage/app/<path relative>
            $table->string('mime')->default('application/pdf');
            $table->unsignedBigInteger('size')->default(0);
            $table->boolean('allow_download')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('documents');
    }
};
