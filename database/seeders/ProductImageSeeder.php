<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageSeeder extends Seeder
{
    /**
     * Make compact, locally stored vector previews for the 50 preview SKUs.
     * No third-party hotlinks or copyrighted product imagery are required.
     * When staff upload a real image in the dashboard, rerunning this seeder
     * preserves that uploaded image instead of replacing it.
     */
    public function run(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $products = Product::query()
            ->where('sku', 'like', 'FZ-SEED-%')
            ->with(['category', 'images'])
            ->orderBy('sku')
            ->get();

        foreach ($products as $product) {
            $existingImage = $product->images->first(
                fn (ProductImage $image) => $image->sort_order === 0
            );

            // Do not overwrite a genuine image uploaded through the admin.
            if ($existingImage && ! Str::startsWith($existingImage->path, 'seed/farzin/')) {
                continue;
            }

            $isSink = $product->category?->slug === 'sinks';
            $number = (int) preg_replace('/\D+/', '', (string) Str::afterLast($product->sku, '-'));
            $number = max(1, $number);

            $palette = [
                ['#dbeafe', '#d9f7f2', '#2563eb', '#122945'],
                ['#e0e7ff', '#fce7f3', '#6558c7', '#172b43'],
                ['#cffafe', '#e0f2fe', '#0891b2', '#12324c'],
                ['#ffedd5', '#fee2e2', '#ea580c', '#3b2734'],
                ['#dcfce7', '#dbeafe', '#15803d', '#17384a'],
                ['#f3e8ff', '#e0f2fe', '#9333ea', '#202e4b'],
                ['#fef3c7', '#dbeafe', '#b45309', '#1f354a'],
                ['#e2e8f0', '#cffafe', '#475569', '#17283b'],
                ['#ffe4e6', '#dbeafe', '#e11d48', '#3b2539'],
                ['#d1fae5', '#fef3c7', '#0f766e', '#213648'],
            ][$number % 10];

            [$start, $end, $accent, $metal] = $palette;
            $slug = Str::slug($product->sku);
            $path = "seed/farzin/{$slug}.svg";
            $svg = $isSink
                ? $this->sinkArtwork($product->sku, $start, $end, $accent, $metal, $number)
                : $this->hoodArtwork($product->sku, $start, $end, $accent, $metal, $number);

            Storage::disk('public')->put($path, $svg);

            ProductImage::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sort_order' => 0,
                ],
                [
                    'path' => $path,
                    'alt' => $product->name,
                    'is_primary' => true,
                ]
            );
        }
    }

    private function hoodArtwork(string $sku, string $start, string $end, string $accent, string $metal, int $number): string
    {
        $id = Str::slug($sku);
        $safeSku = htmlspecialchars($sku, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $style = $number % 4;

        $hood = match ($style) {
            0 => '<path d="M322 190H478V382L588 443H212L322 382Z" fill="url(#metal)" stroke="#ffffff" stroke-opacity=".68" stroke-width="4" stroke-linejoin="round"/><path d="M218 448H582L625 516H175Z" fill="url(#glass)" stroke="#ffffff" stroke-opacity=".76" stroke-width="4" stroke-linejoin="round"/><path d="M235 468H565L583 499H217Z" fill="#15263a" opacity=".82"/>',
            1 => '<rect x="332" y="194" width="136" height="220" rx="9" fill="url(#metal)" stroke="#ffffff" stroke-opacity=".7" stroke-width="4"/><path d="M216 414H584L630 485Q632 500 612 500H188Q168 500 170 485Z" fill="url(#glass)" stroke="#ffffff" stroke-opacity=".8" stroke-width="4"/><path d="M222 474H578L590 488H210Z" fill="#1d3045"/>',
            2 => '<path d="M260 246H540V392H260Z" rx="8" fill="url(#metal)" stroke="#ffffff" stroke-opacity=".7" stroke-width="4"/><path d="M200 392H600V466H200Z" fill="url(#glass)" stroke="#ffffff" stroke-opacity=".8" stroke-width="4"/><path d="M232 438H568M246 450H554M260 462H540" stroke="#9baec1" stroke-width="5" stroke-linecap="round"/>',
            default => '<rect x="346" y="190" width="108" height="205" rx="8" fill="url(#metal)" stroke="#ffffff" stroke-opacity=".7" stroke-width="4"/><path d="M260 395H540L613 454L575 492H225L187 454Z" fill="url(#glass)" stroke="#ffffff" stroke-opacity=".8" stroke-width="4" stroke-linejoin="round"/><path d="M242 454H558L577 475H223Z" fill="#1d3045"/>',
        };

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 900" role="img" aria-label="Illustration preview for Farzin kitchen hood {$safeSku}">
<defs>
 <linearGradient id="bg-{$id}" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$start}"/><stop offset="1" stop-color="{$end}"/></linearGradient>
 <linearGradient id="metal" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#fbfdff"/><stop offset=".27" stop-color="#aebdcc"/><stop offset=".52" stop-color="#f8fbff"/><stop offset=".77" stop-color="#74879b"/><stop offset="1" stop-color="#eaf0f5"/></linearGradient>
 <linearGradient id="glass" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$metal}"/><stop offset=".55" stop-color="#24364a"/><stop offset="1" stop-color="#081827"/></linearGradient>
 <linearGradient id="accent-{$id}" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$accent}"/><stop offset="1" stop-color="{$end}"/></linearGradient>
 <filter id="shadow-{$id}" x="-40%" y="-40%" width="180%" height="190%"><feDropShadow dx="0" dy="22" stdDeviation="19" flood-color="#16324b" flood-opacity=".22"/></filter>
 <pattern id="grid-{$id}" width="38" height="38" patternUnits="userSpaceOnUse"><path d="M38 0H0V38" fill="none" stroke="#fff" stroke-opacity=".22" stroke-width="1"/></pattern>
