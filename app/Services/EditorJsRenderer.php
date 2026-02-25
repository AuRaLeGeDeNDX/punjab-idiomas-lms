<?php

namespace App\Services;

class EditorJsRenderer
{
    /**
     * Render Editor.js JSON data to HTML
     *
     * @param array $data Editor.js JSON data
     * @return string Rendered HTML
     */
    public function render(array $data): string
    {
        if (empty($data['blocks'])) {
            return '';
        }

        $html = '';
        
        foreach ($data['blocks'] as $block) {
            $html .= $this->renderBlock($block);
        }
        
        return $html;
    }

    /**
     * Render a single Editor.js block
     *
     * @param array $block Block data
     * @return string Rendered HTML
     */
    private function renderBlock(array $block): string
    {
        $type = $block['type'] ?? '';
        $data = $block['data'] ?? [];

        return match($type) {
            'header' => $this->renderHeader($data),
            'paragraph' => $this->renderParagraph($data),
            'list' => $this->renderList($data),
            'quote' => $this->renderQuote($data),
            'code' => $this->renderCode($data),
            default => ''
        };
    }

    /**
     * Render header block
     *
     * @param array $data Block data
     * @return string Rendered HTML
     */
    private function renderHeader(array $data): string
    {
        $level = $data['level'] ?? 2;
        $text = $this->sanitizeInlineHtml($data['text'] ?? '');
        
        // Ensure level is between 1 and 6
        $level = max(1, min(6, $level));
        
        return "<h{$level}>{$text}</h{$level}>";
    }

    /**
     * Render paragraph block
     *
     * @param array $data Block data
     * @return string Rendered HTML
     */
    private function renderParagraph(array $data): string
    {
        $text = $this->sanitizeInlineHtml($data['text'] ?? '');
        return "<p>{$text}</p>";
    }

    /**
     * Render list block
     *
     * @param array $data Block data
     * @return string Rendered HTML
     */
    private function renderList(array $data): string
    {
        $style = $data['style'] ?? 'unordered';
        $items = $data['items'] ?? [];
        
        if (empty($items)) {
            return '';
        }
        
        $tag = $style === 'ordered' ? 'ol' : 'ul';
        
        // Normalize each list item before processing and skip empty items
        $itemsHtml = [];
        foreach ($items as $item) {
            // Normalize the item to string (handles strings, arrays, objects)
            $normalized = $this->normalizeToString($item);
            
            // Skip empty items after normalization
            if ($normalized === '') {
                continue;
            }
            
            // Sanitize and create list item HTML
            $sanitized = $this->sanitizeInlineHtml($normalized);
            $itemsHtml[] = "<li>{$sanitized}</li>";
        }
        
        // If all items were empty, return empty string
        if (empty($itemsHtml)) {
            return '';
        }
        
        return "<{$tag}>" . implode('', $itemsHtml) . "</{$tag}>";
    }

    /**
     * Render quote block
     *
     * @param array $data Block data
     * @return string Rendered HTML
     */
    private function renderQuote(array $data): string
    {
        $text = $this->sanitizeInlineHtml($data['text'] ?? '');
        $caption = $data['caption'] ?? '';
        
        $html = "<blockquote><p>{$text}</p>";
        
        if (!empty($caption)) {
            $sanitizedCaption = $this->sanitizeInlineHtml($caption);
            $html .= "<cite>{$sanitizedCaption}</cite>";
        }
        
        $html .= "</blockquote>";
        
        return $html;
    }

    /**
     * Render code block
     *
     * @param array $data Block data
     * @return string Rendered HTML
     */
    private function renderCode(array $data): string
    {
        $code = htmlspecialchars($data['code'] ?? '', ENT_QUOTES, 'UTF-8');
        return "<pre><code>{$code}</code></pre>";
    }

    /**
     * Sanitize inline HTML to allow safe formatting tags
     * Allows: <b>, <i>, <u>, <a>, <code>, <mark>
     * Escapes everything else
     *
     * @param mixed $html HTML string or other data type to sanitize
     * @return string Sanitized HTML
     */
    private function sanitizeInlineHtml(mixed $html): string
    {
        // Add type check at method entry
        // Call normalizeToString for non-string input
        if (!is_string($html)) {
            $html = $this->normalizeToString($html);
        }
        
        // Allow specific inline formatting tags
        $allowedTags = '<b><i><u><a><code><mark><strong><em>';
        
        // Strip all tags except allowed ones
        $sanitized = strip_tags($html, $allowedTags);
        
        // Additional sanitization for anchor tags to prevent javascript: URLs
        $sanitized = preg_replace_callback(
            '/<a\s+([^>]*?)href=["\']([^"\']*)["\']([^>]*)>/i',
            function($matches) {
                $href = $matches[2];
                
                // Block javascript:, data:, and vbscript: URLs
                if (preg_match('/^\s*(javascript|data|vbscript):/i', $href)) {
                    return '<a ' . $matches[1] . 'href="#"' . $matches[3] . '>';
                }
                
                return $matches[0];
            },
            $sanitized
        );
        
        return $sanitized;
    }

    /**
     * Normalize various data types to string for safe processing
     * Handles strings, arrays, objects, and null values
     *
     * @param mixed $value Value to normalize
     * @return string Normalized string value
     */
    private function normalizeToString(mixed $value): string
    {
        // Handle null or empty values
        if ($value === null || $value === '') {
            return '';
        }

        // Handle string input (passthrough)
        if (is_string($value)) {
            return $value;
        }

        // Handle array input (join elements with space)
        if (is_array($value)) {
            \Log::debug('EditorJsRenderer: Converting array to string', [
                'type' => 'array',
                'count' => count($value)
            ]);
            
            // Recursively normalize array elements
            $normalized = array_map(function($item) {
                return $this->normalizeToString($item);
            }, $value);
            
            // Filter out empty strings and join with space
            $filtered = array_filter($normalized, function($item) {
                return $item !== '';
            });
            
            return implode(' ', $filtered);
        }

        // Handle object input (extract 'text' or 'content' property)
        if (is_object($value)) {
            \Log::debug('EditorJsRenderer: Converting object to string', [
                'type' => 'object',
                'class' => get_class($value)
            ]);
            
            // Try to extract 'text' property
            if (isset($value->text)) {
                return $this->normalizeToString($value->text);
            }
            
            // Try to extract 'content' property
            if (isset($value->content)) {
                return $this->normalizeToString($value->content);
            }
            
            // If object has __toString method, use it
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
            
            // Last resort: return empty string
            \Log::warning('EditorJsRenderer: Unable to extract text from object', [
                'class' => get_class($value)
            ]);
            return '';
        }

        // Handle scalar types (int, float, bool)
        if (is_scalar($value)) {
            \Log::debug('EditorJsRenderer: Converting scalar to string', [
                'type' => gettype($value),
                'value' => $value
            ]);
            return (string) $value;
        }

        // Unknown type - log warning and return empty string
        \Log::warning('EditorJsRenderer: Unexpected data type encountered', [
            'type' => gettype($value)
        ]);
        return '';
    }
}
