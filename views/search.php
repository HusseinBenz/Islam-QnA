<?php
use App\Support\Css;
use App\Support\Text;
?>
<!doctype html>
<html lang="<?php echo $e($ui->uiLang); ?>" dir="<?php echo $e($ui->direction); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $e($t('title')); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <?php if ($ui->uiFontUrl !== ''): ?>
        <link rel="stylesheet" href="<?php echo $e($ui->uiFontUrl); ?>">
    <?php endif; ?>
    <style>
        body {
            font-family: <?php echo Css::fontFamily($ui->uiFontFamily); ?>;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .content-area {
            flex: 1;
            display: flex;
        }
        .content-wrap {
            width: 70%;
            margin: auto;
            padding: 16px 0;
            text-align: center;
        }
        footer {
            width: 70%;
            margin: 0 auto;
            padding: 12px 0 0;
            text-align: center;
        }
        button,
        input[type="submit"],
        input[type="button"],
        input[type="reset"] {
            width: auto;
            display: inline-block;
            font-size: 0.9rem;
            padding: 0.35rem 0.7rem;
        }
        .theme-toggle-single {
            position: fixed;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            padding: 0;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 20;
        }
        .entry-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }
        .entry-details {
            flex: 1 1 auto;
            text-align: left;
        }
        .entry-question a {
            font-weight: 600;
            text-decoration: none;
        }
        .entry-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 6px;
        }
        .entry-tag {
            color: #b02a63;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .entry-created {
            font-size: 0.85em;
            font-style: italic;
        }
        .entry-score {
            min-width: 48px;
            text-align: right;
            font-size: 0.95em;
            font-weight: 600;
            align-self: center;
        }
        .entry-score-positive {
            color: #1b7f3a;
        }
        .entry-score-negative {
            color: #b42318;
        }
        .entry-score-neutral {
            color: #777;
        }
        .search-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }
        .search-form input[type="search"] {
            width: 100%;
            min-width: 0;
        }
        .search-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .search-actions .search-btn {
            width: auto;
            font-size: 0.8rem;
            padding: 0.25rem 0.6rem;
        }
        .pagination-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            margin-top: 10px;
            flex-wrap: nowrap;
        }
        .page-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .page-info {
            white-space: nowrap;
        }
        .page-disabled {
            color: #777;
        }
        .per-page form {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        .per-page label {
            margin: 0;
            white-space: nowrap;
        }
        .per-page select {
            
        }
        .lang-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .lang-links a {
            text-decoration: underline;
        }
        @media (max-width: 720px) {
            .entry-row {
                flex-direction: column;
                align-items: stretch;
            }
            .entry-score {
                align-self: flex-start;
                text-align: left;
            }
            .pagination-bar {
                flex-wrap: wrap;
                justify-content: center;
            }
            .content-wrap {
                width: 100%;
                padding: 0 12px;
            }
            footer {
                width: 100%;
                padding: 12px 12px 0;
            }
        }
        [dir="rtl"] .entry-row {
            flex-direction: row-reverse;
        }
        [dir="rtl"] .entry-details {
            text-align: right;
        }
        [dir="rtl"] .entry-score {
            text-align: left;
        }
        [dir="rtl"] .page-nav {
            flex-direction: row-reverse;
        }
        [dir="rtl"] .pagination-bar {
            flex-direction: row;
        }
        [dir="rtl"] .per-page form {
            flex-direction: row;
        }
    </style>
</head>
<body>
<button type="button" id="theme-toggle" class="theme-toggle-single" aria-label="Toggle theme">&#x2600;</button>
<div class="content-area">
    <div class="content-wrap">
        <header>
            <h1><?php echo $e($t('title')); ?></h1>
            <p><?php echo $e($t('tagline')); ?></p>
            <form method="get" class="search-form">
                <input type="hidden" name="lang" value="<?php echo $e($ui->uiLang); ?>">
                <input type="search" name="q" value="<?php echo $e($searchQuery); ?>" size="50" placeholder="<?php echo $e($t('placeholder')); ?>" autofocus>
                <div class="search-actions">
                    <button type="submit" class="search-btn"><?php echo $e($t('search')); ?></button>
                    <button type="submit" name="browse" value="1" class="search-btn"><?php echo $e($t('browse')); ?></button>
                </div>
                <input type="hidden" name="per_page" value="<?php echo (int) $perPage; ?>">
            </form>
        </header>

                <?php if ($voteNotice !== ''): ?>
                    <p><?php echo $e($voteNotice); ?></p>
                <?php endif; ?>
                <?php if ($voteError !== ''): ?>
                    <p><?php echo $e($voteError); ?></p>
                <?php endif; ?>

                <section>
                    <?php if ($searchQuery !== ''): ?>
                        <p><?php echo $e($t('results_for')); ?> "<?php echo $e($searchQuery); ?>"</p>
                    <?php elseif ($browsing): ?>
                        <p><?php echo $e($t('browse')); ?>.</p>
                    <?php else: ?>
                        <p><?php echo $e($t('use_search_or_browse')); ?></p>
                    <?php endif; ?>

                    <?php if ($searchQuery !== '' || $browsing): ?>
                        <?php if (count($results) === 0): ?>
                            <p><?php echo $e($t('no_results')); ?></p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($results as $row): ?>
                                    <?php $articleParams = ['id' => $row['id'], 'lang' => $ui->uiLang]; ?>
                                    <li class="entry-row">
                                        <div class="entry-details">
                                            <div class="entry-question"><a href="article.php?<?php echo $e(http_build_query($articleParams)); ?>"><?php echo $e($row['question']); ?></a></div>
                                            <?php if (!empty($row['tags'])): ?>
                                                <div class="entry-tags">
                                                    <?php foreach ($row['tags'] as $tag): ?>
                                                        <span class="entry-tag"><?php echo $e($tag); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="entry-created"><?php echo $e($t('created_label')); ?> <?php echo $e(Text::displayDate($row['created_at'] ?? null)); ?></div>
                                        </div>
                                        <?php
                                            $score = (int) $row['score'];
                                            $scoreClass = $score > 0 ? 'entry-score-positive' : ($score < 0 ? 'entry-score-negative' : 'entry-score-neutral');
                                        ?>
                                        <div class="entry-score <?php echo $e($scoreClass); ?>"><?php echo $score; ?></div>
                                    </li>
                                    <hr>
                                <?php endforeach; ?>
                            </ul>

                            <?php
                                $baseParams = ['lang' => $ui->uiLang, 'per_page' => $perPage];
                                if ($browsing) {
                                    $baseParams['browse'] = '1';
                                }
                                if ($searchQuery !== '') {
                                    $baseParams['q'] = $searchQuery;
                                }
                            ?>
                            <div class="pagination-bar">
                                <div class="page-nav">
                                    <?php if ($page > 1): ?>
                                        <?php $prevParams = $baseParams; $prevParams['page'] = $page - 1; ?>
                                        <a href="?<?php echo $e(http_build_query($prevParams)); ?>"><?php echo $e($t('previous')); ?></a>
                                    <?php else: ?>
                                        <span class="page-disabled"><?php echo $e($t('previous')); ?></span>
                                    <?php endif; ?>
                                    <span class="page-info"><?php echo $e($tf('page_of', (int) $page, (int) $totalPages)); ?></span>
                                    <?php if ($page < $totalPages): ?>
                                        <?php $nextParams = $baseParams; $nextParams['page'] = $page + 1; ?>
                                        <a href="?<?php echo $e(http_build_query($nextParams)); ?>"><?php echo $e($t('next')); ?></a>
                                    <?php else: ?>
                                        <span class="page-disabled"><?php echo $e($t('next')); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="per-page">
                                    <form method="get">
                                        <input type="hidden" name="lang" value="<?php echo $e($ui->uiLang); ?>">
                                        <?php if ($searchQuery !== ''): ?>
                                            <input type="hidden" name="q" value="<?php echo $e($searchQuery); ?>">
                                        <?php endif; ?>
                                        <?php if ($browsing): ?>
                                            <input type="hidden" name="browse" value="1">
                                        <?php endif; ?>
                                        <label for="per-page-select"><?php echo $e($t('results_per_page')); ?></label>
                                        <select id="per-page-select" name="per_page" onchange="this.form.submit()">
                                            <?php foreach ([5, 10, 25, 50, 100] as $opt): ?>
                                                <option value="<?php echo $opt; ?>"<?php echo $perPage === $opt ? ' selected' : ''; ?>><?php echo $opt; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>
    </div>
</div>

<footer>
    <p><?php echo $e($t('languages')); ?></p>
    <?php
        $langBaseParams = [];
        if ($searchQuery !== '') {
            $langBaseParams['q'] = $searchQuery;
        }
        if ($browsing) {
            $langBaseParams['browse'] = '1';
        }
        $langBaseParams['per_page'] = $perPage;
        $langBaseParams['page'] = $page;
    ?>
    <div class="lang-links">
        <?php foreach ($ui->languages as $code => $meta): ?>
            <?php $langParams = $langBaseParams; $langParams['lang'] = $code; ?>
            <a href="?<?php echo $e(http_build_query($langParams)); ?>"><?php echo $e($meta['label']); ?></a>
        <?php endforeach; ?>
    </div>
</footer>
<script>
    (function () {
        var root = document.documentElement;
        var toggle = document.getElementById('theme-toggle');
        if (!toggle) {
            return;
        }
        var saved = '';
        try {
            saved = localStorage.getItem('theme') || '';
        } catch (err) {
            saved = '';
        }
        var theme = saved === 'light' || saved === 'dark' ? saved : '';
        if (theme === '') {
            theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        function applyTheme(value) {
            root.setAttribute('data-theme', value);
            toggle.innerHTML = value === 'dark' ? '&#x1F319;' : '&#x2600;';
        }
        applyTheme(theme);
        toggle.addEventListener('click', function () {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            try {
                localStorage.setItem('theme', next);
            } catch (err) {
                return;
            }
        });
    })();
</script>
</body>
</html>
