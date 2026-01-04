<?php
use App\Support\Css;
use App\Support\Text;
?>
<!doctype html>
<html lang="<?php echo $e($ui->uiLang); ?>" dir="<?php echo $e($ui->direction); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $e($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <?php if ($ui->uiFontUrl !== ''): ?>
        <link rel="stylesheet" href="<?php echo $e($ui->uiFontUrl); ?>">
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/marked@4.3.0/marked.min.js"></script>
    <style>
        body {
            font-family: <?php echo Css::fontFamily($ui->uiFontFamily); ?>;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1 0 auto;
        }
        .site-header,
        main,
        footer,
        body > p {
            width: 70%;
            margin-left: auto;
            margin-right: auto;
        }
        .site-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 24px 0 16px;
        }
        .site-brand {
            font-size: 1.35rem;
            font-weight: 600;
            margin: 0;
        }
        .site-brand a {
            text-decoration: none;
        }
        .site-nav {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .entry-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 8px 0 12px;
        }
        .entry-tag {
            color: #b02a63;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
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
        .entry-meta {
            font-size: 0.85em;
            font-style: italic;
        }
        .article-rating {
            margin-top: 16px;
            display: flex;
            justify-content: center;
        }
        .rating-component {
            width: 100px;
            max-width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .rating-buttons {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 0px;
        }
        .rating-component .button {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            color: #909090;
            background: none;
            border: none;
            box-shadow: none;
            padding: 0;
            text-decoration: none;
            font: inherit;
        }
        .rating-component .rate-icon {
            width: 20px;
        }
        .rating-component .rate-icon svg {
            fill: #909090;
        }
        #rate-count-positive,
        #rate-count-negative {
            font-size: 13px;
            text-transform: uppercase;
            user-select: none;
        }
        .sentiment {
            height: 2.5px;
            width: 100%;
            background: #606060;
            position: relative;
        }
        #positive-bar {
            position: absolute;
            background: #53a7ff;
            height: inherit;
        }
        footer {
            padding: 12px 0 0;
            text-align: center;
            margin-top: auto;
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
            .site-header,
            main,
            footer,
            body > p {
                width: 100%;
            }
            .site-header {
                padding: 16px 12px 12px;
            }
            main {
                padding: 0 12px;
            }
            footer {
                padding: 12px 12px 0;
            }
            body > p {
                padding: 0 12px;
            }
        }
    </style>
</head>
<body>
<button type="button" id="theme-toggle" class="theme-toggle-single" aria-label="Toggle theme">&#x2600;</button>
<header class="site-header">
    <div class="site-brand">
        <a href="index.php?<?php echo $e(http_build_query(['lang' => $ui->uiLang])); ?>">Anti Shuboohat</a>
    </div>
    <nav class="site-nav">
        <a href="index.php?<?php echo $e(http_build_query(['lang' => $ui->uiLang])); ?>"><?php echo $e($t('search')); ?></a>
        <a href="index.php?<?php echo $e(http_build_query(['lang' => $ui->uiLang, 'browse' => '1'])); ?>"><?php echo $e($t('browse')); ?></a>
    </nav>
</header>

<?php if ($voteNotice !== ''): ?>
    <p><?php echo $e($voteNotice); ?></p>
<?php endif; ?>
<?php if ($voteError !== ''): ?>
    <p><?php echo $e($voteError); ?></p>
<?php endif; ?>

