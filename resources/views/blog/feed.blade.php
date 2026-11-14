<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    @php
        $normalizeFeedCopy = static fn (?string $value): string => str_ireplace(
            ['فرزین فرجی', 'فرزین', 'FARZIN', 'Farzin'],
            ['فرجی‌هوم', 'فرجی‌هوم', 'FARAJI HOME', 'Faraji Home'],
            trim((string) $value)
        );
    @endphp
    <channel>
        <title>مجله فرجی‌هوم</title>
        <link>{{ route('blog.index') }}</link>
        <description>راهنماهای انتخاب، مقایسه، نصب و نگهداری هود، اجاق گاز و سینک از فرجی‌هوم.</description>
        <language>fa-IR</language>
        <lastBuildDate>{{ now()->format(DATE_RSS) }}</lastBuildDate>
        @foreach($posts as $post)
            <item>
                <title>{{ $normalizeFeedCopy($post->title) }}</title>
                <link>{{ route('blog.show', $post->slug) }}</link>
                <guid isPermaLink="true">{{ route('blog.show', $post->slug) }}</guid>
                <description>{{ $normalizeFeedCopy($post->excerpt ?: $post->meta_description) }}</description>
                <pubDate>{{ $post->published_at?->format(DATE_RSS) }}</pubDate>
                @if($post->author)
                    <author>{{ $post->author->name }}</author>
                @endif
                @foreach($post->terms->where('type', 'category') as $category)
                    <category>{{ $normalizeFeedCopy($category->name) }}</category>
                @endforeach
                @foreach($post->terms->where('type', 'tag') as $tag)
                    <category>{{ $normalizeFeedCopy($tag->name) }}</category>
                @endforeach
            </item>
        @endforeach
    </channel>
</rss>
