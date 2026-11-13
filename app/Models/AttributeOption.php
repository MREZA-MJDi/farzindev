<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AttributeOption extends Model {
 protected $fillable=['attribute_id','label','value','sort_order'];
 public function attribute(): BelongsTo { return $this->belongsTo(ProductAttribute::class,'attribute_id'); }
}