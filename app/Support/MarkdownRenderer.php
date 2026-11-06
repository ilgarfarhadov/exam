<?php



namespace App\Support;

use App\Support\Markdown\MathInlineParser;
use App\Support\Markdown\MathNode;
use App\Support\Markdown\MathNodeRenderer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownRenderer
{
    private static ?MarkdownConverter $converter = null;

    private static function getConverter(): MarkdownConverter
    {
        if (self::$converter === null) {
            // Question and answer text is admin-authored, but the rendered HTML
            // is echoed unescaped onto the student's exam page — so raw HTML is
            // escaped rather than passed through (CommonMark's default is
            // 'allow', which would make a pasted <script> a stored XSS), and
            // javascript:/data: links are dropped.
            $environment = new Environment([
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
                'max_nesting_level' => 20,
            ]);

            $environment->addExtension(new CommonMarkCoreExtension);
            $environment->addExtension(new GithubFlavoredMarkdownExtension);

            // Math has to be claimed while the cursor is still on the opening
            // `$`, or `$x_1 y_2$` loses its underscores to <em> before KaTeX
            // ever sees the formula.
            $environment->addInlineParser(new MathInlineParser, 100);
            $environment->addRenderer(MathNode::class, new MathNodeRenderer);

            self::$converter = new MarkdownConverter($environment);
        }

        return self::$converter;
    }

    public static function render(?string $text): string
    {
        // Not empty(): a question or answer whose whole text is "0" is valid
        // content, and empty("0") is true.
        if ($text === null || $text === '') {
            return '';
        }

        if (str_contains($text, '///')) {
            $parsed = self::parseBilingual($text);
            $renderedAz = self::getConverter()->convert($parsed['az'])->getContent();
            $renderedEn = $parsed['en'] !== null && $parsed['en'] !== ''
                ? self::getConverter()->convert($parsed['en'])->getContent()
                : '';
            $renderedCommon = $parsed['common'] !== null && $parsed['common'] !== ''
                ? self::getConverter()->convert($parsed['common'])->getContent()
                : '';

            $output = '<div class="ps-bilingual">';
            $output .= '<div class="ps-bilingual-az">' . $renderedAz . '</div>';
            if ($renderedEn !== '') {
                $output .= '<div class="ps-bilingual-en"><span class="badge ps-bilingual-badge me-2">EN</span>' . $renderedEn . '</div>';
            }
            $output .= '</div>';

            if ($renderedCommon !== '') {
                $output .= '<div class="ps-bilingual-common mt-2">' . $renderedCommon . '</div>';
            }

            return $output;
        }

        return self::getConverter()->convert($text)->getContent();
    }

    /**
     * Parse bilingual content separated by '///'.
     *
     * @return array{az: string, en: ?string, common: ?string}
     */
    public static function parseBilingual(string $text): array
    {
        if (! str_contains($text, '///')) {
            return ['az' => $text, 'en' => null, 'common' => null];
        }

        $parts = explode('///', $text);
        if (count($parts) >= 3) {
            return [
                'az' => trim($parts[0]),
                'en' => trim($parts[1]),
                'common' => trim(implode('///', array_slice($parts, 2))),
            ];
        }

        $az = trim($parts[0]);
        $rest = trim($parts[1]);

        if (preg_match('/^(.*?)\n\n(###?\s+.*|```.*)$/s', $rest, $matches)) {
            return [
                'az' => $az,
                'en' => trim($matches[1]),
                'common' => trim($matches[2]),
            ];
        }

        return [
            'az' => $az,
            'en' => $rest,
            'common' => null,
        ];
    }

    /**
     * render() minus the wrapping <p> when the result is a single paragraph.
     *
     * The answer options put rendered text inside a <label>, which may only
     * hold phrasing content — a <p> there is invalid and breaks the label's
     * click-to-select. Multi-block text (a list, a table) still comes back as
     * blocks; that is rare enough to leave to the browser's tolerance.
     */
    public static function renderInline(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        if (str_contains($text, '///')) {
            $parts = explode('///', $text, 2);
            $azHtml = self::renderInlinePart(trim($parts[0]));
            $enHtml = self::renderInlinePart(trim($parts[1]));

            return '<span class="ps-bilingual-inline">'
                . '<span class="ps-bilingual-az">' . $azHtml . '</span>'
                . ' <span class="ps-bilingual-sep text-muted mx-1">/</span> '
                . '<span class="ps-bilingual-en text-muted">' . $enHtml . '</span>'
                . '</span>';
        }

        return self::renderInlinePart($text);
    }

    private static function renderInlinePart(string $text): string
    {
        $html = \trim(self::getConverter()->convert($text)->getContent());

        if (\preg_match('/^<p>(.*)<\/p>$/s', $html, $matches) && ! \str_contains($matches[1], '<p>')) {
            return $matches[1];
        }

        return $html;
    }

    /**
     * A one-line plain-text preview: markdown rendered, tags dropped, runs of
     * whitespace collapsed, then truncated.
     *
     * Truncating the *source* instead — Str::limit over raw markdown — cuts
     * code fences and emphasis in half, and the listing then shows the broken
     * syntax rather than the question. Cutting the rendered text avoids that.
     *
     * Returns text, not HTML — the caller must escape it ({{ }} does).
     */
    public static function plainText(?string $text, int $limit = 160): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        if (str_contains($text, '///')) {
            $parsed = self::parseBilingual($text);
            $raw = $parsed['az'] . ($parsed['en'] ? ' / ' . $parsed['en'] : '') . ($parsed['common'] ? ' ' . $parsed['common'] : '');
            $plain = self::cleanPlainText($raw);
        } else {
            $plain = self::cleanPlainText($text);
        }

        if (\mb_strlen($plain) <= $limit) {
            return $plain;
        }

        return \rtrim(\mb_substr($plain, 0, $limit)).'…';
    }

    private static function cleanPlainText(string $text): string
    {
        $plain = \html_entity_decode(
            \strip_tags(self::getConverter()->convert($text)->getContent()),
            \ENT_QUOTES | \ENT_HTML5,
            'UTF-8'
        );

        return \trim((string) \preg_replace('/\s+/u', ' ', $plain));
    }
}
