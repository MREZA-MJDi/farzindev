@extends('layouts.app')

@php
    $legalCopy = $storefrontPageContent ?? [];
    $privacyTitle = trim((string) ($legalCopy['page_privacy_title'] ?? '')) ?: 'حریم خصوصی';
    $privacyLead = trim((string) ($legalCopy['page_privacy_lead'] ?? ''));
    $privacySections = collect(range(1, 4))->map(function (int $index) use ($legalCopy): array {
        return [
            'title' => trim((string) ($legalCopy["page_privacy_section_{$index}_title"] ?? '')),
            'body' => trim((string) ($legalCopy["page_privacy_section_{$index}_body"] ?? '')),
        ];
    })->filter(fn (array $section): bool => $section['title'] !== '' || $section['body'] !== '');
@endphp

@section('title', $privacyTitle . ' | فرجی‌هوم')
@section('description', $privacyLead)
@section('canonical', route('privacy'))

@section('content')
<section class="fz-legal-page">
    <div class="fz-legal-page__hero">
        <div class="fz-container">
            <p class="fz-eyebrow"><span></span> FARAJI HOME / CUSTOMER INFORMATION</p>
            <h1>{{ $privacyTitle }}</h1>
            @if($privacyLead !== '')<p>{{ $privacyLead }}</p>@endif
        </div>
    </div>
    <div class="fz-container fz-legal-page__body">
        @forelse($privacySections as $section)
            <article>
                @if($section['title'] !== '')<h2>{{ $section['title'] }}</h2>@endif
                @if($section['body'] !== '')<p>{{ $section['body'] }}</p>@endif
            </article>
        @empty
            <article><p>محتوای این صفحه هنوز ثبت نشده است.</p></article>
        @endforelse
        <div class="fz-legal-page__contact">
            <strong>{{ $legalCopy['page_privacy_contact_title'] ?? 'درخواست یا پرسشی درباره اطلاعات حساب دارید؟' }}</strong>
            <a href="{{ route('contact') }}">{{ $legalCopy['page_privacy_contact_cta'] ?? 'ارتباط با فروشگاه' }} <span aria-hidden="true">←</span></a>
        </div>
    </div>
</section>
@endsection
