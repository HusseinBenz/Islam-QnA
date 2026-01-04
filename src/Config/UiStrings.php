<?php
declare(strict_types=1);

namespace App\Config;

final class UiStrings
{
    public static function base(): array
    {
        return [
            'title' => 'Anti Shuboohat Search',
            'tagline' => 'Lightweight questions and answers, ready to search.',
            'placeholder' => 'Search questions or answers',
            'search' => 'Search',
            'browse' => 'Browse',
            'latest' => 'Fresh picks',
            'results_for' => 'Showing matches for',
            'no_results' => 'No entries available in this language yet.',
            'use_search_or_browse' => 'Use search or browse to see entries.',
            'question_label' => 'Question:',
            'created_label' => 'Created:',
            'posted_on' => 'Posted on',
            'previous' => 'Previous',
            'next' => 'Next',
            'page_of' => 'Page %d of %d',
            'results_per_page' => 'Results per page:',
            'language' => 'Language',
            'languages' => 'Languages:',
            'admin_link' => 'Admin and translations live in admin.php',
            'entry_missing' => 'No entry found for this language.',
        ];
    }

    public static function arabic(): array
    {
        return [
            'title' => 'بحث ضد الشبهات',
            'tagline' => 'أسئلة وأجوبة خفيفة وجاهزة للبحث.',
            'placeholder' => 'ابحث في الأسئلة أو الإجابات',
            'search' => 'بحث',
            'browse' => 'تصفح',
            'latest' => 'مختارات جديدة',
            'results_for' => 'عرض النتائج لـ',
            'no_results' => 'لا توجد مدخلات متاحة بهذه اللغة بعد.',
            'use_search_or_browse' => 'استخدم البحث أو التصفح لعرض المدخلات.',
            'question_label' => 'السؤال:',
            'created_label' => 'تاريخ الإنشاء:',
            'posted_on' => 'نُشر في',
            'previous' => 'السابق',
            'next' => 'التالي',
            'page_of' => 'الصفحة %d من %d',
            'results_per_page' => 'النتائج لكل صفحة:',
            'language' => 'اللغة',
            'languages' => 'اللغات:',
            'admin_link' => 'لوحة التحكم والترجمات في admin.php',
            'entry_missing' => 'لا توجد مشاركة بهذه اللغة.',
        ];
    }

    private function __construct()
    {
    }
}
