<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Admin Console</title>
    <style>
        header h1 {
            text-align: center;
        }
        nav table {
            margin: 0 auto;
        }
        nav td {
            white-space: nowrap;
        }
        table.admin-table {
            width: 100%;
        }
        .admin-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 10px;
        }
        .admin-page-info {
            white-space: nowrap;
        }
        .admin-page-disabled {
            color: #777;
        }
        .tag-picker {
            margin: 8px 0 0;
        }
        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 6px;
        }
        .tag-chip {
            border: 1px solid #bbb;
            border-radius: 999px;
            background: #f6f6f6;
            color: #333;
            padding: 2px 10px;
            cursor: pointer;
            font-size: 12px;
        }
        .tag-chip.is-selected {
            background: #1f1f1f;
            border-color: #1f1f1f;
            color: #fff;
        }
    </style>
</head>
<body>
<header>
    <h1>Admin Console</h1>
</header>

<?php if ($notice !== ''): ?>
    <p><?php echo $e($notice); ?></p>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <p><?php echo $e($error); ?></p>
<?php endif; ?>

<?php if (!$isAdmin): ?>
    <?php require __DIR__ . '/login.php'; ?>
<?php else: ?>
    <?php require __DIR__ . '/nav.php'; ?>

    <?php if ($view === 'entries'): ?>
        <?php require __DIR__ . '/entries.php'; ?>
    <?php elseif ($view === 'add'): ?>
        <?php require __DIR__ . '/add.php'; ?>
    <?php elseif ($view === 'edit'): ?>
        <?php require __DIR__ . '/edit.php'; ?>
    <?php elseif ($view === 'translate'): ?>
        <?php require __DIR__ . '/translate.php'; ?>
    <?php elseif ($view === 'languages'): ?>
        <?php require __DIR__ . '/languages.php'; ?>
    <?php elseif ($view === 'ui'): ?>
        <?php require __DIR__ . '/ui.php'; ?>
    <?php elseif ($view === 'palette'): ?>
        <?php require __DIR__ . '/palette.php'; ?>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
