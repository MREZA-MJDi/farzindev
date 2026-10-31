<?php

namespace App\Services\Seo;

use App\Models\BlogPost;

class BlogSeoAnalyzer
{
    /**
     * A deterministic editorial checklist, not a promise of Google rankings.
     *
     * @return array{score: int, checks: array<int, array{key: string, label: string, passed: bool, detail: string}>, word_count: int, reading_time: int}
     */
    public function analyze(BlogPost $post): array
    {
        $title = trim((string) ($post->meta_title ?: $post->title));
        $description = trim((string) ($post->meta_description ?: $post->excerpt));
        $keyword = $this->normalize((string) $post->focus_keyword);
        $slug = $this->normalize(str_replace('-', ' ', (string) $post->slug));
        $content = (string) ($post->body_html ?: $post->body);
        $plainText = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalizedContent = $this->normalize($plainText);
        $words = preg_split('/\s+/u', trim($normalizedContent), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $wordCount = count($words);
        $firstWords = implode(' ', array_slice($words, 0, 100));
        $html = (string) $post->body_html;

        $checks = [];
        $checks[] = $this->check(
            'title_length',
            'طول عنوان SEO',
            mb_strlen($title) >= 25 && mb_strlen($title) <= 65,
            'عنوان پیشنهادی معمولاً بین ۲۵ تا ۶۵ نویسه باشد.'
        );
        $checks[] = $this->check(
            'description_length',
            'توضیحات متا',
            mb_strlen($description) >= 70 && mb_strlen($description) <= 165,
            'توضیح یکتا و مفید با طول تقریبی ۷۰ تا ۱۶۵ نویسه بنویسید.'
        );
        $checks[] = $this->check(
            'focus_keyword',
            'عبارت کلیدی اصلی',
            $keyword !== '',
            'یک عبارت جست‌وجوی اصلی برای مقاله تعیین کنید.'
        );

        if ($keyword !== '') {
            $checks[] = $this->check(
                'keyword_title',
                'عبارت کلیدی در عنوان',
                str_contains($this->normalize($title), $keyword),
                'عبارت کلیدی را طبیعی در عنوان SEO بیاورید.'
            );
            $checks[] = $this->check(
                'keyword_description',
                'عبارت کلیدی در توضیحات',
                str_contains($this->normalize($description), $keyword),
                'عبارت کلیدی را طبیعی در توضیحات متا استفاده کنید.'
            );
            $checks[] = $this->check(
                'keyword_slug',
                'عبارت کلیدی در نشانی',
                str_contains($slug, $keyword),
                'در صورت طبیعی‌بودن، عبارت کلیدی را در slug کوتاه وارد کنید.'
            );
            $checks[] = $this->check(
                'keyword_intro',
                'عبارت کلیدی در ابتدای متن',
                $firstWords !== '' && str_contains($firstWords, $keyword),
                'موضوع اصلی را در مقدمه مقاله روشن کنید.'
            );
        }

        $checks[] = $this->check(
            'content_depth',
            'عمق محتوای مقاله',
            $wordCount >= 300,
            'برای راهنمای کامل، حداقل حدود ۳۰۰ واژه محتوای مفید در نظر بگیرید؛ کیفیت از تعداد مهم‌تر است.'
        );
        $checks[] = $this->check(
            'subheadings',
            'ساختار تیترها',
            preg_match('/<h[2-4]\b/i', $html) === 1,
            'متن را با تیترهای H2 و H3 به بخش‌های قابل‌خواندن تقسیم کنید.'
        );
        $checks[] = $this->check(
            'cover_image',
            'تصویر شاخص',
            filled($post->cover_image),
            'یک تصویر شاخص مرتبط و بهینه‌شده اضافه کنید.'
        );
        $checks[] = $this->check(
            'image_alt',
            'متن جایگزین تصویر',
            filled($post->cover_image_alt),
            'برای تصویر شاخص یک Alt توصیفی و مرتبط با محتوا بنویسید.'
        );
        $checks[] = $this->check(
            'excerpt',
            'خلاصه مقاله',
            filled($post->excerpt) && mb_strlen((string) $post->excerpt) >= 40,
            'یک خلاصه مستقل برای کارت مقاله و پیش‌نمایش بنویسید.'
        );

        $passed = count(array_filter($checks, static fn (array $item): bool => $item['passed']));
        $score = (int) round(($passed / max(1, count($checks))) * 100);

        return [
            'score' => $score,
            'checks' => $checks,
            'word_count' => $wordCount,
            'reading_time' => max(1, (int) ceil($wordCount / 200)),
        ];
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value);
        $value = str_replace(['ي', 'ك', 'ۀ', 'ة'], ['ی', 'ک', 'ه', 'ه'], $value);

        return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? '');
    }

    /**
     * @return array{key: string, label: string, passed: bool, detail: string}
     */
    private function check(string $key, string $label, bool $passed, string $detail): array
    {
        return compact('key', 'label', 'passed', 'detail');
    }
}
