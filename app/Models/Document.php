<?php
// app/Models/Document.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','original_name','storage_path','mime','size','allow_download',
    ];

    public function links() { return $this->hasMany(DocLink::class); }
}
