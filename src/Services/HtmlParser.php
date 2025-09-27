<?php
declare(strict_types=1);

namespace CeatProductParser\Services;

use DOMDocument;
use DOMNode;
use DOMXPath;
use WP_Error;

final class HtmlParser
{
    /**
     * Fetch remote HTML and return markup for nodes that match the selector.
     *
     * @return string[]|WP_Error
     */
    public function extract(string $url, string $selector)
    {
        $selector = trim($selector);

        if ($selector === '') {
            return new WP_Error('ceat_pp_empty_selector', __('Selector cannot be empty.', 'ceat-product-parser'));
        }

        $response = wp_remote_get(
            $url,
            [
                'timeout'     => 15,
                'redirection' => 5,
                'user-agent'  => $this->buildUserAgent(),
            ]
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $status = wp_remote_retrieve_response_code($response);
        if ($status < 200 || $status >= 300) {
            return new WP_Error(
                'ceat_pp_http_error',
                sprintf(
                    /* translators: %d: HTTP status code */
                    __('Unexpected response status: %d.', 'ceat-product-parser'),
                    $status
                )
            );
        }

        $body = wp_remote_retrieve_body($response);
        if ($body === '') {
            return new WP_Error('ceat_pp_empty_body', __('The response body is empty.', 'ceat-product-parser'));
        }

        $document = $this->createDocument($body);
        if ($document instanceof WP_Error) {
            return $document;
        }

        $expression = $this->selectorToXPath($selector);
        if ($expression instanceof WP_Error) {
            return $expression;
        }

        $xpath = new DOMXPath($document);
        $nodes = @$xpath->query($expression);

        if ($nodes === false) {
            return new WP_Error('ceat_pp_xpath_error', __('The selector could not be converted to a valid XPath query.', 'ceat-product-parser'));
        }

        $results = [];
        foreach ($nodes as $node) {
            if ($node instanceof DOMNode) {
                $results[] = trim($document->saveHTML($node));
            }
        }

        return $results;
    }

    private function buildUserAgent(): string
    {
        $site = '';

        if (function_exists('home_url')) {
            $site = (string) home_url('/');
        } elseif (function_exists('get_site_url')) {
            $site = (string) get_site_url();
        }

        if ($site === '') {
            $site = 'https://wordpress.org';
        }

        return sprintf('CeatProductParser/0.1 (+%s)', $site);
    }

    /**
     * @return DOMDocument|WP_Error
     */
    private function createDocument(string $html)
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        $previous_state = libxml_use_internal_errors(true);

        try {
            $markup = $this->prepareMarkup($html);
            $loaded = $document->loadHTML($markup, LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous_state);
        }

        if (! $loaded) {
            return new WP_Error('ceat_pp_dom_error', __('Failed to parse the HTML document.', 'ceat-product-parser'));
        }

        return $document;
    }

    private function prepareMarkup(string $html): string
    {
        $encoding = null;

        if (function_exists('mb_detect_encoding')) {
            $encoding = mb_detect_encoding($html, 'UTF-8, ISO-8859-1, WINDOWS-1252, ASCII', true);
        }

        if (function_exists('mb_convert_encoding')) {
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', $encoding ?: 'UTF-8');
        }

        if (stripos($html, '<meta charset=') === false && stripos($html, 'http-equiv="Content-Type"') === false) {
            $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $html;
        }

        return $html;
    }

    /**
     * @return string|WP_Error
     */
    private function selectorToXPath(string $selector)
    {
        if (strpos($selector, '//') === 0 || strpos($selector, './/') === 0) {
            return $selector;
        }

        $first = $selector[0];

        if ($first === '#') {
            $id = substr($selector, 1);
            if ($id === '') {
                return new WP_Error('ceat_pp_invalid_selector', __('The provided selector is not valid.', 'ceat-product-parser'));
            }

            return sprintf('//*[@id=%s]', $this->escapeForXPath($id));
        }

        if ($first === '.') {
            $class = substr($selector, 1);
            if ($class === '') {
                return new WP_Error('ceat_pp_invalid_selector', __('The provided selector is not valid.', 'ceat-product-parser'));
            }

            return sprintf(
                "//*[contains(concat(' ', normalize-space(@class), ' '), %s)]",
                $this->escapeForXPath(' ' . $class . ' ')
            );
        }

        if (preg_match("/^(?P<attr>[a-zA-Z0-9_:\\-]+)\\s*=\\s*(\"|')?(?P<value>[^\"']+)(\\2)?$/", $selector, $matches)) {
            $attribute = strtolower($matches['attr']);
            $value     = $matches['value'];

            if ($attribute === 'class') {
                return sprintf(
                    "//*[contains(concat(' ', normalize-space(@class), ' '), %s)]",
                    $this->escapeForXPath(' ' . $value . ' ')
                );
            }

            return sprintf('//*[@%s=%s]', $attribute, $this->escapeForXPath($value));
        }

        if (preg_match('/^[a-zA-Z0-9_-]+$/', $selector)) {
            return sprintf('//%s', $selector);
        }

        return new WP_Error('ceat_pp_invalid_selector', __('The provided selector format is not supported.', 'ceat-product-parser'));
    }

    private function escapeForXPath(string $value): string
    {
        $double_quote = chr(34);

        if (strpos($value, "'") === false) {
            return "'" . $value . "'";
        }

        if (strpos($value, $double_quote) === false) {
            return $double_quote . $value . $double_quote;
        }

        $parts = explode("'", $value);
        $escaped = [];

        foreach ($parts as $index => $part) {
            if ($part !== '') {
                $escaped[] = "'" . $part . "'";
            }

            if ($index !== count($parts) - 1) {
                $escaped[] = $double_quote . "'" . $double_quote;
            }
        }

        return 'concat(' . implode(', ', $escaped) . ')';
    }
}
