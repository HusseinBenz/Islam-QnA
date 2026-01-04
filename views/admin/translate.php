<?php
use App\Support\Text;
?>
<section>
    <h2>Translate entry</h2>
    <form method="get">
        <input type="hidden" name="view" value="translate">
        <p>
            <label>Select entry:
                <select name="translate">
                    <option value="">Pick entry</option>
                    <?php foreach ($entries as $row): ?>
                        <option value="<?php echo (int) $row['id']; ?>"<?php echo $translateId === (int) $row['id'] ? ' selected' : ''; ?>>#<?php echo (int) $row['id']; ?> - <?php echo $e(Text::truncate($row['question'], 40)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <button type="submit">Load</button>
    </form>

    <?php if ($entryToTranslate !== null): ?>
        <h3>Add new translation</h3>
        <?php if (count($missingTranslationLangs) === 0): ?>
            <p>All configured languages already have translations.</p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="action" value="translate">
                <input type="hidden" name="view" value="translate">
                <input type="hidden" name="qa_id" value="<?php echo (int) $entryToTranslate['id']; ?>">
                <p>Language:
                    <select name="lang">
                        <?php foreach ($missingTranslationLangs as $code): ?>
                            <option value="<?php echo $e($code); ?>"><?php echo $e($languages[$code]['label'] ?? $code); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>Translated question:<br>
                    <textarea name="t_question" rows="3" cols="80"><?php echo $e($entryToTranslate['question']); ?></textarea>
                </p>
                <p>Translated answer (Markdown):<br>
                    <textarea name="t_answer" rows="5" cols="80"><?php echo $e($entryToTranslate['answer']); ?></textarea>
                </p>
                <button type="submit">Save translation</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</section>
