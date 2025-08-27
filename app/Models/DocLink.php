<?php
// app/Models/DocLink.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocLink extends Model
{
    use HasFactory;

    protected $fillable = [ 'document_id','slug','expires_at','allow_download','max_views','views' ];
    protected $casts = [ 'expires_at' => 'datetime', 'allow_download' => 'boolean' ];

    public function document(){ return $this->belongsTo(Document::class); }

    public function isExpired(): bool { return $this->expires_at && now()->greaterThan($this->expires_at); }

    public function isViewLimitHit(): bool { return !is_null($this->max_views) && $this->views >= $this->max_views; }
}
