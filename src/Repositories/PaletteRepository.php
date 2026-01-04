<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Services\PaletteService;
use SQLite3;

final class PaletteRepository
{
    private SQLite3 $db;
    private PaletteService $paletteService;

    public function __construct(SQLite3 $db, PaletteService $paletteService)
    {
        $this->db = $db;
        $this->paletteService = $paletteService;
    }

    public function load(): array
    {
        $defaults = $this->paletteService->defaultPalette();
        $row = $this->db->querySingle('SELECT colors FROM pico_palette WHERE id = 1');
        if (!is_string($row) || trim($row) === '') {
            $stmt = $this->db->prepare('INSERT INTO pico_palette (id, colors) VALUES (1, :colors)');
            $stmt->bindValue(':colors', $this->paletteService->paletteToText($defaults), SQLITE3_TEXT);
            $stmt->execute();
            return $defaults;
        }
        $colors = $this->paletteService->parseLines($row);
        return $colors !== [] ? $colors : $defaults;
    }

    public function save(array $colors): void
    {
        $stmt = $this->db->prepare('INSERT INTO pico_palette (id, colors, updated_at) VALUES (1, :colors, CURRENT_TIMESTAMP)
            ON CONFLICT(id) DO UPDATE SET colors = excluded.colors, updated_at = excluded.updated_at');
        $stmt->bindValue(':colors', $this->paletteService->paletteToText($colors), SQLITE3_TEXT);
        $stmt->execute();
    }
}
