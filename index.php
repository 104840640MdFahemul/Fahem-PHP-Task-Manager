<?php
session_start();

if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');

        if ($title !== '') {
            $_SESSION['tasks'][] = [
                'id' => uniqid(),
                'title' => $title,
                'completed' => false
            ];
        }
    }

    if ($action === 'toggle') {
        $id = $_POST['id'] ?? '';

        foreach ($_SESSION['tasks'] as &$task) {
            if ($task['id'] === $id) {
                $task['completed'] = !$task['completed'];
                break;
            }
        }
        unset($task);
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';

        $_SESSION['tasks'] = array_values(array_filter(
            $_SESSION['tasks'],
            fn($task) => $task['id'] !== $id
        ));
    }

    header('Location: index.php');
    exit;
}

$totalTasks = count($_SESSION['tasks']);

$completedTasks = count(array_filter(
    $_SESSION['tasks'],
    fn($task) => $task['completed']
));

$pendingTasks = $totalTasks - $completedTasks;

$environment = getenv('APP_ENVIRONMENT') ?: 'Local / Default Environment';
$phpVersion = PHP_VERSION;
$serverTime = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Student Task Manager</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f5f7fa;
            color: #222;
        }

        header {
            background: #ffffff;
            padding: 18px 40px;
            border-bottom: 1px solid #ddd;
        }

        header h2 {
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .hero {
            text-align: center;
            margin-bottom: 30px;
        }

        .hero h1 {
            font-size: 42px;
            margin-bottom: 10px;
        }

        .hero p {
            font-size: 18px;
            color: #555;
        }

        .environment {
            margin-top: 10px;
            font-weight: bold;
            color: #198754;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: #ffffff;
            padding: 25px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .card h3 {
            margin-top: 0;
        }

        .card .number {
            font-size: 32px;
            font-weight: bold;
        }

        .panel {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .task-form {
            display: flex;
            gap: 10px;
        }

        .task-form input {
            flex: 1;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-add {
            background: #0d6efd;
            color: white;
        }

        .btn-toggle {
            background: #198754;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f1f3f5;
        }

        .completed {
            text-decoration: line-through;
            color: #777;
        }

        .status-complete {
            color: #198754;
            font-weight: bold;
        }

        .status-pending {
            color: #d39e00;
            font-weight: bold;
        }

        .system-info {
            margin-top: 30px;
            font-size: 14px;
            color: #555;
        }

        @media (max-width: 768px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .task-form {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<header>
    <h2>Fahem PHP Deployment</h2>
</header>

<div class="container">

    <div class="hero">
        <h1>PHP Student Task Manager</h1>
        <p>A separate PHP 8.x web application deployed to Microsoft Azure App Service.</p>

        <div class="environment">
            Environment: <?= htmlspecialchars($environment) ?>
        </div>
    </div>

    <div class="stats">

        <div class="card">
            <h3>Total Tasks</h3>
            <div class="number"><?= $totalTasks ?></div>
        </div>

        <div class="card">
            <h3>Completed</h3>
            <div class="number"><?= $completedTasks ?></div>
        </div>

        <div class="card">
            <h3>Pending</h3>
            <div class="number"><?= $pendingTasks ?></div>
        </div>

    </div>

    <div class="panel">
        <h2>Add New Task</h2>

        <form method="post" class="task-form">
            <input type="hidden" name="action" value="add">

            <input
                type="text"
                name="title"
                placeholder="Enter a PHP task..."
                required
            >

            <button type="submit" class="btn-add">
                Add Task
            </button>
        </form>
    </div>

    <div class="panel">
        <h2>Task List</h2>

        <?php if (empty($_SESSION['tasks'])): ?>

            <p>No tasks added yet.</p>

        <?php else: ?>

            <table>
                <thead>
                <tr>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>

                <tbody>

                <?php foreach ($_SESSION['tasks'] as $task): ?>

                    <tr>

                        <td class="<?= $task['completed'] ? 'completed' : '' ?>">
                            <?= htmlspecialchars($task['title']) ?>
                        </td>

                        <td>
                            <?php if ($task['completed']): ?>
                                <span class="status-complete">Completed</span>
                            <?php else: ?>
                                <span class="status-pending">Pending</span>
                            <?php endif; ?>
                        </td>

                        <td>

                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $task['id'] ?>">

                                <button type="submit" class="btn-toggle">
                                    <?= $task['completed'] ? 'Mark Pending' : 'Complete' ?>
                                </button>
                            </form>

                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $task['id'] ?>">

                                <button type="submit" class="btn-delete">
                                    Delete
                                </button>
                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>

        <?php endif; ?>

    </div>

    <div class="system-info">
        <strong>Runtime:</strong> PHP <?= htmlspecialchars($phpVersion) ?><br>
        <strong>Server time:</strong> <?= htmlspecialchars($serverTime) ?>
    </div>

</div>

</body>
</html>