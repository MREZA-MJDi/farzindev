<?php
namespace App\Console\Commands;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Services\Catalog\ProductAttributeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class MigrateLegacyProductSpecifications extends Command
{
 protected $signature='catalog:migrate-legacy-specifications {--dry-run : Report changes without writing values}';
 protected $description='Move exact legacy product specification values into the typed attribute tables.';
 private array $maps=[
  'sink'=>['installation'=>'installation','length'=>'length','width'=>'width','basin_count'=>'basin_count','material'=>'sink_material','basin_depth'=>'basin_depth','sheet_thickness'=>'sheet_thickness','capacity_liters'=>'basin_capacity','weight'=>'weight','basin_direction'=>'basin_direction','design_type'=>'sink_design','tap_holes'=>'tap_holes','accessories'=>'sink_accessories'],
  'hood'=>['hood_type'=>'hood_type','hood_model'=>'hood_model','size'=>'hood_size','length'=>'hood_length','width'=>'hood_width','height'=>'hood_height','light_count'=>'light_count','switch_type'=>'switch_type','body_material'=>'hood_body_material','suction_capacity'=>'suction_capacity','filter_type'=>'filter_material','motor_count'=>'motor_count','noise_level'=>'noise_level','air_outlet'=>'air_outlet','accessories'=>'hood_accessories'],
  'gas'=>['model_code'=>'gas_model','length'=>'gas_length','width'=>'gas_width','height'=>'gas_height','burner_count'=>'burner_count','size_range'=>'gas_size_range','burner_position'=>'burner_position','surface_material'=>'surface_material','glass_type'=>'glass_type','glass_thickness'=>'surface_thickness','grate_material'=>'grate_material','knob_type'=>'knob_type','ignition_type'=>'design_model','burner_type'=>'burner_type','valve_type'=>'valve_type','safety_system'=>'safety_system','energy_rating'=>'energy_rating','energy_source'=>'energy_source','power_cable'=>'power_cable','accessories'=>'gas_accessories'],
 ];
 private array $labels=[
  'نحوه نصب'=>'installation','طول'=>'length','عرض'=>'width','تعداد لگن'=>'basin_count','جنس بدنه'=>'material','جنس'=>'material','عمق لگن'=>'basin_depth','ضخامت ورق'=>'sheet_thickness','ظرفیت لگن'=>'capacity_liters','وزن'=>'weight','جهت لگن'=>'basin_direction','نوع طراحی'=>'design_type','تعداد و محل سوراخ شیر'=>'tap_holes','لوازم همراه'=>'accessories',
  'نوع هود'=>'hood_type','مدل'=>'hood_model','سایز (سانتی‌متر)'=>'size','سایز'=>'size','تعداد لامپ'=>'light_count','نوع کلید'=>'switch_type','قدرت مکش (مترمکعب بر ساعت)'=>'suction_capacity','قدرت مکش'=>'suction_capacity','نوع فیلتر'=>'filter_type','تعداد موتور'=>'motor_count','میزان صدا (دسی‌بل)'=>'noise_level','میزان صدا'=>'noise_level','نوع خروجی هوا'=>'air_outlet',
  'ارتفاع'=>'height','تعداد شعله'=>'burner_count','محدوده سایز'=>'size_range','محل شعله پلوپز'=>'burner_position','جنس صفحه'=>'surface_material','نوع شیشه'=>'glass_type','ضخامت شیشه'=>'glass_thickness','جنس شبکه'=>'grate_material','نوع ولوم'=>'knob_type','نوع فندک'=>'ignition_type','نوع سرشعله'=>'burner_type','نوع شیر'=>'valve_type','سیستم ایمنی و ترموکوپل'=>'safety_system','رده مصرف انرژی'=>'energy_rating','منبع انرژی'=>'energy_source','کابل برق'=>'power_cable','مدل کالا / کد محصول'=>'model_code',
 ];
 public function handle(ProductAttributeManager $manager): int
 {
  $dry=(bool)$this->option('dry-run'); $stats=['scanned'=>0,'eligible'=>0,'migrated'=>0,'skipped'=>0,'unmatched'=>0,'suspicious'=>0];
  Product::query()->with(['category','attributeValues'])->chunkById(100,function($products) use($manager,$dry,&$stats): void {
   foreach($products as $product){
    $stats['scanned']++;
    $type=$this->categoryType($product);
    if(!$type || !is_array($product->specifications) || $product->specifications===[]){$stats['skipped']++;continue;}
    $legacy=$product->specifications; $rows=[];
    foreach($legacy as $key=>$raw){
     $value=is_array($raw)?($raw['value']??null):$raw;
     $label=is_array($raw)?trim((string)($raw['label']??'')):(string)$key;
     $legacyKey=isset($this->maps[$type][$key])?$key:($this->labels[$label]??null);
     if ($type==='hood' && $label==='جنس بدنه') $legacyKey='body_material';
     if(!$legacyKey || !isset($this->maps[$type][$legacyKey])) { $stats['unmatched']++; $this->warn("Product #{$product->id}: no exact attribute mapping for {$label}."); continue; }
     if(!is_scalar($value) || trim((string)$value)==='') continue;
     $attributeKey=$this->maps[$type][$legacyKey];
     $attribute=ProductAttribute::where('key',$attributeKey)->with('options')->first();
     if(!$attribute){$stats['unmatched']++;$this->warn("Product #{$product->id}: attribute {$attributeKey} is not seeded.");continue;}
     if ($product->attributeValues->contains('attribute_id', $attribute->id)) { $stats['skipped']++; continue; }
     $text=trim((string)$value);
     if($attributeKey==='width' && (int)$this->digits($text)===50 && $this->legacyBasinCount($legacy)>=2){
      $stats['suspicious']++; $this->warn("Product #{$product->id} ({$product->sku}): width=50 with multiple basins; skipped for manual review."); continue;
     }
     $input=$this->convert($attribute,$text);
     if($input===null){$stats['skipped']++;$this->warn("Product #{$product->id}: unmatched value for {$attributeKey} — {$text}");continue;}
     $rows[$attribute->id]=$input;
    }
    if($rows===[]){$stats['skipped']++;continue;}
    $stats['eligible']++;
    if(!$dry){DB::transaction(fn() => $manager->sync($product,$rows));$stats['migrated']++;}
   }
  });
  $this->table(['Metric','Count'],array_map(fn($key,$value)=>[$key,$value],array_keys($stats),array_values($stats)));
  $this->info($dry?'Dry-run completed. No product values were changed.':'Migration completed. Legacy JSON was preserved.');
  return self::SUCCESS;
 }
 private function categoryType(Product $product): ?string {
  $slug=Str::lower((string)$product->category?->slug); $name=(string)$product->category?->name;
  if(str_contains($slug,'sink')||str_contains($name,'سینک')) return 'sink';
  if(str_contains($slug,'hood')||str_contains($name,'هود')) return 'hood';
  if(str_contains($slug,'gas')||str_contains($name,'گاز')||str_contains($name,'اجاق')) return 'gas';
  return null;
 }
 private function convert(ProductAttribute $attribute,string $value): ?array {
  if(in_array($attribute->type,['select','multiselect'],true)){
   $parts=$attribute->type==='multiselect'?preg_split('/[،,؛;]+/u',$value):[$value];
   $ids=[];
   foreach($parts as $part){
    $part=trim($part); $option=$attribute->options->first(fn($option)=>mb_strtolower($option->label)===mb_strtolower($part)||mb_strtolower($option->value)===mb_strtolower($part));
    if(!$option) return null; $ids[]=$option->id;
   }
   return $attribute->type==='multiselect'?['option_ids'=>$ids]:['option_id'=>$ids[0]??null];
  }
  if($attribute->type==='number'){
   $number=$this->digits($value);
   if(!is_numeric($number)) return null;
   return ['value'=>$number];
  }
  if($attribute->type==='boolean'){
   $normalized=mb_strtolower($value);
   if(in_array($normalized,['بله','دارد','true','1','yes'],true)) return ['value'=>'1'];
   if(in_array($normalized,['خیر','ندارد','false','0','no'],true)) return ['value'=>'0'];
   return null;
  }
  return ['value'=>$value];
 }
 private function legacyBasinCount(array $legacy): int {
  $value=$legacy['basin_count']??null;
  if(is_array($value)) $value=$value['value']??null;
  if($value===null){
   foreach($legacy as $key=>$row){
    if(is_array($row) && trim((string)($row['label']??''))==='تعداد لگن'){ $value=$row['value']??null; break; }
   }
  }
  $normalized=$this->digits((string)$value);
  return is_numeric($normalized)?(int)$normalized:0;
 }
 private function digits(string $value): string {
  return strtr($value,['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
 }
 private function warn(string $message): void { $this->line('<comment>'.$message.'</comment>'); }
}
