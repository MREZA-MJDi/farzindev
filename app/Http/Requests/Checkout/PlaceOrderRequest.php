<?php

namespace App\Http\Requests\Checkout;

use App\Models\Province;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $provinceId = filter_var($this->input('province_id'), FILTER_VALIDATE_INT) ?: null;
        $cityId = filter_var($this->input('city_id'), FILTER_VALIDATE_INT) ?: null;

        $province = $provinceId
            ? Province::query()->find($provinceId)
            : null;

        // Resolve both labels on the server. Browser-submitted province/city
        // text is never trusted or persisted to the order.
        $city = $province && $cityId
            ? $province->cities()->whereKey($cityId)->first()
            : null;

        $this->merge([
            'province' => $province?->name,
            'city' => $city?->name,
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'phone' => [
                'required',
                'string',
                'regex:/^09\d{9}$/',
            ],

            'email' => [
                'required',
                'email:rfc',
                'max:255',
            ],

            'province_id' => [
                'required',
                'integer',
                Rule::exists('provinces', 'id'),
            ],

            'city_id' => [
                'required',
                'integer',
                Rule::exists('cities', 'id')->where(
                    fn ($query) => $query->where('province_id', (int) $this->input('province_id'))
                ),
            ],

            // These labels are populated by prepareForValidation() from the
            // selected IDs and keep the existing order/address snapshot schema.
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],

            'address' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],

            'postal_code' => [
                'required',
                'digits:10',
            ],

            'unit' => [
                'nullable',
                'string',
                'max:50',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'phone' => 'شماره موبایل',
            'email' => 'ایمیل',
            'province_id' => 'استان',
            'city_id' => 'شهر',
            'province' => 'استان',
            'city' => 'شهر',
            'address' => 'آدرس',
            'postal_code' => 'کد پستی',
            'unit' => 'واحد',
            'notes' => 'توضیحات',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' =>
                'وارد کردن نام الزامی است.',

            'last_name.required' =>
                'وارد کردن نام خانوادگی الزامی است.',

            'phone.required' =>
                'شماره موبایل را وارد کنید.',

            'phone.regex' =>
                'شماره موبایل باید به شکل 09123456789 باشد.',

            'email.required' =>
                'ایمیل را وارد کنید.',

            'email.email' =>
                'فرمت ایمیل صحیح نیست.',

            'province_id.required' =>
                'استان را انتخاب کنید.',

            'province_id.exists' =>
                'استان انتخاب‌شده معتبر نیست.',

            'city_id.required' =>
                'شهر را انتخاب کنید.',

            'city_id.exists' =>
                'شهر انتخاب‌شده متعلق به استان انتخاب‌شده نیست.',

            'address.required' =>
                'آدرس کامل را وارد کنید.',

            'address.min' =>
                'آدرس واردشده خیلی کوتاه است.',

            'postal_code.required' =>
                'کد پستی را وارد کنید.',

            'postal_code.digits' =>
                'کد پستی باید دقیقاً ۱۰ رقم باشد.',
        ];
    }
}