</defs>
<rect width="800" height="900" rx="38" fill="url(#bg-{$id})"/>
<rect width="800" height="900" rx="38" fill="url(#grid-{$id})" opacity=".55"/>
<circle cx="666" cy="176" r="154" fill="#fff" opacity=".20"/>
<circle cx="112" cy="722" r="185" fill="url(#accent-{$id})" opacity=".18"/>
<path d="M60 612C192 564 254 654 400 615S650 565 750 626" fill="none" stroke="#fff" stroke-opacity=".54" stroke-width="2"/>
<text x="58" y="75" font-family="Arial,sans-serif" font-size="17" font-weight="700" letter-spacing="4" fill="{$metal}">FARZIN / HOOD</text>
<text x="60" y="112" font-family="Arial,sans-serif" font-size="13" letter-spacing="2" fill="{$metal}" opacity=".7">KITCHEN EQUIPMENT PREVIEW</text>
<g filter="url(#shadow-{$id})">
 <ellipse cx="400" cy="566" rx="226" ry="32" fill="#17314a" opacity=".13"/>
 {$hood}
</g>
<circle cx="400" cy="443" r="6" fill="{$accent}"/>
<circle cx="380" cy="443" r="3" fill="#eaf4ff" opacity=".9"/>
<circle cx="420" cy="443" r="3" fill="#eaf4ff" opacity=".9"/>
<rect x="58" y="744" width="684" height="1" fill="{$metal}" opacity=".35"/>
<text x="60" y="790" font-family="Arial,sans-serif" font-size="22" font-weight="700" fill="{$metal}">{$safeSku}</text>
<text x="60" y="822" font-family="Arial,sans-serif" font-size="13" letter-spacing="2" fill="{$metal}" opacity=".78">CATALOG ARTWORK — VERIFY MODEL DETAILS</text>
<rect x="618" y="770" width="122" height="34" rx="17" fill="url(#accent-{$id})"/>
<text x="679" y="792" text-anchor="middle" font-family="Arial,sans-serif" font-size="12" font-weight="700" fill="#fff">PREVIEW</text>
</svg>
SVG;
    }

    private function sinkArtwork(string $sku, string $start, string $end, string $accent, string $metal, int $number): string
    {
        $id = Str::slug($sku);
        $safeSku = htmlspecialchars($sku, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $doubleBowl = $number % 3 === 0;
        $basin = $doubleBowl
            ? '<rect x="186" y="326" width="428" height="200" rx="25" fill="url(#steel)" stroke="#ffffff" stroke-width="5"/><rect x="207" y="347" width="181" height="157" rx="18" fill="#34485b" stroke="#c7d5e3" stroke-width="5"/><rect x="414" y="347" width="179" height="157" rx="18" fill="#34485b" stroke="#c7d5e3" stroke-width="5"/><path d="M226 365H368M433 365H572" stroke="#8295a7" stroke-width="4" stroke-linecap="round"/>'
            : '<rect x="177" y="326" width="446" height="200" rx="25" fill="url(#steel)" stroke="#ffffff" stroke-width="5"/><rect x="204" y="347" width="392" height="157" rx="20" fill="#34485b" stroke="#c7d5e3" stroke-width="5"/><path d="M227 365H573M227 377H573" stroke="#8295a7" stroke-width="4" stroke-linecap="round"/>';
        $faucet = $number % 4 === 0
            ? '<path d="M476 330V273C476 227 448 211 412 211H384C360 211 345 230 345 252V267" fill="none" stroke="url(#steel)" stroke-width="21" stroke-linecap="round"/><path d="M345 258V282" stroke="#8da2b7" stroke-width="17" stroke-linecap="round"/>'
            : '<path d="M472 325V253C472 213 450 194 412 194H381C354 194 338 211 338 239V264" fill="none" stroke="url(#steel)" stroke-width="22" stroke-linecap="round"/><path d="M338 254V278" stroke="#8da2b7" stroke-width="17" stroke-linecap="round"/>';

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 900" role="img" aria-label="Illustration preview for Farzin kitchen sink {$safeSku}">
<defs>
 <linearGradient id="bg-{$id}" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$start}"/><stop offset="1" stop-color="{$end}"/></linearGradient>
 <linearGradient id="steel" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#f8fbff"/><stop offset=".23" stop-color="#8da0b4"/><stop offset=".45" stop-color="#fff"/><stop offset=".7" stop-color="#8799ac"/><stop offset="1" stop-color="#e5edf5"/></linearGradient>
 <linearGradient id="accent-{$id}" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$accent}"/><stop offset="1" stop-color="{$end}"/></linearGradient>
 <filter id="shadow-{$id}" x="-40%" y="-40%" width="180%" height="190%"><feDropShadow dx="0" dy="22" stdDeviation="19" flood-color="#16324b" flood-opacity=".22"/></filter>
 <pattern id="grid-{$id}" width="38" height="38" patternUnits="userSpaceOnUse"><path d="M38 0H0V38" fill="none" stroke="#fff" stroke-opacity=".22" stroke-width="1"/></pattern>
