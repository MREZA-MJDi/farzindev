<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class CategoryAttributeController extends Controller
{
 public function edit(Category $category): View {
  $attributes=ProductAttribute::query()->with('options')->orderBy('group')->orderBy('label')->get();
  $selected=$category->attributes()->get()->keyBy('id');
  return view('admin.categories.attributes',compact('category','attributes','selected'));
 }
 public function update(Request $request,Category $category): RedirectResponse {
  $data=$request->validate([
   'attributes'=>['nullable','array'],
   'attributes.*.enabled'=>['nullable','boolean'],
   'attributes.*.sort_order'=>['nullable','integer','min:0','max:9999'],
   'attributes.*.is_required'=>['nullable','boolean'],
   'attributes.*.is_recommended'=>['nullable','boolean'],
   'attributes.*.is_filterable'=>['nullable','boolean'],
   'attributes.*.is_key_spec'=>['nullable','boolean'],
  ]);
  $selected=collect($data['attributes']??[])->filter(fn($row)=>filter_var($row['enabled']??false,FILTER_VALIDATE_BOOLEAN));
  if($selected->filter(fn($row)=>filter_var($row['is_key_spec']??false,FILTER_VALIDATE_BOOLEAN))->count()>3) {
   return back()->withInput()->withErrors(['attributes'=>'حداکثر سه مشخصه کلیدی برای هر دسته‌بندی مجاز است.']);
  }
  $ids=$selected->keys()->map(fn($id)=>(int)$id)->all();
  $existing=ProductAttribute::whereIn('id',$ids)->count();
  if($existing!==count($ids)) return back()->withInput()->withErrors(['attributes'=>'یکی از ویژگی‌های انتخاب‌شده معتبر نیست.']);
  $currentIds=$category->attributes()->pluck('attributes.id')->map(fn($id)=>(int)$id);
  $removedIds=$currentIds->diff($ids);
  if($removedIds->isNotEmpty() && ProductAttribute::query()->whereIn('id',$removedIds)
      ->whereHas('values.product',fn($products)=>$products->where('category_id',$category->id))->exists()) {
   return back()->withInput()->withErrors(['attributes'=>'یکی از ویژگی‌های حذف‌شده در محصولات این دسته مقدار ثبت‌شده دارد؛ ابتدا مقادیر محصولات را منتقل یا پاک کنید.']);
  }

  $sync=[];
  foreach($selected as $id=>$row) $sync[$id]=[
   'sort_order'=>(int)($row['sort_order']??0),
   'is_required'=>filter_var($row['is_required']??false,FILTER_VALIDATE_BOOLEAN),
   'is_recommended'=>filter_var($row['is_recommended']??false,FILTER_VALIDATE_BOOLEAN),
   'is_filterable'=>filter_var($row['is_filterable']??false,FILTER_VALIDATE_BOOLEAN),
   'is_key_spec'=>filter_var($row['is_key_spec']??false,FILTER_VALIDATE_BOOLEAN),
  ];
  DB::transaction(fn () => $category->attributes()->sync($sync));
  return redirect()->route('admin.categories.attributes.edit',$category)->with('success','ویژگی‌های دسته‌بندی ذخیره شدند.');
 }
}
