<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AttributeOption;
use App\Models\ProductAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class ProductAttributeController extends Controller
{
 public function index(): View {
  $attributes=ProductAttribute::query()->with('options')->withCount(['categories','values'])->orderBy('group')->orderBy('label')->paginate(30);
  return view('admin.product-attributes.index',compact('attributes'));
 }
 public function create(): View { return view('admin.product-attributes.form',['attribute'=>new ProductAttribute()]); }
 public function store(Request $request): RedirectResponse {
  $data=$this->validated($request);
  DB::transaction(function() use($data): void {
   $attribute=ProductAttribute::create(collect($data)->except('options')->all());
   $this->syncOptions($attribute,$data['options']??'');
  });
  return redirect()->route('admin.product-attributes.index')->with('success','ویژگی فنی ایجاد شد.');
 }
 public function edit(ProductAttribute $productAttribute): View {
  $productAttribute->load('options');
  return view('admin.product-attributes.form',['attribute'=>$productAttribute]);
 }
 public function update(Request $request,ProductAttribute $productAttribute): RedirectResponse {
  $data=$this->validated($request,$productAttribute);
  DB::transaction(function() use($productAttribute,$data): void {
   $productAttribute->update(collect($data)->except('options')->all());
   $this->syncOptions($productAttribute,$data['options']??'');
  });
  return redirect()->route('admin.product-attributes.index')->with('success','ویژگی فنی بروزرسانی شد.');
 }
 public function destroy(ProductAttribute $productAttribute): RedirectResponse {
  if($productAttribute->values()->exists()) return back()->with('error','این ویژگی مقدار ثبت‌شده برای محصول دارد؛ ابتدا مقادیر محصولات را منتقل یا پاک کنید.');
  $productAttribute->delete();
  return redirect()->route('admin.product-attributes.index')->with('success','ویژگی حذف شد.');
 }
 private function validated(Request $request,?ProductAttribute $attribute=null): array {
  return $request->validate([
   'key'=>['required','alpha_dash:ascii','max:100',Rule::unique('attributes','key')->ignore($attribute)],
   'label'=>['required','string','max:160'],
   'type'=>['required',Rule::in(['text','textarea','number','select','multiselect','boolean'])],
   'unit'=>['nullable','string','max:40'],
   'group'=>['nullable','string','max:120'],
   'help_text'=>['nullable','string','max:1000'],
   'options'=>['nullable','string','max:5000'],
  ],[
   'key.required'=>'شناسه لاتین ویژگی الزامی است.','key.alpha_dash'=>'شناسه فقط می‌تواند شامل حروف لاتین، عدد و خط تیره باشد.',
   'key.unique'=>'این شناسه قبلاً ثبت شده است.','label.required'=>'عنوان فارسی ویژگی الزامی است.',
   'type.in'=>'نوع داده انتخاب‌شده معتبر نیست.','options.max'=>'فهرست گزینه‌ها بیش از حد طولانی است.',
  ]);
 }
 private function syncOptions(ProductAttribute $attribute,string $raw): void {
  $labels=collect(preg_split('/\r\n|\r|\n/',$raw) ?: [])->map(fn($v)=>trim($v))->filter()->unique()->values();
  if(!in_array($attribute->type,['select','multiselect'],true)) {
   if($attribute->options()->exists() && $attribute->options()->whereHas('values')->exists()) {
    throw ValidationException::withMessages(['options'=>'گزینه‌های این ویژگی هنوز در محصولات استفاده می‌شوند.']);
   }
   $attribute->options()->delete();
   return;
  }
  if($labels->isEmpty()) throw ValidationException::withMessages(['options'=>'برای ویژگی انتخابی حداقل یک گزینه تعریف کنید.']);
  $existing=$attribute->options()->get();
  foreach($existing as $option) {
   if(!$labels->contains($option->label) && $option->values()->exists()) {
    throw ValidationException::withMessages(['options'=>'گزینه «'.$option->label.'» در محصولات استفاده شده و قابل حذف نیست.']);
   }
  }
  $keep=[];
  foreach($labels as $index=>$label) {
   $existingOption=$existing->firstWhere('label',$label);
   if($existingOption) {
    $existingOption->update(['sort_order'=>$index]);
    $keep[]=$existingOption->value;
    continue;
   }
   $value='opt-'.substr(sha1(mb_strtolower($label)),0,16);
   $option=AttributeOption::updateOrCreate(['attribute_id'=>$attribute->id,'value'=>$value],['label'=>$label,'sort_order'=>$index]);
   $keep[]=$option->value;
  }
  $attribute->options()->whereNotIn('value',$keep)->whereDoesntHave('values')->delete();
 }
}
