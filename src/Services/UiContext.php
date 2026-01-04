<?php
declare(strict_types=1);

namespace App\Services;

final class UiContext
{
    public array $languages;
    public string $uiLang;
    public string $direction;
    public string $uiFontFamily;
    public string $uiFontUrl;
    public Translator $translator;
    public array $palette;
    public string $paletteCss;

    public function __construct(
        array $languages,
        string $uiLang,
        string $direction,
        string $uiFontFamily,
        string $uiFontUrl,
        Translator $translator,
        array $palette,
        string $paletteCss
    ) {
        $this->languages = $languages;
        $this->uiLang = $uiLang;
        $this->direction = $direction;
        $this->uiFontFamily = $uiFontFamily;
        $this->uiFontUrl = $uiFontUrl;
        $this->translator = $translator;
        $this->palette = $palette;
        $this->paletteCss = $paletteCss;
    }
}
