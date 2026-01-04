<?php
declare(strict_types=1);

namespace App;

use App\Infrastructure\Database;
use App\Infrastructure\Schema;
use App\Infrastructure\Seeder;
use App\Repositories\AdminSessionRepository;
use App\Repositories\LanguageRepository;
use App\Repositories\PaletteRepository;
use App\Repositories\QaRepository;
use App\Repositories\UiTranslationRepository;
use App\Repositories\VoteRepository;
use App\Services\GeoService;
use App\Services\LanguageService;
use App\Services\PaletteService;
use App\Services\UiContextFactory;
use App\Services\VoteService;

final class App
{
    public static function bootstrap(string $rootPath): AppContext
    {
        $dbPath = $rootPath . DIRECTORY_SEPARATOR . 'qa.sqlite';
        $db = Database::connect($dbPath);
        Schema::setup($db);
        Seeder::seed($db);

        $geo = new GeoService();
        $languageService = new LanguageService($geo);
        $languageRepo = new LanguageRepository($db, $languageService);
        $languageRepo->ensureDefaults();

        $uiTranslations = new UiTranslationRepository($db);
        $uiTranslations->ensureDefaults();

        $paletteService = new PaletteService();
        $paletteRepo = new PaletteRepository($db, $paletteService);

        $qaRepo = new QaRepository($db);
        $voteRepo = new VoteRepository($db);
        $voteService = new VoteService($voteRepo, $qaRepo);
        $adminSessions = new AdminSessionRepository($db);

        $uiContextFactory = new UiContextFactory(
            $languageRepo,
            $uiTranslations,
            $paletteRepo,
            $languageService,
            $paletteService
        );

        return new AppContext(
            $db,
            $languageRepo,
            $uiTranslations,
            $qaRepo,
            $voteRepo,
            $voteService,
            $paletteRepo,
            $paletteService,
            $uiContextFactory,
            $adminSessions,
            $languageService
        );
    }

    private function __construct()
    {
    }
}
