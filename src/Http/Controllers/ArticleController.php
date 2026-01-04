<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\AppContext;
use App\View;

final class ArticleController
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
                'article.php',
                fn(array $input) => $this->buildVoteReturnParams($input),
                (string) ($_SERVER['REMOTE_ADDR'] ?? '')
            );
        }

        $entryId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $entry = $entryId > 0 ? $this->context->qa->fetchEntryByLang($entryId, $ui->uiLang) : null;
        $entryTags = $entry !== null ? $this->context->qa->getTagsForEntry($entryId) : [];

        $voteNotice = '';
        $voteError = '';
        if (isset($_GET['vote'])) {
            [$voteNotice, $voteError] = $this->context->voteService->getStatusMessage((string) $_GET['vote']);
        }

        $upvotes = $entry !== null ? (int) ($entry['upvotes'] ?? 0) : 0;
        $downvotes = $entry !== null ? (int) ($entry['downvotes'] ?? 0) : 0;
        $totalVotes = $upvotes + $downvotes;
        $positivePercent = $totalVotes > 0 ? ($upvotes / $totalVotes) * 100 : 0.0;

        $answerMarkdown = $entry !== null ? (string) $entry['answer'] : '';
        $pageTitle = $entry !== null && trim((string) ($entry['question'] ?? '')) !== ''
            ? (string) $entry['question'] . ' - Anti Shuboohat'
            : 'Anti Shuboohat';

        View::render('article', [
            'ui' => $ui,
            'translator' => $ui->translator,
            'entryId' => $entryId,
            'entry' => $entry,
            'entryTags' => $entryTags,
            'voteNotice' => $voteNotice,
            'voteError' => $voteError,
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
            'positivePercent' => $positivePercent,
            'answerMarkdown' => $answerMarkdown,
            'pageTitle' => $pageTitle,
        ]);
    }

    private function buildVoteReturnParams(array $input): array
    {
        $params = [];
        $id = isset($input['id']) ? (int) $input['id'] : 0;
        if ($id > 0) {
            $params['id'] = $id;
        }

        $lang = strtolower(trim((string) ($input['lang'] ?? '')));
        if ($lang !== '') {
            $params['lang'] = $lang;
        }

        return $params;
    }
}
