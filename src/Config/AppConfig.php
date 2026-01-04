<?php
declare(strict_types=1);

namespace App\Config;

final class AppConfig
{
    public const DEFAULT_LANG = 'en';
    public const UI_KEYS = [
        'title',
        'tagline',
        'placeholder',
        'search',
        'browse',
        'latest',
        'results_for',
        'no_results',
        'use_search_or_browse',
        'question_label',
        'created_label',
        'posted_on',
        'previous',
        'next',
        'page_of',
        'results_per_page',
        'language',
        'languages',
        'admin_link',
        'entry_missing',
    ];
    public const PALETTE_SIZE = 13;

    public const ADMIN_USERNAME = 'admin';
    public const ADMIN_PASSWORD_SALT = 'change-this-salt';
    public const ADMIN_PASSWORD_HASH = 'c0f5cfb2e815713838889dd1700a84155aa4d4c51f4b6e2f27b95e0ddc2fb869';
    public const ADMIN_SESSION_COOKIE = 'admin_session';
    public const ADMIN_SESSION_TTL = 28800;

    private function __construct()
    {
    }
}
