<?php

namespace App\Services\Content;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichTextSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote',
        'strong', 'b', 'em', 'i', 'u', 'a', 'br', 'hr',
        'figure', 'figcaption', 'img',
    ];

    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'svg',
        'math', 'form', 'input', 'button', 'textarea', 'video',
        'audio', 'template',
    ];

    public function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<!doctype html><html><head><meta charset="UTF-8"></head><body><div id="blog-content-root">' . $html . '</div></body></html>',
                LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
            );

            if (! $loaded) {
                return e(strip_tags($html));
            }

            $root = $document->getElementById('blog-content-root');

            if (! $root) {
                return e(strip_tags($html));
            }

            $output = '';
            foreach ($root->childNodes as $child) {
                $output .= $this->renderNode($child);
            }

            return trim($output);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function renderNode(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return htmlspecialchars((string) $node->nodeValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        if ($node->nodeType !== XML_ELEMENT_NODE || ! $node instanceof DOMElement) {
            return '';
        }

        $tag = strtolower($node->tagName);

        if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
            return '';
        }

        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            return $this->renderChildren($node);
        }

        if ($tag === 'img') {
            return $this->renderImage($node);
        }

        $attributes = '';

        if ($tag === 'a') {
            $href = trim($node->getAttribute('href'));
            if ($this->isSafeLink($href)) {
                $attributes .= ' href="' . e($href) . '"';
            }

            $title = trim($node->getAttribute('title'));
            if ($title !== '') {
                $attributes .= ' title="' . e(mb_substr($title, 0, 180)) . '"';
            }

            if ($node->getAttribute('target') === '_blank') {
                $attributes .= ' target="_blank" rel="noopener noreferrer"';
            }
        }

        if ($tag === 'ol') {
            $start = $node->getAttribute('start');
            if (ctype_digit($start) && (int) $start > 1 && (int) $start <= 10000) {
                $attributes .= ' start="' . (int) $start . '"';
            }
        }

        if (in_array($tag, ['br', 'hr'], true)) {
            return '<' . $tag . $attributes . '>';
        }

        return '<' . $tag . $attributes . '>' . $this->renderChildren($node) . '</' . $tag . '>';
    }

    private function renderChildren(DOMNode $node): string
    {
        $output = '';

        foreach ($node->childNodes as $child) {
            $output .= $this->renderNode($child);
        }

        return $output;
    }

    private function renderImage(DOMElement $node): string
    {
        $src = trim($node->getAttribute('src'));

        if (! $this->isSafeImageSource($src)) {
            return '';
        }

        $alt = trim($node->getAttribute('alt'));
        $attributes = ' src="' . e($src) . '" alt="' . e(mb_substr($alt, 0, 250)) . '" loading="lazy" decoding="async"';

        foreach (['width', 'height'] as $dimension) {
            $value = $node->getAttribute($dimension);
            if (ctype_digit($value) && (int) $value >= 1 && (int) $value <= 4096) {
                $attributes .= ' ' . $dimension . '="' . (int) $value . '"';
            }
        }

        return '<img' . $attributes . '>';
    }

    private function isSafeLink(string $url): bool
    {
        if ($url === '' || preg_match('/[\x00-\x20]/', $url) || str_contains($url, chr(92))) {
            return false;
        }

        if (str_starts_with($url, '#') || str_starts_with($url, '/')) {
            return ! str_starts_with($url, '//');
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }

    private function isSafeImageSource(string $url): bool
    {
        if ($url === '' || preg_match('/[\x00-\x20]/', $url) || str_contains($url, chr(92))) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return ! str_starts_with($url, '//')
                && (str_starts_with($url, '/storage/') || str_starts_with($url, '/images/'));
        }

        return strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https';
    }
}
