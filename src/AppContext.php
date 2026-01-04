<?php
declare(strict_types=1);

namespace App;

use App\Repositories\AdminSessionRepository;
use App\Repositories\LanguageRepository;
use App\Repositories\PaletteRepository;
use App\Repositories\QaRepository;
use App\Repositories\UiTranslationRepository;
use App\Repositories\VoteRepository;
use App\Services\LanguageService;
use App\Services\PaletteService;
use App\Services\UiContextFactory;
use App\Services\VoteService;
use SQLite3;

final class AppContext
{
    public SQLite3 $db;
    public LanguageRepository $languages;
    public UiTranslationRepository $uiTranslations;
    public QaRepository $qa;
    public VoteRepository $votes;
    public VoteService $voteService;
    public PaletteRepository $palettes;
    public PaletteService $paletteService;
    public UiContextFactory $uiContextFactory;
    public AdminSessionRepository $adminSessions;
    public LanguageService $languageService;

    public function __construct(
        SQLite3 $db,
        LanguageRepository $languages,
        UiTranslationRepository $uiTranslations,
        QaRepository $qa,
        VoteRepository $votes,
        VoteService $voteService,
        PaletteRepository $palettes,
        PaletteService $paletteService,
        UiContextFactory $uiContextFactory,
        AdminSessionRepository $adminSessions,
        LanguageService $languageService
    ) {
        $this->db = $db;
        $this->languages = $languages;
        $this->uiTranslations = $uiTranslations;
        $this->qa = $qa;
        $this->votes = $votes;
        $this->voteService = $voteService;
        $this->palettes = $palettes;
        $this->paletteService = $paletteService;
        $this->uiContextFactory = $uiContextFactory;
        $this->adminSessions = $adminSessions;
        $this->languageService = $languageService;
    }
}
