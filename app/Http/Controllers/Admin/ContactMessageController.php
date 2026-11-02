<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ContactMessageController extends Controller
{
    public function index(): View
    {
        $messages = ContactMessage::query()
            ->latest()
            ->paginate(15);

        return view('admin.contact-messages.index', [
            'messages' => $messages,
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        if ($contactMessage->status === 'unread') {
            $contactMessage->update([
                'status' => 'read',
            ]);
        }

        return view('admin.contact-messages.show', [
            'contactMessage' => $contactMessage,
        ]);
    }

    public function attachment(ContactMessage $contactMessage)
    {
        $path = $contactMessage->attachment_path;

        abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';

        return Storage::disk('local')->download(
            $path,
            'contact-' . $contactMessage->id . '.' . $extension
        );
    }

    public function markAsRead(
        ContactMessage $contactMessage
    ): RedirectResponse {
        $contactMessage->update([
            'status' => 'read',
        ]);

        return back()->with(
            'success',
            'پیام به عنوان خوانده‌شده علامت خورد.'
        );
    }

    public function destroy(
        ContactMessage $contactMessage
    ): RedirectResponse {
        $contactMessage->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with(
                'success',
                'پیام با موفقیت حذف شد.'
            );
    }
}