</defs>
<rect width="800" height="900" rx="38" fill="url(#bg-{$id})"/>
<rect width="800" height="900" rx="38" fill="url(#grid-{$id})" opacity=".55"/>
<circle cx="665" cy="188" r="155" fill="#fff" opacity=".2"/>
<circle cx="112" cy="722" r="180" fill="url(#accent-{$id})" opacity=".17"/>
<text x="58" y="75" font-family="Arial,sans-serif" font-size="17" font-weight="700" letter-spacing="4" fill="{$metal}">FARZIN / SINK</text>
<text x="60" y="112" font-family="Arial,sans-serif" font-size="13" letter-spacing="2" fill="{$metal}" opacity=".7">KITCHEN EQUIPMENT PREVIEW</text>
<g filter="url(#shadow-{$id})">
 <ellipse cx="400" cy="568" rx="245" ry="30" fill="#17314a" opacity=".13"/>
 <path d="M145 305L198 280H602L655 305L625 340H175Z" fill="url(#steel)" stroke="#fff" stroke-opacity=".9" stroke-width="5" stroke-linejoin="round"/>
 {$faucet}
 {$basin}
 <circle cx="400" cy="492" r="9" fill="#162a3e" stroke="#d7e2ec" stroke-width="4"/>
 <path d="M384 510H416" stroke="#d7e2ec" stroke-width="4" stroke-linecap="round"/>
</g>
<path d="M60 612C192 564 254 654 400 615S650 565 750 626" fill="none" stroke="#fff" stroke-opacity=".54" stroke-width="2"/>
<rect x="58" y="744" width="684" height="1" fill="{$metal}" opacity=".35"/>
<text x="60" y="790" font-family="Arial,sans-serif" font-size="22" font-weight="700" fill="{$metal}">{$safeSku}</text>
<text x="60" y="822" font-family="Arial,sans-serif" font-size="13" letter-spacing="2" fill="{$metal}" opacity=".78">CATALOG ARTWORK — VERIFY MODEL DETAILS</text>
<rect x="618" y="770" width="122" height="34" rx="17" fill="url(#accent-{$id})"/>
<text x="679" y="792" text-anchor="middle" font-family="Arial,sans-serif" font-size="12" font-weight="700" fill="#fff">PREVIEW</text>
</svg>
SVG;
    }
}
