<?php
use App\Support\Text;
?>
<section>
    <h2>Filter and sort</h2>
    <form method="get">
        <input type="hidden" name="view" value="entries">
        <label>Keyword:
            <input type="text" name="keyword" value="<?php echo $e($keyword); ?>">
        </label>
        <label>Sort by:
            <select name="sort">
                <option value="id"<?php echo $sort === 'id' ? ' selected' : ''; ?>>ID</option>
                <option value="updated_at"<?php echo $sort === 'updated_at' ? ' selected' : ''; ?>>Updated</option>
                <option value="created_at"<?php echo $sort === 'created_at' ? ' selected' : ''; ?>>Created</option>
                <option value="question"<?php echo $sort === 'question' ? ' selected' : ''; ?>>Question</option>
            </select>
        </label>
        <label>Per page:
            <select name="per_page">
                <?php foreach ([10, 25, 50, 100, 200] as $opt): ?>
                    <option value="<?php echo $opt; ?>"<?php echo $perPage === $opt ? ' selected' : ''; ?>><?php echo $opt; ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Direction:
            <select name="direction">
                <option value="DESC"<?php echo $direction === 'DESC' ? ' selected' : ''; ?>>DESC</option>
                <option value="ASC"<?php echo $direction === 'ASC' ? ' selected' : ''; ?>>ASC</option>
            </select>
        </label>
        <button type="submit">Apply</button>
    </form>
</section>

<section>
    <h2>Entries</h2>
    <table class="admin-table" border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Question</th>
            <th>Answer (preview)</th>
            <th>Available languages</th>
            <th>Created</th>
            <th>Updated</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($entries as $row): ?>
            <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td><?php echo $e($row['question']); ?></td>
                <td><?php echo $e(Text::truncate($row['answer'])); ?></td>
                <td><?php echo $e('EN' . (count($row['langs_array']) ? ', ' . implode(', ', array_map('strtoupper', $row['langs_array'])) : '')); ?></td>
                <td><?php echo $e(Text::displayDate($row['created_at'] ?? null)); ?></td>
                <td><?php echo $e(Text::displayDate($row['updated_at'] ?? null)); ?></td>
                <td>
                    <table cellpadding="2" cellspacing="2">
                        <tr>
                            <td>
                                <form method="get">
                                    <input type="hidden" name="view" value="edit">
                                    <input type="hidden" name="edit" value="<?php echo (int) $row['id']; ?>">
                                    <button type="submit">Edit</button>
                                </form>
                            </td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="view" value="entries">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                            <td>
                                <form method="get">
                                    <input type="hidden" name="view" value="translate">
                                    <input type="hidden" name="translate" value="<?php echo (int) $row['id']; ?>">
                                    <input type="hidden" name="tlang" value="<?php echo $e($translateLang); ?>">
                                    <button type="submit">Translate</button>
                                </form>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (count($entries) === 0): ?>
            <tr>
                <td colspan="7">No entries found.</td>
            </tr>
        <?php endif; ?>
    </table>
    <?php
        $baseParams = [
            'view' => 'entries',
            'keyword' => $keyword,
            'sort' => $sort,
            'direction' => $direction,
            'per_page' => $perPage,
        ];
    ?>
    <div class="admin-pagination">
        <div>
            <?php if ($page > 1): ?>
                <?php $prevParams = $baseParams; $prevParams['page'] = $page - 1; ?>
                <a href="?<?php echo $e(http_build_query($prevParams)); ?>">Previous</a>
            <?php else: ?>
                <span class="admin-page-disabled">Previous</span>
            <?php endif; ?>
            <span class="admin-page-info">Page <?php echo (int) $page; ?> of <?php echo (int) $totalPages; ?></span>
            <?php if ($page < $totalPages): ?>
                <?php $nextParams = $baseParams; $nextParams['page'] = $page + 1; ?>
                <a href="?<?php echo $e(http_build_query($nextParams)); ?>">Next</a>
            <?php else: ?>
                <span class="admin-page-disabled">Next</span>
            <?php endif; ?>
        </div>
        <div>Total entries: <?php echo (int) $totalCount; ?></div>
    </div>
</section>
