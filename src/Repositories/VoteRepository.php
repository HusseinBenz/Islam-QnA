<?php
declare(strict_types=1);

namespace App\Repositories;

use SQLite3;

final class VoteRepository
{
    private SQLite3 $db;

    public function __construct(SQLite3 $db)
    {
        $this->db = $db;
    }

    public function hasExistingVote(int $qaId, string $hash): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM qa_votes WHERE qa_id = :id AND voter_hash = :hash');
        $stmt->bindValue(':id', $qaId, SQLITE3_INTEGER);
        $stmt->bindValue(':hash', $hash, SQLITE3_TEXT);
        $res = $stmt->execute();
        $row = $res !== false ? $res->fetchArray(SQLITE3_NUM) : false;
        return $row !== false && (int) $row[0] > 0;
    }

    public function insertVote(int $qaId, string $hash, int $vote): void
    {
        $stmt = $this->db->prepare('INSERT INTO qa_votes (qa_id, voter_hash, vote) VALUES (:id, :hash, :vote)');
        $stmt->bindValue(':id', $qaId, SQLITE3_INTEGER);
        $stmt->bindValue(':hash', $hash, SQLITE3_TEXT);
        $stmt->bindValue(':vote', $vote, SQLITE3_INTEGER);
        $stmt->execute();
    }
}
