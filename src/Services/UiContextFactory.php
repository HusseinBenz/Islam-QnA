<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\AppConfig;
use App\Repositories\LanguageRepository;
use App\Repositories\PaletteRepository;
use App\Repositories\UiTranslationRepository;

final class UiContextFactory
{
    private LanguageRepository $languages;
    private UiTranslationRepository $uiTranslations;
    private PaletteRepository $palettes;
    private LanguageService $languageService;
    private PaletteService $paletteService;

    public function __construct(
        LanguageRepository $languages,
        UiTranslationRepository $uiTranslations,
        PaletteRepository $palettes,
        LanguageService $languageService,
        PaletteService $paletteService
    ) {
        $this->languages = $languages;
        $this->uiTranslations = $uiTranslations;
        $this->palettes = $palettes;
        $this->languageService = $languageService;
        $this->paletteService = $paletteService;
    }

    public function build(array $query, array $server): UiContext
    {
        $languages = $this->languages->all();
        $requested = (string) ($query['lang'] ?? '');
        $uiLang = $this->languageService->resolveUiLang($requested, $languages, AppConfig::DEFAULT_LANG, $server);

        $direction = $languages[$uiLang]['dir'] ?? 'ltr';
        $fontDefaults = $this->languageService->defaultUiFontConfig($uiLang);
        $uiFontFamily = $languages[$uiLang]['ui_font'] ?? $fontDefaults['font'];
        $uiFontUrl = $languages[$uiLang]['ui_font_url'] ?? $fontDefaults['url'];

        $uiStrings = $this->uiTranslations->load($uiLang);
        $translator = new Translator($uiStrings);

        $palette = $this->palettes->load();
        $paletteCss = $this->paletteService->renderCss($palette);

        return new UiContext(
            $languages,
            $uiLang,
            $direction,
            $uiFontFamily,
            $uiFontUrl,
            $translator,
            $palette,
            $paletteCss
        );
    }
}
