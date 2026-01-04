<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\AppContext;
use App\View;

final class SearchController
{
    private AppContext $context;

    public function __construct(AppContext $context)
    {
        $this->context = $context;
    }

    public function handle(): void
    {
        header('Content-Type: text/html; charset=UTF-8');

        $ui = $this->context->uiContextFactory->build($_GET, $_SERVER);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'vote') {
            $this->context->voteService->handleVote(
                $_POST,
                'index.php',
                fn(array $input) => $this->buildVoteReturnParams($input),
                (string) ($_SERVER['REMOTE_ADDR'] ?? '')
            );
        }

        $searchQuery = trim((string) ($_GET['q'] ?? ''));
        $browsing = isset($_GET['browse']);
        $perPageInput = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        $perPage = max(1, min(100, $perPageInput));
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $offset = ($page - 1) * $perPage;
        $totalCount = 0;
        $results = [];
        if ($searchQuery !== '' || $browsing) {
            $results = $this->context->qa->fetchPublicEntries($ui->uiLang, $searchQuery, $perPage, $offset, $totalCount);
        }
        if ($results !== []) {
            $tagMap = $this->context->qa->getTagsForEntries(array_column($results, 'id'));
            foreach ($results as &$row) {
                $id = (int) ($row['id'] ?? 0);
                $row['tags'] = $tagMap[$id] ?? [];
            }
            unset($row);
        }
        $totalPages = $perPage > 0 ? (int) max(1, ceil($totalCount / $perPage)) : 1;

        $voteNotice = '';
        $voteError = '';
        if (isset($_GET['vote'])) {
            [$voteNotice, $voteError] = $this->context->voteService->getStatusMessage((string) $_GET['vote']);
        }

        View::render('search', [
            'ui' => $ui,
            'translator' => $ui->translator,
            'searchQuery' => $searchQuery,
            'browsing' => $browsing,
            'perPage' => $perPage,
            'page' => $page,
            'totalPages' => $totalPages,
            'results' => $results,
            'voteNotice' => $voteNotice,
            'voteError' => $voteError,
        ]);
    }

    private function buildVoteReturnParams(array $input): array
    {
        $params = [];
        $lang = strtolower(trim((string) ($input['lang'] ?? '')));
        if ($lang !== '') {
            $params['lang'] = $lang;
        }

        $query = trim((string) ($input['q'] ?? ''));
        if ($query !== '') {
            $params['q'] = $query;
        }

        if (!empty($input['browse'])) {
            $params['browse'] = '1';
        }

        $perPage = isset($input['per_page']) ? (int) $input['per_page'] : 10;
        $perPage = max(1, min(100, $perPage));
        $params['per_page'] = $perPage;

        $page = isset($input['page']) ? max(1, (int) $input['page']) : 1;
        if ($page > 1) {
            $params['page'] = $page;
        }

        return $params;
    }
}
