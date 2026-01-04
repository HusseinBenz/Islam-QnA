<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\AppContext;
use App\Config\AppConfig;
use App\Support\Text;
use App\View;

final class AdminController
{
    private AppContext $context;

    public function __construct(AppContext $context)
    {
        $this->context = $context;
    }

    public function handle(): void
    {
        header('Content-Type: text/html; charset=UTF-8');

        $notice = '';
        $error = '';
        $clientIp = $this->getClientIp($_SERVER);
        $this->context->adminSessions->purgeExpired();
        $isAdmin = $clientIp !== '' && $this->context->adminSessions->isSessionValid($clientIp);

        $languages = $this->context->languages->all();

        $view = $_POST['view'] ?? $_GET['view'] ?? 'entries';
        $allowedViews = ['entries', 'add', 'edit', 'translate', 'languages', 'ui', 'palette'];
        if (!in_array($view, $allowedViews, true)) {
            $view = 'entries';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'login') {
                $username = trim((string) ($_POST['username'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                if ($clientIp === '') {
                    $error = 'Unable to determine client IP.';
                } elseif (hash_equals(AppConfig::ADMIN_USERNAME, $username)
                    && hash_equals(AppConfig::ADMIN_PASSWORD_HASH, $this->context->adminSessions->hashPassword($password))) {
                    $token = $this->context->adminSessions->createSession($clientIp);
                    $this->context->adminSessions->setAdminCookie($token);
                    $isAdmin = true;
                    $notice = 'Signed in as admin.';
                } else {
                    $error = 'Invalid credentials.';
                }
            }

            if ($isAdmin && $action === 'logout') {
                $token = $_COOKIE[AppConfig::ADMIN_SESSION_COOKIE] ?? '';
                if ($token !== '') {
                    $this->context->adminSessions->deleteSession($token);
                }
                $this->context->adminSessions->clearAdminCookie();
                $isAdmin = false;
                $notice = 'Signed out.';
            }

            if ($isAdmin && $action === 'add') {
                $question = Text::sanitizePlainText((string) ($_POST['question'] ?? ''));
                $answer = Text::sanitizeMarkdown((string) ($_POST['answer'] ?? ''));
                if ($question === '' || $answer === '') {
                    $error = 'Question and answer are required.';
                } else {
                    $tags = $this->parseTags((string) ($_POST['tags'] ?? ''));
                    $entryId = $this->context->qa->addEntry($question, $answer);
                    $this->context->qa->setTags($entryId, $tags);
                    $notice = 'Entry added.';
                    $view = 'entries';
                }
            }

            if ($isAdmin && $action === 'update') {
                $id = (int) ($_POST['id'] ?? 0);
                $lang = (string) ($_POST['lang'] ?? AppConfig::DEFAULT_LANG);
                $question = Text::sanitizePlainText((string) ($_POST['question'] ?? ''));
                $answer = Text::sanitizeMarkdown((string) ($_POST['answer'] ?? ''));
                if ($id <= 0) {
                    $error = 'Pick an entry to edit.';
                } elseif (!array_key_exists($lang, $languages)) {
                    $error = 'Choose a valid language.';
                } elseif ($question === '' || $answer === '') {
                    $error = 'Question and answer are required.';
                } else {
                    if ($lang === AppConfig::DEFAULT_LANG) {
                        $this->context->qa->updateEntry($id, $question, $answer);
                        $tags = $this->parseTags((string) ($_POST['tags'] ?? ''));
                        $this->context->qa->setTags($id, $tags);
                        $notice = 'Entry updated.';
                    } else {
                        $existing = $this->context->qa->getTranslation($id, $lang);
                        if ($existing === null) {
                            $error = 'Translation does not exist for this language. Add it from Translate.';
                        } else {
                            $this->context->qa->updateTranslation($id, $lang, $question, $answer);
                            $notice = 'Translation updated.';
                        }
                    }
                    $view = 'edit';
                    $_GET['edit'] = $id;
                    $_GET['elang'] = $lang;
                }
            }

            if ($isAdmin && $action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $error = 'Pick an entry to delete.';
                } else {
                    $this->context->qa->deleteEntry($id);
                    $notice = 'Entry deleted.';
                }
            }

            if ($isAdmin && $action === 'translate') {
                $qaId = (int) ($_POST['qa_id'] ?? 0);
                $lang = (string) ($_POST['lang'] ?? AppConfig::DEFAULT_LANG);
                $tQuestion = Text::sanitizePlainText((string) ($_POST['t_question'] ?? ''));
                $tAnswer = Text::sanitizeMarkdown((string) ($_POST['t_answer'] ?? ''));
                if ($lang === AppConfig::DEFAULT_LANG) {
                    $error = 'Use a non-default language for translations.';
                } elseif (!array_key_exists($lang, $languages)) {
                    $error = 'Choose a valid language.';
                } else {
                    $exists = $this->context->qa->exists($qaId);
                    if ($qaId <= 0 || !$exists) {
                        $error = 'Choose a valid entry to translate.';
                    } elseif ($tQuestion === '' || $tAnswer === '') {
                        $error = 'Translated question and answer are required.';
                    } else {
                        $existingLangs = $this->context->qa->getTranslationLangs($qaId);
                        if (in_array($lang, $existingLangs, true)) {
                            $error = 'Translation already exists. Edit it from Edit > language selector.';
                        } else {
                            $this->context->qa->addTranslation($qaId, $lang, $tQuestion, $tAnswer);
                            $notice = 'Translation added.';
                            $view = 'translate';
                            $_GET['translate'] = $qaId;
                        }
                    }
                }
            }

            if ($isAdmin && $action === 'add_language') {
                $code = strtolower(trim((string) ($_POST['code'] ?? '')));
                $label = Text::sanitizePlainText((string) ($_POST['label'] ?? ''));
                $dir = ($_POST['dir'] ?? '') === 'rtl' ? 'rtl' : 'ltr';
                $uiFont = Text::sanitizePlainText((string) ($_POST['ui_font'] ?? ''));
                $uiFontUrl = trim((string) ($_POST['ui_font_url'] ?? ''));
                $fontDefaults = $this->context->languageService->defaultUiFontConfig($code !== '' ? $code : AppConfig::DEFAULT_LANG);
                if ($uiFont === '') {
                    $uiFont = $fontDefaults['font'];
                }
                if ($uiFontUrl === '') {
                    $uiFontUrl = $fontDefaults['url'];
                }
                if ($code === '' || !preg_match('/^[a-z]{2,8}$/', $code)) {
                    $error = 'Use a language code like en, ar, fr.';
                } elseif ($label === '') {
                    $error = 'Label is required.';
                } elseif ($uiFontUrl !== '' && filter_var($uiFontUrl, FILTER_VALIDATE_URL) === false) {
                    $error = 'Provide a valid Google Fonts URL.';
                } else {
                    $this->context->languages->save($code, $label, $dir, $uiFont, $uiFontUrl);
                    $languages = $this->context->languages->all();
                    $notice = 'Language saved.';
                    $view = 'languages';
                }
            }

            if ($isAdmin && $action === 'update_language') {
                $original = strtolower(trim((string) ($_POST['original_code'] ?? '')));
                $code = strtolower(trim((string) ($_POST['code'] ?? '')));
                $label = Text::sanitizePlainText((string) ($_POST['label'] ?? ''));
                $dir = ($_POST['dir'] ?? '') === 'rtl' ? 'rtl' : 'ltr';
                $uiFont = Text::sanitizePlainText((string) ($_POST['ui_font'] ?? ''));
                $uiFontUrl = trim((string) ($_POST['ui_font_url'] ?? ''));
                $fontDefaults = $this->context->languageService->defaultUiFontConfig($code !== '' ? $code : $original);
                if ($uiFont === '') {
                    $uiFont = $fontDefaults['font'];
                }
                if ($uiFontUrl === '') {
                    $uiFontUrl = $fontDefaults['url'];
                }

                if ($original === '' || !array_key_exists($original, $languages)) {
                    $error = 'Select a valid language to update.';
                } elseif ($code === '' || !preg_match('/^[a-z]{2,8}$/', $code)) {
                    $error = 'Use a language code like en, ar, fr.';
                } elseif ($label === '') {
                    $error = 'Label is required.';
                } elseif ($uiFontUrl !== '' && filter_var($uiFontUrl, FILTER_VALIDATE_URL) === false) {
                    $error = 'Provide a valid Google Fonts URL.';
                } else {
                    if ($code !== $original && $this->context->languages->exists($code)) {
                        $error = 'That language code already exists.';
                    }
                }
                if ($error === '') {
                    $this->context->languages->updateCodeAndMeta($original, $code, $label, $dir, $uiFont, $uiFontUrl);
                    $languages = $this->context->languages->all();
                    $notice = 'Language updated.';
                    $view = 'languages';
                }
            }

            if ($isAdmin && $action === 'save_ui') {
                $lang = (string) ($_POST['lang'] ?? AppConfig::DEFAULT_LANG);
                if (!array_key_exists($lang, $languages)) {
                    $error = 'Choose a valid language for UI strings.';
                } else {
                    $values = [];
                    foreach (AppConfig::UI_KEYS as $key) {
                        $field = 'ui_' . $key;
                        $value = Text::sanitizePlainText((string) ($_POST[$field] ?? ''));
                        if ($value === '') {
                            $error = 'Fill all UI strings.';
                            break;
                        }
                        $values[$key] = $value;
                    }
                    if ($error === '') {
                        $this->context->uiTranslations->save($lang, $values);
                        $notice = 'UI strings saved for ' . $lang . '.';
                        $view = 'ui';
                        $_GET['ui_lang'] = $lang;
                    }
                }
            }

            if ($isAdmin && $action === 'save_palette') {
                $input = trim((string) ($_POST['palette'] ?? ''));
                $colors = $this->context->paletteService->parseLines($input);
                if ($colors === []) {
                    $error = 'Paste exactly ' . AppConfig::PALETTE_SIZE . ' hex colors (one per line).';
                } else {
                    $this->context->palettes->save($colors);
                    $notice = 'Palette saved.';
                    $view = 'palette';
                }
            }
        }

        $keyword = trim((string) ($_GET['keyword'] ?? ''));
        $sort = (string) ($_GET['sort'] ?? 'updated_at');
        $direction = strtoupper((string) ($_GET['direction'] ?? 'DESC'));
        $direction = $direction === 'ASC' ? 'ASC' : 'DESC';
        $perPageInput = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 25;
        $perPage = max(1, min(200, $perPageInput));
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $offset = ($page - 1) * $perPage;
        $totalCount = 0;

        $entries = $this->context->qa->fetchAdminEntries($keyword, $sort, $direction, $perPage, $offset, $totalCount);
        $totalPages = $perPage > 0 ? (int) max(1, ceil($totalCount / $perPage)) : 1;

        $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $translateId = isset($_GET['translate']) ? (int) $_GET['translate'] : 0;
        $translateLang = (string) ($_GET['tlang'] ?? AppConfig::DEFAULT_LANG);
        if (!array_key_exists($translateLang, $languages)) {
            $translateLang = AppConfig::DEFAULT_LANG;
        }
        $editLang = (string) ($_GET['elang'] ?? AppConfig::DEFAULT_LANG);
        if (!array_key_exists($editLang, $languages)) {
            $editLang = AppConfig::DEFAULT_LANG;
        }
        $uiTargetLang = (string) ($_GET['ui_lang'] ?? AppConfig::DEFAULT_LANG);
        if (!array_key_exists($uiTargetLang, $languages)) {
            $uiTargetLang = AppConfig::DEFAULT_LANG;
        }

        $entryToEditBase = ($view === 'edit' && $editId > 0) ? $this->context->qa->getEntry($editId) : null;
        $entryEditLangs = [];
        $entryEditData = null;
        if ($entryToEditBase !== null) {
            $entryEditLangs = array_merge([AppConfig::DEFAULT_LANG], $this->context->qa->getTranslationLangs($editId));
            if (!in_array($editLang, $entryEditLangs, true)) {
                $editLang = AppConfig::DEFAULT_LANG;
            }
            if ($editLang === AppConfig::DEFAULT_LANG) {
                $entryEditData = $entryToEditBase;
            } else {
                $entryEditData = $this->context->qa->getTranslation($editId, $editLang);
            }
        }
        $entryTags = $entryToEditBase !== null ? $this->context->qa->getTagsForEntry((int) $entryToEditBase['id']) : [];

        $entryToTranslate = ($view === 'translate' && $translateId > 0) ? $this->context->qa->getEntry($translateId) : null;
        $entryTranslationLangs = [];
        $missingTranslationLangs = [];
        if ($entryToTranslate !== null) {
            $entryTranslationLangs = array_values(array_filter(
                $this->context->qa->getTranslationLangs($translateId),
                static fn(string $code): bool => $code !== AppConfig::DEFAULT_LANG
            ));
            $missingTranslationLangs = array_values(array_diff(array_keys($languages), $entryTranslationLangs, [AppConfig::DEFAULT_LANG]));
        }
        $uiStringsPreview = $this->context->uiTranslations->load($uiTargetLang);

        $palette = $this->context->palettes->load();
        $paletteText = $this->context->paletteService->paletteToText($palette);
        $paletteHelp = $this->context->paletteService->helpLines();
        $allTags = $this->context->qa->getAllTags();
        $addTagsInput = $view === 'add' ? trim((string) ($_POST['tags'] ?? '')) : '';

        View::render('admin/index', [
            'notice' => $notice,
            'error' => $error,
            'isAdmin' => $isAdmin,
            'view' => $view,
            'languages' => $languages,
            'entries' => $entries,
            'keyword' => $keyword,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'editId' => $editId,
            'editLang' => $editLang,
            'translateId' => $translateId,
            'translateLang' => $translateLang,
            'entryToEditBase' => $entryToEditBase,
            'entryEditLangs' => $entryEditLangs,
            'entryEditData' => $entryEditData,
            'entryTags' => $entryTags,
            'entryToTranslate' => $entryToTranslate,
            'missingTranslationLangs' => $missingTranslationLangs,
            'uiTargetLang' => $uiTargetLang,
            'uiStringsPreview' => $uiStringsPreview,
            'uiKeys' => AppConfig::UI_KEYS,
            'paletteText' => $paletteText,
            'paletteHelp' => $paletteHelp,
            'paletteSize' => AppConfig::PALETTE_SIZE,
            'allTags' => $allTags,
            'addTagsInput' => $addTagsInput,
            'defaultLang' => AppConfig::DEFAULT_LANG,
        ]);
    }

    private function getClientIp(array $server): string
    {
        $ip = trim((string) ($server['REMOTE_ADDR'] ?? ''));
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }

    private function parseTags(string $input): array
    {
        $parts = preg_split('/[,\r\n]+/', $input);
        if ($parts === false) {
            return [];
        }
        $tags = [];
        foreach ($parts as $part) {
            $tag = Text::sanitizePlainText($part);
            $tag = preg_replace('/\s+/', ' ', $tag) ?? '';
            $tag = trim($tag);
            if ($tag === '') {
                continue;
            }
            $tags[] = $tag;
        }
        return $tags;
    }
}
