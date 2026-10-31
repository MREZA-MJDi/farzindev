<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>مجله فرزین</title>
        <link>{{ route('blog.index') }}</link>
        <description>راهنماهای انتخاب، نصب و نگهداری تجهیزات آشپزخانه از فرزین.</description>
        <language>fa-IR</language>
        <lastBuildDate>{{ now()->format(DATE_RSS) }}</lastBuildDate>
        @foreach($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ route('blog.show', $post->slug) }}</link>
                <guid isPermaLink="true">{{ route('blog.show', $post->slug) }}</guid>
                <description>{{ $post->excerpt ?: $post->meta_description }}</description>
                <pubDate>{{ $post->published_at?->format(DATE_RSS) }}</pubDate>
                @if($post->author)
                    <author>{{ $post->author->name }}</author>
                @endif
                @foreach($post->categories as $category)
                    <category>{{ $category->name }}</category>
                @endforeach
                @foreach($post->tags as $tag)
                    <category>{{ $tag->name }}</category>
                @endforeach
            </item>
        @endforeach
    </channel>
</rss>
