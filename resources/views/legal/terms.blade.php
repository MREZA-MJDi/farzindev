@extends('layouts.app')

@php
    $legalCopy = $storefrontPageContent ?? [];
    $termsTitle = trim((string) ($legalCopy['page_terms_title'] ?? '')) ?: 'قوانین و شرایط استفاده';
    $termsLead = trim((string) ($legalCopy['page_terms_lead'] ?? ''));
    $termsSections = collect(range(1, 4))->map(function (int $index) use ($legalCopy): array {
        return [
            'title' => trim((string) ($legalCopy["page_terms_section_{$index}_title"] ?? '')),
            'body' => trim((string) ($legalCopy["page_terms_section_{$index}_body"] ?? '')),
        ];
    })->filter(fn (array $section): bool => $section['title'] !== '' || $section['body'] !== '');
@endphp

@section('title', $termsTitle . ' | فرجی‌هوم')
@section('description', $termsLead)
@section('canonical', route('terms'))

@section('content')
<section class="fz-legal-page">
    <div class="fz-legal-page__hero">
        <div class="fz-container">
            <p class="fz-eyebrow"><span></span> FARAJI HOME / CUSTOMER INFORMATION</p>
            <h1>{{ $termsTitle }}</h1>
            @if($termsLead !== '')<p>{{ $termsLead }}</p>@endif
        </div>
    </div>
    <div class="fz-container fz-legal-page__body">
        @forelse($termsSections as $section)
            <article>
                @if($section['title'] !== '')<h2>{{ $section['title'] }}</h2>@endif
                @if($section['body'] !== '')<p>{{ $section['body'] }}</p>@endif
            </article>
        @empty
            <article><p>محتوای این صفحه هنوز ثبت نشده است.</p></article>
        @endforelse
        <div class="fz-legal-page__contact">
            <strong>{{ $legalCopy['page_terms_contact_title'] ?? 'پرسشی درباره شرایط خرید دارید؟' }}</strong>
            <a href="{{ route('contact') }}">{{ $legalCopy['page_terms_contact_cta'] ?? 'ارتباط با فرجی‌هوم' }} <span aria-hidden="true">←</span></a>
        </div>
    </div>
</section>
@endsection
