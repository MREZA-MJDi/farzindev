<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\ContactSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ContactController extends Controller
{
    public function index(): View
    {
        $contactSetting = ContactSetting::query()
            ->where('is_active', true)
            ->first();

        $faqs = [
            // FAQ های static
        ];

        return view('contact.index', [
            'contactSetting' => $contactSetting,
            'faqs' => $faqs,
        ]);
    }

    public function store(
        StoreContactMessageRequest $request
    ): RedirectResponse {
        $data = $request->validated();
        $photo = $request->file('photo');
        unset($data['photo']);

        $attachmentPath = null;

        if ($photo) {
            $attachmentPath = $photo->store('contact-attachments', 'local');

            if (! $attachmentPath) {
                return back()
                    ->withInput($request->except('photo'))
                    ->withErrors([
                        'photo' => 'ذخیره تصویر انجام نشد؛ لطفاً دوباره تلاش کنید.',
                    ]);
            }
        }

        try {
            ContactMessage::query()->create([
                ...$data,
                'user_id' => auth()->id(),
                'callback_requested' => (bool) ($data['callback_requested'] ?? false),
                'attachment_path' => $attachmentPath,
                'status' => 'unread',
            ]);
        } catch (Throwable $exception) {
            if ($attachmentPath) {
                Storage::disk('local')->delete($attachmentPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('contact')
            ->with('success', 'پیام شما با موفقیت ارسال شد. در صورت درخواست تماس برگشتی، همکاران مجموعه با شما هماهنگ می‌کنند.');
    }
}
