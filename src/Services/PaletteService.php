<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\AppConfig;

final class PaletteService
{
    public function defaultPalette(): array
    {
        return [
            '#0c1c2c',
            '#0c1c2c',
            '#3a82e4',
            '#40b2a4',
            '#eff2ec',
            '#dedad0',
            '#d0e6da',
            '#085856',
            '#24366e',
            '#143e50',
            '#7e245c',
            '#d25238',
            '#eeca4a',
        ];
    }

    public function parseLines(string $input): array
    {
        $lines = preg_split('/\\r\\n|\\r|\\n/', $input);
        if ($lines === false) {
            return [];
        }
        $colors = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $color = $this->normalizeHex($line);
            if ($color === '') {
                return [];
            }
            $colors[] = $color;
        }
        if (count($colors) !== AppConfig::PALETTE_SIZE) {
            return [];
        }
        return $colors;
    }

    public function paletteToText(array $colors): string
    {
        return implode("\n", $colors);
    }

    public function helpLines(): array
    {
        return [
            'Base dark for contrast and dark backgrounds.',
            'Primary ink color for body text.',
            'Primary brand color for links and buttons.',
            'Secondary accent color.',
            'Light background color.',
            'Light border and divider color.',
            'Soft surface color for cards/inputs.',
            'Positive accent for success/ins states.',
            'Primary hover color.',
            'Muted dark for subtle text and dark borders.',
            'Accent color for code/emphasis.',
            'Danger color for errors/deletions.',
            'Highlight color for marks/badges.',
        ];
    }

    public function renderCss(array $colors): string
    {
        if (count($colors) !== AppConfig::PALETTE_SIZE) {
            $colors = $this->defaultPalette();
        }
        [
            $baseDark,
            $ink,
            $primary,
            $secondary,
            $lightBg,
            $lightBorder,
            $softSurface,
            $positive,
            $primaryHover,
            $mutedDark,
            $accent,
            $danger,
            $highlight,
        ] = array_values($colors);

        $primaryFocusLight = $this->rgba($primary, 0.35);
        $primaryFocusDark = $this->rgba($primary, 0.45);
        $secondaryFocusLight = $this->rgba($secondary, 0.35);
        $secondaryFocusDark = $this->rgba($secondary, 0.45);
        $contrastFocusLight = $this->rgba($baseDark, 0.35);
        $contrastFocusDark = $this->rgba($lightBg, 0.4);
        $selectionLight = $this->rgba($primary, 0.35);
        $selectionDark = $this->rgba($primary, 0.45);
        $overlayLight = $this->rgba($baseDark, 0.35);
        $overlayDark = $this->rgba($baseDark, 0.7);

        return <<<CSS
:root:not([data-theme=dark]),
[data-theme=light] {
    color-scheme: light;
    --pico-background-color: {$lightBg};
    --pico-color: {$ink};
    --pico-text-selection-color: {$selectionLight};
    --pico-muted-color: {$mutedDark};
    --pico-muted-border-color: {$lightBorder};
    --pico-primary: {$primary};
    --pico-primary-background: {$primary};
    --pico-primary-border: {$primary};
    --pico-primary-underline: {$selectionLight};
    --pico-primary-hover: {$primaryHover};
    --pico-primary-hover-background: {$primaryHover};
    --pico-primary-hover-border: {$primaryHover};
    --pico-primary-hover-underline: {$primaryHover};
    --pico-primary-focus: {$primaryFocusLight};
    --pico-primary-inverse: {$lightBg};
    --pico-secondary: {$secondary};
    --pico-secondary-background: {$secondary};
    --pico-secondary-border: {$secondary};
    --pico-secondary-underline: {$secondaryFocusLight};
    --pico-secondary-hover: {$positive};
    --pico-secondary-hover-background: {$positive};
    --pico-secondary-hover-border: {$positive};
    --pico-secondary-hover-underline: {$positive};
    --pico-secondary-focus: {$secondaryFocusLight};
    --pico-secondary-inverse: {$lightBg};
    --pico-contrast: {$baseDark};
    --pico-contrast-background: {$baseDark};
    --pico-contrast-border: {$baseDark};
    --pico-contrast-underline: {$contrastFocusLight};
    --pico-contrast-hover: {$mutedDark};
    --pico-contrast-hover-background: {$mutedDark};
    --pico-contrast-hover-border: {$mutedDark};
    --pico-contrast-hover-underline: {$mutedDark};
    --pico-contrast-focus: {$contrastFocusLight};
    --pico-contrast-inverse: {$lightBg};
    --pico-card-background-color: {$lightBg};
    --pico-card-sectioning-background-color: {$softSurface};
    --pico-code-background-color: {$softSurface};
    --pico-code-color: {$accent};
    --pico-form-element-background-color: {$lightBg};
    --pico-form-element-selected-background-color: {$softSurface};
    --pico-form-element-border-color: {$lightBorder};
    --pico-form-element-color: {$ink};
    --pico-form-element-placeholder-color: {$mutedDark};
    --pico-form-element-active-background-color: {$lightBg};
    --pico-form-element-active-border-color: {$primary};
    --pico-form-element-focus-color: {$primary};
    --pico-switch-background-color: {$lightBorder};
    --pico-switch-checked-background-color: {$primary};
    --pico-range-border-color: {$lightBorder};
    --pico-range-active-border-color: {$softSurface};
    --pico-range-thumb-color: {$secondary};
    --pico-range-thumb-active-color: {$primary};
    --pico-progress-background-color: {$lightBorder};
    --pico-progress-color: {$primary};
    --pico-mark-background-color: {$highlight};
    --pico-mark-color: {$baseDark};
    --pico-ins-color: {$positive};
    --pico-del-color: {$danger};
    --pico-modal-overlay-background-color: {$overlayLight};
    --pico-dropdown-background-color: {$lightBg};
    --pico-dropdown-border-color: {$lightBorder};
    --pico-dropdown-hover-background-color: {$softSurface};
}
[data-theme=dark] {
    color-scheme: dark;
    --pico-background-color: {$baseDark};
    --pico-color: {$lightBg};
    --pico-text-selection-color: {$selectionDark};
    --pico-muted-color: {$softSurface};
    --pico-muted-border-color: {$mutedDark};
    --pico-primary: {$primary};
    --pico-primary-background: {$primary};
    --pico-primary-border: {$primary};
    --pico-primary-underline: {$selectionDark};
    --pico-primary-hover: {$secondary};
    --pico-primary-hover-background: {$secondary};
    --pico-primary-hover-border: {$secondary};
    --pico-primary-hover-underline: {$secondary};
    --pico-primary-focus: {$primaryFocusDark};
    --pico-primary-inverse: {$baseDark};
    --pico-secondary: {$secondary};
    --pico-secondary-background: {$secondary};
    --pico-secondary-border: {$secondary};
    --pico-secondary-underline: {$secondaryFocusDark};
    --pico-secondary-hover: {$highlight};
    --pico-secondary-hover-background: {$highlight};
    --pico-secondary-hover-border: {$highlight};
    --pico-secondary-hover-underline: {$highlight};
    --pico-secondary-focus: {$secondaryFocusDark};
    --pico-secondary-inverse: {$baseDark};
    --pico-contrast: {$lightBg};
    --pico-contrast-background: {$lightBg};
    --pico-contrast-border: {$lightBg};
    --pico-contrast-underline: {$contrastFocusDark};
    --pico-contrast-hover: {$softSurface};
    --pico-contrast-hover-background: {$softSurface};
    --pico-contrast-hover-border: {$softSurface};
    --pico-contrast-hover-underline: {$softSurface};
    --pico-contrast-focus: {$contrastFocusDark};
    --pico-contrast-inverse: {$baseDark};
    --pico-card-background-color: {$baseDark};
    --pico-card-sectioning-background-color: {$mutedDark};
    --pico-code-background-color: {$mutedDark};
    --pico-code-color: {$accent};
    --pico-form-element-background-color: {$baseDark};
    --pico-form-element-selected-background-color: {$mutedDark};
    --pico-form-element-border-color: {$mutedDark};
    --pico-form-element-color: {$lightBg};
    --pico-form-element-placeholder-color: {$softSurface};
    --pico-form-element-active-background-color: {$baseDark};
    --pico-form-element-active-border-color: {$primary};
    --pico-form-element-focus-color: {$primary};
    --pico-switch-background-color: {$mutedDark};
    --pico-switch-checked-background-color: {$primary};
    --pico-range-border-color: {$mutedDark};
    --pico-range-active-border-color: {$primaryHover};
    --pico-range-thumb-color: {$secondary};
    --pico-range-thumb-active-color: {$primary};
    --pico-progress-background-color: {$mutedDark};
    --pico-progress-color: {$primary};
    --pico-mark-background-color: {$highlight};
    --pico-mark-color: {$baseDark};
    --pico-ins-color: {$secondary};
    --pico-del-color: {$danger};
    --pico-modal-overlay-background-color: {$overlayDark};
    --pico-dropdown-background-color: {$baseDark};
    --pico-dropdown-border-color: {$mutedDark};
    --pico-dropdown-hover-background-color: {$mutedDark};
}
CSS;
    }

    private function normalizeHex(string $value): string
    {
        $value = strtolower(trim($value));
        if ($value === '') {
            return '';
        }
        if ($value[0] !== '#') {
            $value = '#' . $value;
        }
        if (preg_match('/^#[0-9a-f]{6}$/', $value) === 1) {
            return $value;
        }
        if (preg_match('/^#[0-9a-f]{3}$/', $value) === 1) {
            return '#' . $value[1] . $value[1] . $value[2] . $value[2] . $value[3] . $value[3];
        }
        return '';
    }

    private function hexToRgb(string $hex): array
    {
        $hex = $this->normalizeHex($hex);
        if ($hex === '') {
            return [0, 0, 0];
        }
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function rgba(string $hex, float $alpha): string
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $alpha = max(0.0, min(1.0, $alpha));
        return sprintf('rgba(%d, %d, %d, %.2f)', $r, $g, $b, $alpha);
    }
}
