<section>
    <h2>UI translations</h2>
    <form method="get">
        <input type="hidden" name="view" value="ui">
        <label>Language:
            <select name="ui_lang">
                <?php foreach ($languages as $code => $meta): ?>
                    <option value="<?php echo $e($code); ?>"<?php echo $uiTargetLang === $code ? ' selected' : ''; ?>><?php echo $e($meta['label']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Load UI strings</button>
    </form>

    <form method="post">
        <input type="hidden" name="action" value="save_ui">
        <input type="hidden" name="view" value="ui">
        <input type="hidden" name="lang" value="<?php echo $e($uiTargetLang); ?>">
        <?php foreach ($uiKeys as $key): ?>
            <p>
                <label><?php echo $e(ucwords(str_replace('_', ' ', $key))); ?>:<br>
                    <input type="text" name="ui_<?php echo $e($key); ?>" size="60" value="<?php echo $e($uiStringsPreview[$key] ?? ''); ?>">
                </label>
            </p>
        <?php endforeach; ?>
        <button type="submit">Save UI strings</button>
    </form>
</section>
