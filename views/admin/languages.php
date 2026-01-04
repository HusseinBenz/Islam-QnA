<section>
    <h2>Languages</h2>
    <table class="admin-table" border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Code</th>
            <th>Label</th>
            <th>Direction</th>
            <th>UI font</th>
            <th>Google font URL</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($languages as $code => $meta): ?>
            <?php $formId = 'lang-edit-' . $code; ?>
            <tr>
                <td>
                    <input type="text" name="code" form="<?php echo $e($formId); ?>" value="<?php echo $e($code); ?>" size="6">
                </td>
                <td>
                    <input type="text" name="label" form="<?php echo $e($formId); ?>" value="<?php echo $e($meta['label']); ?>" size="18">
                </td>
                <td>
                    <select name="dir" form="<?php echo $e($formId); ?>">
                        <option value="ltr"<?php echo $meta['dir'] === 'ltr' ? ' selected' : ''; ?>>LTR</option>
                        <option value="rtl"<?php echo $meta['dir'] === 'rtl' ? ' selected' : ''; ?>>RTL</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="ui_font" form="<?php echo $e($formId); ?>" value="<?php echo $e($meta['ui_font']); ?>" size="28">
                </td>
                <td>
                    <input type="text" name="ui_font_url" form="<?php echo $e($formId); ?>" value="<?php echo $e($meta['ui_font_url']); ?>" size="40">
                </td>
                <td>
                    <form method="post" id="<?php echo $e($formId); ?>">
                        <input type="hidden" name="action" value="update_language">
                        <input type="hidden" name="view" value="languages">
                        <input type="hidden" name="original_code" value="<?php echo $e($code); ?>">
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <h3>Add language</h3>
    <form method="post">
        <input type="hidden" name="action" value="add_language">
        <input type="hidden" name="view" value="languages">
        <p>
            <label>Code:
                <input type="text" name="code" placeholder="en">
            </label>
        </p>
        <p>
            <label>Label:
                <input type="text" name="label" placeholder="English">
            </label>
        </p>
        <p>
            <label>Direction:
                <select name="dir">
                    <option value="ltr">LTR</option>
                    <option value="rtl">RTL</option>
                </select>
            </label>
        </p>
        <p>
            <label>UI font family:
                <input type="text" name="ui_font" placeholder="'Noto Sans', sans-serif" size="40">
            </label>
        </p>
        <p>
            <label>Google Fonts URL:
                <input type="text" name="ui_font_url" placeholder="https://fonts.googleapis.com/css2?family=..." size="60">
            </label>
        </p>
        <button type="submit">Save language</button>
    </form>
</section>