<main>
    <?php if ($entry === null): ?>
        <p><?php echo $e($t('entry_missing')); ?></p>
    <?php else: ?>
        <article>
            <h1><?php echo $e($entry['question']); ?></h1>
            <?php if (!empty($entryTags)): ?>
                <div class="entry-tags">
                    <?php foreach ($entryTags as $tag): ?>
                        <span class="entry-tag"><?php echo $e($tag); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <p class="entry-meta"><?php echo $e($t('posted_on')); ?> <?php echo $e(Text::displayDate($entry['created_at'] ?? null)); ?></p>
            <div id="answer-output"></div>
            <textarea id="answer-markdown" hidden><?php echo $e($answerMarkdown); ?></textarea>
            <noscript>
                <p><?php echo nl2br($e($answerMarkdown)); ?></p>
            </noscript>
            <div class="article-rating">
                <form method="post" class="rating-component">
                    <input type="hidden" name="action" value="vote">
                    <input type="hidden" name="id" value="<?php echo (int) $entryId; ?>">
                    <input type="hidden" name="lang" value="<?php echo $e($ui->uiLang); ?>">
                    <div class="rating-buttons">
                        <button type="submit" name="vote" value="1" class="button like" aria-label="Like">
                            <div class="rate-icon">
                                <svg viewBox="0 0 24 24" preserveAspectRatio="xMidYMid meet" focusable="false" style="pointer-events: none; display: block; width: 100%; height: 100%;">
                                    <g>
                                        <path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-1.91l-.01-.01L23 10z"></path>
                                    </g>
                                </svg>
                            </div>
                            <div id="rate-count-positive"><?php echo $upvotes; ?></div>
                        </button>

                        <button type="submit" name="vote" value="-1" class="button dislike" aria-label="Dislike">
                            <div class="rate-icon">
                                <svg viewBox="0 0 24 24" preserveAspectRatio="xMidYMid meet" focusable="false" style="pointer-events: none; display: block; width: 100%; height: 100%;">
                                    <g>
                                        <path d="M15 3H6c-.83 0-1.54.5-1.84 1.22l-3.02 7.05c-.09.23-.14.47-.14.73v1.91l.01.01L1 14c0 1.1.9 2 2 2h6.31l-.95 4.57-.03.32c0 .41.17.79.44 1.06L9.83 23l6.59-6.59c.36-.36.58-.86.58-1.41V5c0-1.1-.9-2-2-2zm4 0v12h4V3h-4z"></path>
                                    </g>
                                </svg>
                            </div>
                            <div id="rate-count-negative"><?php echo $downvotes; ?></div>
                        </button>
                    </div>
                    <div class="sentiment">
                        <div id="positive-bar" style="width: <?php echo $e(number_format($positivePercent, 2, '.', '')); ?>%;"></div>
                    </div>
                </form>
            </div>
        </article>
    <?php endif; ?>
</main>

<footer>
    <p><?php echo $e($t('languages')); ?></p>
    <div class="lang-links">
        <?php foreach ($ui->languages as $code => $meta): ?>
            <?php $langParams = ['lang' => $code]; ?>
            <?php if ($entryId > 0): ?>
                <?php $langParams['id'] = $entryId; ?>
            <?php endif; ?>
            <a href="article.php?<?php echo $e(http_build_query($langParams)); ?>"><?php echo $e($meta['label']); ?></a>
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
<script>
    (function () {
        var input = document.getElementById('answer-markdown');
        var output = document.getElementById('answer-output');
        if (!input || !output || typeof marked === 'undefined') {
            return;
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function isSafeUrl(url) {
            if (!url) {
                return true;
            }
            var trimmed = String(url).trim().toLowerCase();
            if (trimmed.startsWith('javascript:') || trimmed.startsWith('data:') || trimmed.startsWith('vbscript:')) {
                return false;
            }
            if (trimmed.startsWith('#') || trimmed.startsWith('/') || trimmed.startsWith('./') || trimmed.startsWith('../')) {
                return true;
            }
            try {
                var parsed = new URL(url, window.location.origin);
                return ['http:', 'https:', 'mailto:', 'tel:'].indexOf(parsed.protocol) !== -1;
            } catch (err) {
                return false;
            }
        }

        var renderer = new marked.Renderer();
        renderer.link = function (href, title, text) {
            if (!isSafeUrl(href)) {
                return escapeHtml(text);
            }
            var safeHref = escapeHtml(href);
            var safeTitle = title ? ' title="' + escapeHtml(title) + '"' : '';
            return '<a href="' + safeHref + '"' + safeTitle + '>' + text + '</a>';
        };
        renderer.image = function (href, title, text) {
            return escapeHtml(text || '');
        };
        renderer.html = function () {
            return '';
        };

        marked.setOptions({
            renderer: renderer,
            gfm: true,
            breaks: true,
            mangle: false,
            headerIds: false
        });

        output.innerHTML = marked.parse(input.value || '');
    })();
</script>
</body>
</html>
