<?php

declare(strict_types=1);

/**
 * Placeholder dashboard view showing mock metrics and activity with a left
 * navigation side panel layout.
 */

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$teamMembers = [
    ['name' => 'Alice Johnson', 'role' => 'Product Manager'],
    ['name' => 'Marcus Reed', 'role' => 'Lead Engineer'],
    ['name' => 'Priya Singh', 'role' => 'UX Designer'],
    ['name' => 'Leo Torres', 'role' => 'QA Analyst'],
];

$recentActivity = [
    ['time' => '09:30', 'text' => 'New magic link request approved.'],
    ['time' => '10:05', 'text' => 'User onboarding checklist completed.'],
    ['time' => '11:15', 'text' => 'Security scan passed on latest build.'],
    ['time' => '12:40', 'text' => 'Marketing assets synced to CDN.'],
];

$metrics = [
    ['label' => 'Active Users', 'value' => '1,248', 'trend' => '+12.5%'],
    ['label' => 'Magic Links Sent', 'value' => '362', 'trend' => '+8.1%'],
    ['label' => 'Conversion Rate', 'value' => '27%', 'trend' => '+3.6%'],
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container-fluid">
    <div class="row min-vh-100">
        <aside class="col-12 col-md-3 col-lg-2 bg-dark text-white py-4 px-3">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary rounded-circle me-2" style="width: 40px; height: 40px;"></div>
                <span class="fw-semibold">Acme Portal</span>
            </div>

            <nav class="nav flex-column gap-2">
                <a class="nav-link text-white-50" href="/dashboard">Overview</a>
                <a class="nav-link text-white-50" href="#">Reports</a>
                <a class="nav-link text-white-50" href="#">Users</a>
                <a class="nav-link text-white-50" href="#">Settings</a>
                <hr class="border-secondary">
                <a class="nav-link text-white" href="/logout">Sign out</a>
            </nav>
        </aside>

        <main class="col-12 col-md-9 col-lg-10 py-4 px-4 px-lg-5">
            <header class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-1">Welcome back!</h1>
                    <p class="text-muted mb-0">Here is whats happening with your product today.</p>
                </div>

                <button class="btn btn-primary">New Action</button>
            </header>

            <section class="row g-4 mb-4">
                <?php foreach ($metrics as $metric): ?>
                    <div class="col-12 col-sm-6 col-xl-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <p class="text-uppercase text-muted small mb-1"><?= htmlspecialchars($metric['label']) ?></p>
                                <h2 class="h3 mb-0"><?= htmlspecialchars($metric['value']) ?></h2>
                                <span class="text-success small"><?= htmlspecialchars($metric['trend']) ?> vs last week</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <h2 class="h5 mb-0">Recent Activity</h2>
                            <a href="#" class="small">View all</a>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentActivity as $activity): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($activity['text']) ?></span>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($activity['time']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0">
                            <h2 class="h5 mb-0">Team Members</h2>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($teamMembers as $member): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($member['name']) ?></strong>
                                    <div class="text-muted small"><?= htmlspecialchars($member['role']) ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

