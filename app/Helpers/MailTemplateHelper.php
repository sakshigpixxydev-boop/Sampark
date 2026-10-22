<?php

namespace App\Helpers;

use App\Models\MailTemplate;
use Illuminate\Support\Facades\Log;

class MailTemplateHelper
{
    /**
     * Render the mail template by replacing {{key}} with values
     *
     * @param string $identifier
     * @param array $variables
     * @param string|null $lang
     * @return array|null [ 'subject' => string, 'message' => string ] or null
     */
    public static function render(string $identifier, array $variables, ?string $lang = null): ?array
    {
        $template = MailTemplate::where('identifier', $identifier)->first();

        if (!$template) {
            Log::error("Mail template '{$identifier}' not found.");
            return null;
        }

        // Default to app locale if lang is not specified
        $lang = strtolower($lang ?? app()->getLocale());

        // Determine title and content based on language, falling back to English ('en') if empty
        $subject = null;
        $content = null;

        if ($lang === 'hi') {
            $subject = $template->title_hi ?: $template->title_en;
            $content = $template->content_hi ?: $template->content_en;
        } elseif ($lang === 'gu') {
            $subject = $template->title_gu ?: $template->title_en;
            $content = $template->content_gu ?: $template->content_en;
        }

        // Fallback to English if not Hindi/Gujarati or if those translations are empty
        if (empty($subject)) {
            $subject = $template->title_en;
        }
        if (empty($content)) {
            $content = $template->content_en;
        }

        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value ?? '', $subject);
            $content = str_replace($placeholder, $value ?? '', $content);
        }

        // Decode HTML entities (e.g., &lt; -> <)
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

        return [
            'subject' => $subject,
            'message' => $content,
        ];
    }

    /**
     * Render raw content string with variables
     *
     * @param string $content
     * @param array $variables
     * @return string
     */
    public static function renderContent(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value ?? '', $content);
        }

        return html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    }
}
