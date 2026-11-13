<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProductAttributeValue extends Model {
 protected $fillable=['product_id','attribute_id','option_id','value_text','value_number','value_boolean'];
 protected function casts(): array { return ['value_number'=>'decimal:3','value_boolean'=>'boolean']; }
 public function attribute(): BelongsTo { return $this->belongsTo(ProductAttribute::class,'attribute_id'); }
 public function option(): BelongsTo { return $this->belongsTo(AttributeOption::class,'option_id'); }
}