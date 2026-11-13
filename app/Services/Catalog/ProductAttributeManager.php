<?php
namespace App\Services\Catalog;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
class ProductAttributeManager {
 public function validateValues(int $categoryId,array $values,bool $requireRequired=true): array {
  $attributes=ProductAttribute::query()->whereHas('categories',fn($q)=>$q->where('categories.id',$categoryId))->with('options')->get()->keyBy('id');
  $errors=[];
  foreach($values as $id=>$input){
   if(!$attributes->has((int)$id)){ $errors["attribute_values.$id"]='این ویژگی به دسته‌بندی انتخاب‌شده تعلق ندارد.'; continue; }
   $a=$attributes->get((int)$id); $input=is_array($input)?$input:['value'=>$input]; $value=$input['value']??null;
   if($a->type==='number' && filled($value) && !is_numeric($this->digits((string)$value))) $errors["attribute_values.$id.value"]="مقدار «{$a->label}» باید عددی باشد.";
   if($a->type==='select' && filled($input['option_id']??null) && !$a->options->contains('id',(int)$input['option_id'])) $errors["attribute_values.$id.option_id"]="گزینه «{$a->label}» معتبر نیست.";
   if($a->type==='multiselect'){ $ids=array_filter((array)($input['option_ids']??[]),fn($v)=>$v!==''&&$v!==null); if($a->options->whereIn('id',$ids)->count()!==count($ids)) $errors["attribute_values.$id.option_ids"]="یکی از گزینه‌های «{$a->label}» معتبر نیست."; }
   if($a->type==='boolean' && filled($value) && !in_array((string)$value,['0','1','true','false'],true)) $errors["attribute_values.$id.value"]="مقدار «{$a->label}» معتبر نیست.";
  }
  foreach($attributes as $a){ if(!$requireRequired || !$a->pivot->is_required) continue; $input=$values[$a->id]??[]; $input=is_array($input)?$input:['value'=>$input];
   $has=match($a->type){'select'=>filled($input['option_id']??null),'multiselect'=>count(array_filter((array)($input['option_ids']??[])))>0,default=>array_key_exists('value',$input)&&$input['value']!==''&&$input['value']!==null};
   if(!$has) $errors["attribute_values.{$a->id}.value"]="تکمیل ویژگی «{$a->label}» الزامی است.";
  }
  return $errors;
 }
 public function sync(Product $product,array $values): void {
  $allowed=$product->category->attributes()->pluck('attributes.id')->map(fn($id)=>(int)$id)->all();
  ProductAttributeValue::where('product_id',$product->id)->whereNotIn('attribute_id',$allowed)->delete();
  foreach($values as $id=>$input){
   $a=ProductAttribute::whereKey($id)->whereHas('categories',fn($q)=>$q->where('categories.id',$product->category_id))->first(); if(!$a) continue;
   $input=is_array($input)?$input:['value'=>$input]; ProductAttributeValue::where('product_id',$product->id)->where('attribute_id',$a->id)->delete();
   if($a->type==='multiselect'){ foreach(array_unique(array_map('intval',(array)($input['option_ids']??[]))) as $optionId) if($optionId>0&&AttributeOption::where('attribute_id',$a->id)->whereKey($optionId)->exists()) ProductAttributeValue::create(['product_id'=>$product->id,'attribute_id'=>$a->id,'option_id'=>$optionId]); continue; }
   if($a->type==='select'){ $optionId=(int)($input['option_id']??0); if($optionId>0&&AttributeOption::where('attribute_id',$a->id)->whereKey($optionId)->exists()) ProductAttributeValue::create(['product_id'=>$product->id,'attribute_id'=>$a->id,'option_id'=>$optionId]); continue; }
   $value=$input['value']??null; if($value===''||$value===null) continue;
   $row=['product_id'=>$product->id,'attribute_id'=>$a->id];
   if($a->type==='number') $row['value_number']=$this->digits((string)$value);
   elseif($a->type==='boolean') $row['value_boolean']=filter_var($value,FILTER_VALIDATE_BOOLEAN);
   else $row['value_text']=trim((string)$value);
   ProductAttributeValue::create($row);
  }
 }
 public function completeness(Product $product): array {
  $required=$product->category->attributes()->wherePivot('is_required',true)->get(); $values=$product->attributeValues()->get()->groupBy('attribute_id');
  $complete=$required->filter(fn($a)=>$values->has($a->id)&&$values->get($a->id)->contains(fn($v)=>$v->option_id!==null||$v->value_text!==null||$v->value_number!==null||$v->value_boolean!==null));
  return ['complete'=>$complete->count(),'required'=>$required->count(),'is_complete'=>$complete->count()===$required->count()];
 }
 private function digits(string $v): string { return strtr($v,['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']); }
}