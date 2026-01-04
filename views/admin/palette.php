<section>
    <h2>Pico palette</h2>
    <form method="post">
        <input type="hidden" name="action" value="save_palette">
        <input type="hidden" name="view" value="palette">
        <p>
            <label>Hex colors (<?php echo (int) $paletteSize; ?> lines, one per line):<br>
                <textarea name="palette" rows="14" cols="40"><?php echo $e($paletteText); ?></textarea>
            </label>
        </p>
        <p>Line guide:</p>
        <ol>
            <?php foreach ($paletteHelp as $line): ?>
                <li><?php echo $e($line); ?></li>
            <?php endforeach; ?>
        </ol>
        <button type="submit">Save palette</button>
    </form>
</section>
