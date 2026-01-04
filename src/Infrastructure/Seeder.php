<?php
declare(strict_types=1);

namespace App\Infrastructure;

use SQLite3;

final class Seeder
{
    public static function seed(SQLite3 $db): void
    {
        $existingRows = (int) $db->querySingle('SELECT COUNT(*) FROM qa');
        if ($existingRows > 0) {
            return;
        }

        $seed = $db->prepare('INSERT INTO qa (question, answer) VALUES (:q, :a)');
        $samples = [
            'What is this place?' => 'A tiny Q&A vault you can search. Keep it simple and type what you are curious about.',
            'How do I add my own entries?' => 'Head to admin.php, sign in, and drop your questions and answers there.',
            'Do I need anything fancy?' => 'Nope. It is plain HTML and SQLite under the hood.',
        ];
        foreach ($samples as $question => $answer) {
            $seed->bindValue(':q', $question, SQLITE3_TEXT);
            $seed->bindValue(':a', $answer, SQLITE3_TEXT);
            $seed->execute();
        }

        $bulk = $db->prepare('INSERT INTO qa (question, answer) VALUES (:q, :a)');
        for ($i = 1; $i <= 40; $i++) {
            $question = 'Sample question #' . $i;
            $answer = 'Sample answer body for question #' . $i . ' with a bit more filler text to make pagination meaningful.';
            $bulk->bindValue(':q', $question, SQLITE3_TEXT);
            $bulk->bindValue(':a', $answer, SQLITE3_TEXT);
            $bulk->execute();
        }
    }

    private function __construct()
    {
    }
}
