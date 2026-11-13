<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ProductAttribute extends Model {
 protected $table='attributes';
 protected $fillable=['key','label','type','unit','group','help_text'];
 public function options(): HasMany { return $this->hasMany(AttributeOption::class,'attribute_id')->orderBy('sort_order')->orderBy('id'); }
 public function categories(): BelongsToMany { return $this->belongsToMany(Category::class,'category_attribute','attribute_id','category_id')->withPivot(['sort_order','is_required','is_recommended','is_filterable','is_key_spec'])->withTimestamps()->orderByPivot('sort_order'); }
 public function values(): HasMany { return $this->hasMany(ProductAttributeValue::class,'attribute_id'); }
}