<?php
declare(strict_types=1);

namespace App\Services;

use App\Http\Response;
use App\Repositories\QaRepository;
use App\Repositories\VoteRepository;

final class VoteService
{
    private VoteRepository $votes;
    private QaRepository $qa;

    public function __construct(VoteRepository $votes, QaRepository $qa)
    {
        $this->votes = $votes;
        $this->qa = $qa;
    }

    public function handleVote(array $input, string $redirectPath, callable $paramsBuilder, string $clientIp): void
    {
        $voteId = (int) ($input['id'] ?? 0);
        $voteValue = (int) ($input['vote'] ?? 0);
        $returnParams = $paramsBuilder($input);

        if ($voteId <= 0 || !in_array($voteValue, [1, -1], true)) {
            $returnParams['vote'] = 'invalid';
            Response::redirectWithParams($redirectPath, $returnParams);
        }

        if (!$this->qa->exists($voteId)) {
            $returnParams['vote'] = 'missing';
            Response::redirectWithParams($redirectPath, $returnParams);
        }

        $voterHash = $this->getVoterHash($clientIp);
        if ($voterHash === '') {
            $returnParams['vote'] = 'invalid';
            Response::redirectWithParams($redirectPath, $returnParams);
        }

        if ($this->votes->hasExistingVote($voteId, $voterHash)) {
            $returnParams['vote'] = 'already';
            Response::redirectWithParams($redirectPath, $returnParams);
        }

        $this->votes->insertVote($voteId, $voterHash, $voteValue);
        $returnParams['vote'] = 'ok';
        Response::redirectWithParams($redirectPath, $returnParams);
    }

    public function getStatusMessage(string $status): array
    {
        $notice = '';
        $error = '';
        switch ($status) {
            case 'ok':
                $notice = 'Thanks for voting.';
                break;
            case 'already':
                $error = 'You already voted on this entry.';
                break;
            case 'missing':
                $error = 'This entry is not available anymore.';
                break;
            default:
                $error = 'Unable to process that vote.';
                break;
        }
        return [$notice, $error];
    }

    private function getVoterHash(string $ip): string
    {
        $ip = trim($ip);
        if ($ip === '') {
            return '';
        }
        return hash('sha256', $ip);
    }
}
