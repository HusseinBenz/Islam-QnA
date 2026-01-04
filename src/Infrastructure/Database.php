<?php
declare(strict_types=1);

namespace App\Infrastructure;

use SQLite3;

final class Database
{
    public static function connect(string $path): SQLite3
    {
        $db = new SQLite3($path);
        $db->exec('PRAGMA encoding="UTF-8"');
        return $db;
    }

    private function __construct()
    {
    }
}
