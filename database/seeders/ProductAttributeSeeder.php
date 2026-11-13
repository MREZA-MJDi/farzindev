<?php
namespace Database\Seeders;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class ProductAttributeSeeder extends Seeder
{
 public function run(): void
 {
  $categoryFor = function (array $slugs, string $name, array $defaults): Category {
   $existing = Category::query()->where(function ($query) use ($slugs, $name): void {
    $query->whereIn('slug', $slugs)->orWhere('name', $name);
   })->first();
   return $existing ?: Category::create($defaults);
  };
  $categories=[
   'sink'=>$categoryFor(['sinks'],'سینک ظرفشویی',['slug'=>'sinks','name'=>'سینک ظرفشویی','description'=>'انواع سینک ظرفشویی','is_active'=>true,'sort_order'=>2]),
   'hood'=>$categoryFor(['hoods'],'هود آشپزخانه',['slug'=>'hoods','name'=>'هود آشپزخانه','description'=>'انواع هود آشپزخانه','is_active'=>true,'sort_order'=>1]),
   'gas'=>$categoryFor(['built-in-stove','gas-stoves','gas-hobs','built-in-gas','built-in-hobs','gas-stove'],'اجاق گاز صفحه‌ای',['slug'=>'built-in-stove','name'=>'اجاق گاز صفحه‌ای','description'=>'انواع اجاق گاز صفحه‌ای','is_active'=>true,'sort_order'=>3]),
  ];
  $definitions=[];
  $add=function(string $key,string $label,string $type,string $unit,string $group,array $options=[],string $help='') use (&$definitions): void {
   $definitions[$key]=compact('label','type','unit','group','options','help');
  };
  $add('installation','نحوه نصب','select','','نصب و ابعاد',['توکار','روکار']);
  $add('length','طول','number','cm','نصب و ابعاد');
  $add('width','عرض','number','cm','نصب و ابعاد');
  $add('cutout_dimensions','ابعاد برش کابینت','text','','نصب و ابعاد',[],'طول × عرض');
  $add('basin_count','تعداد لگن','number','عدد','لگن');
  $add('basin_depth','عمق لگن','number','cm','لگن');
  $add('basin_capacity','ظرفیت لگن','number','لیتر','لگن');
  $add('basin_direction','جهت لگن','select','','لگن',['راست','چپ']);
  $add('sink_material','جنس','select','','جنس و ساخت',['استیل','گرانیت']);
  $add('sheet_thickness','ضخامت ورق','number','mm','جنس و ساخت');
  $add('sink_design','نوع طراحی سینک','select','','جنس و ساخت',['باکسی']);
  $add('weight','وزن','number','kg','جنس و ساخت');
  $add('surface_finish','پرداخت سطح','select','','جنس و ساخت',['مات','براق']);
  $add('color','رنگ','text','','جنس و ساخت');
  $add('sink_accessories','اقلام همراه','multiselect','','اقلام همراه و ضمانت',['سیفون','آبکش','دفترچه راهنما','پیچ و اتصالات نصب']);
  $add('tap_holes','تعداد سوراخ شیرآلات','number','عدد','اقلام همراه و ضمانت');
  $add('warranty_months','ضمانت','number','ماه','اقلام همراه و ضمانت');
  $add('hood_type','نوع هود','select','','نوع و مدل',['مخفی','زیرکابینتی']);
  $add('hood_model','مدل هود','text','','نوع و مدل');
  $add('hood_body_material','جنس بدنه','select','','نوع و مدل',['استیل','فلز رنگ‌شده']);
  $add('switch_type','نوع کلید','select','','نوع و مدل',['لمسی','فشاری']);
  $add('hood_size','سایز','number','cm','ابعاد');
  $add('hood_length','طول','number','cm','ابعاد');
  $add('hood_width','عرض','number','cm','ابعاد');
  $add('hood_height','ارتفاع یا عمق','number','cm','ابعاد');
  $add('suction_capacity','حجم مکش','number','متر مکعب بر ساعت','عملکرد');
  $add('light_count','تعداد لامپ','number','عدد','عملکرد');
  $add('motor_count','تعداد موتور','number','عدد','عملکرد');
  $add('noise_level','میزان صدا','number','dB','عملکرد');
  $add('air_outlet','نوع خروج هوا','select','','عملکرد',['خروج هوا به بیرون','بازچرخشی']);
  $add('air_outlet_diameter','قطر خروجی هوا','number','mm','عملکرد');
  $add('filter_material','جنس فیلتر','text','','عملکرد');
  $add('hood_accessories','اقلام همراه','multiselect','','اقلام همراه و ضمانت',['دفترچه راهنما']);
  $add('gas_model','مدل کالا','text','','مدل و ابعاد');
  $add('gas_length','طول','number','cm','مدل و ابعاد');
  $add('gas_width','عرض','number','cm','مدل و ابعاد');
  $add('gas_height','ارتفاع','number','cm','مدل و ابعاد');
  $add('gas_size_range','محدوده سایز','select','','مدل و ابعاد',['۸۵','بزرگ‌تر از ۸۵']);
  $add('gas_weight','وزن','number','kg','مدل و ابعاد');
  $add('burner_count','تعداد شعله','number','عدد','شعله و عملکرد');
  $add('burner_position','محل شعله پلوپز','select','','شعله و عملکرد',['وسط']);
  $add('energy_source','منبع انرژی','select','','شعله و عملکرد',['گاز شهری']);
  $add('energy_rating','رده مصرف انرژی','select','','شعله و عملکرد',['A']);
  $add('design_model','مدل طراحی','select','','شعله و عملکرد',['فندک اتوماتیک']);
  $add('lighting_tool','ابزار روشنایی','text','','شعله و عملکرد');
  $add('safety_system','سیستم ایمنی','text','','شعله و عملکرد');
  $add('convert_lpg','قابلیت تبدیل به گاز مایع','boolean','','شعله و عملکرد');
  $add('surface_material','جنس صفحه','select','','بدنه و قطعات',['شیشه']);
  $add('glass_type','نوع شیشه','select','','بدنه و قطعات',['سکوریت']);
  $add('surface_thickness','ضخامت صفحه','number','mm','بدنه و قطعات');
  $add('grate_material','جنس شبکه','select','','بدنه و قطعات',['چدن']);
  $add('valve_type','نوع شیر','text','','بدنه و قطعات');
  $add('burner_type','نوع سرشعله','text','','بدنه و قطعات');
  $add('knob_type','نوع ولوم','select','','بدنه و قطعات',['باکالیت']);
  $add('power_cable','کابل','text','','بدنه و قطعات');
  $add('gas_notes','سایر توضیحات','textarea','','ویژگی ویژه و اقلام');
  $add('gas_accessories','سایر اقلام همراه','multiselect','','ویژگی ویژه و اقلام',['دفترچه راهنما','شلنگ گاز','۲ عدد بست']);
  $add('manufacturing_country','محل ساخت','select','','مشخصات مشترک',['ساخت ایران']);

  $map=[
   'sink'=>[
    'installation'=>[1,1,1,1],'length'=>[1,0,0,1],'width'=>[1,0,1,0],'cutout_dimensions'=>[0,1,0,0],
    'basin_count'=>[1,1,1,1],'basin_depth'=>[1,0,0,1],'basin_capacity'=>[0,1,0,0],'basin_direction'=>[0,1,0,0],
    'sink_material'=>[1,1,1,1],'sheet_thickness'=>[0,1,0,0],'sink_design'=>[0,1,0,0],'weight'=>[0,1,0,0],
    'surface_finish'=>[0,1,0,0],'color'=>[0,0,0,0],'sink_accessories'=>[0,0,0,0],'tap_holes'=>[0,0,0,0],'warranty_months'=>[0,0,0,0],
    'manufacturing_country'=>[0,0,0,0]
   ],
   'hood'=>[
    'hood_type'=>[1,1,1,1],'hood_model'=>[1,0,0,0],'hood_body_material'=>[1,0,1,0],'switch_type'=>[1,0,1,0],
    'hood_size'=>[1,0,1,0],'hood_length'=>[1,0,0,0],'hood_width'=>[1,0,0,0],'hood_height'=>[0,0,0,0],
    'suction_capacity'=>[1,0,0,0],'light_count'=>[1,0,0,0],'motor_count'=>[0,1,0,0],'noise_level'=>[0,1,0,0],
    'air_outlet'=>[0,1,0,0],'air_outlet_diameter'=>[0,0,0,0],'filter_material'=>[0,0,0,0],
    'hood_accessories'=>[0,0,0,0],'warranty_months'=>[0,0,0,0],'manufacturing_country'=>[0,0,0,0],'color'=>[0,0,0,0]
   ],
   'gas'=>[
    'gas_model'=>[1,0,0,0],'gas_length'=>[1,0,0,0],'gas_width'=>[1,0,0,0],'gas_height'=>[1,0,0,0],
    'gas_size_range'=>[0,1,0,0],'gas_weight'=>[0,0,0,0],'burner_count'=>[1,1,1,1],'burner_position'=>[0,1,0,0],
    'energy_source'=>[1,1,1,1],'energy_rating'=>[0,1,0,0],'design_model'=>[0,1,0,0],'lighting_tool'=>[0,1,0,0],
    'safety_system'=>[0,1,0,0],'convert_lpg'=>[0,0,0,0],'surface_material'=>[1,0,1,0],'glass_type'=>[0,1,0,0],
    'surface_thickness'=>[0,0,0,0],'grate_material'=>[0,1,0,0],'valve_type'=>[0,0,0,0],'burner_type'=>[0,0,0,0],
    'knob_type'=>[0,1,0,0],'power_cable'=>[0,0,0,0],'gas_notes'=>[0,0,0,0],'gas_accessories'=>[0,0,0,0],
    'warranty_months'=>[0,0,0,0],'manufacturing_country'=>[0,0,0,0],'color'=>[0,0,0,0]
   ]
  ];
  $keySpecs=['sink'=>['width','basin_count','sink_material'],'hood'=>['hood_type','hood_size','suction_capacity'],'gas'=>['burner_count','energy_source']];
  foreach($definitions as $key=>$definition){
   $attribute=ProductAttribute::updateOrCreate(['key'=>$key],['label'=>$definition['label'],'type'=>$definition['type'],'unit'=>$definition['unit']?:null,'group'=>$definition['group'],'help_text'=>$definition['help']?:null]);
   foreach($definition['options'] as $index=>$label) AttributeOption::updateOrCreate(['attribute_id'=>$attribute->id,'value'=>Str::slug($label,'-') ?: 'option-'.$index],['label'=>$label,'sort_order'=>$index]);
  }
  foreach($map as $type=>$attributes){
   $category=$categories[$type];
   foreach($attributes as $key=>$flags){
    $attribute=ProductAttribute::where('key',$key)->firstOrFail();
    $attribute->categories()->syncWithoutDetaching([$category->id=>[
     'sort_order'=>array_search($key,array_keys($attributes),true),
     'is_required'=>(bool)$flags[0],'is_recommended'=>(bool)$flags[1],
     'is_filterable'=>(bool)$flags[2],'is_key_spec'=>in_array($key,$keySpecs[$type],true),
    ]]);
    $attribute->categories()->updateExistingPivot($category->id,[
     'sort_order'=>array_search($key,array_keys($attributes),true),
     'is_required'=>(bool)$flags[0],'is_recommended'=>(bool)$flags[1],
     'is_filterable'=>(bool)$flags[2],'is_key_spec'=>in_array($key,$keySpecs[$type],true),
    ]);
   }
  }
 }
}