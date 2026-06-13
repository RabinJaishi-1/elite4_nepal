<?php
/**
 * ELITE-4 Nepal - Mentor Dashboard
 */
require_once 'config.php';
requireRole('mentor');

$user = getCurrentUser();
global $conn;

// Get assigned teams
$stmt = $conn->prepare("SELECT t.*, ma.id as assignment_id FROM teams t JOIN mentor_assignments ma ON t.id = ma.team_id WHERE ma.mentor_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$r = $stmt->get_result();
$assignedTeams = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

// Get pending solutions
$teamIds = array_column($assignedTeams, 'id');
$pendingSolutions = [];
if (!empty($teamIds)) {
    $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
    $stmt = $conn->prepare("SELECT s.*, u.name as solver_name, t.name as team_name FROM solutions s JOIN users u ON s.user_id = u.id LEFT JOIN teams t ON s.team_id = t.id WHERE s.team_id IN ($placeholders) AND s.status = 'pending' ORDER BY s.created_at DESC LIMIT 10");
    $stmt->bind_param(str_repeat('i', count($teamIds)), ...$teamIds);
    $stmt->execute();
    $r = $stmt->get_result();
    $pendingSolutions = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// Stats
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM mentor_assignments WHERE mentor_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$r = $stmt->get_result();
$teamsAssigned = $r ? ($r->fetch_assoc()['cnt']) : null;

$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM solutions s JOIN mentor_assignments ma ON s.team_id = ma.team_id WHERE ma.mentor_id = ? AND s.status = 'approved'");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$r = $stmt->get_result();
$solutionsReviewed = $r ? ($r->fetch_assoc()['cnt']) : null;

// Handle review actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $solutionId = (int)$_POST['solution_id'];
    
    if ($_POST['action'] === 'approve') {
        $stmt = $conn->prepare("UPDATE solutions SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $solutionId);
        $stmt->execute();
        setFlash('success', 'Solution approved!');
    } elseif ($_POST['action'] === 'reject') {
        $feedback = trim($_POST['feedback'] ?? '');
        $stmt = $conn->prepare("UPDATE solutions SET status = 'rejected', mentor_feedback = ? WHERE id = ?");
        $stmt->bind_param("si", $feedback, $solutionId);
        $stmt->execute();
        setFlash('success', 'Solution rejected.');
    }
    
    header("Location: mentor_dashboard.php");
    exit;
}

// Get pending join requests for review
$stmt = $conn->prepare("SELECT jr.*, t.name as team_name, u.name as student_name FROM join_requests jr JOIN teams t ON jr.team_id = t.id JOIN users u ON jr.user_id = u.id WHERE t.mentor_id = ? AND jr.status = 'pending'");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$r = $stmt->get_result();
$pendingRequests = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Dashboard - ELITE-4 Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>tailwind.config = { theme: { extend: { colors: { elite: { 600: '#2563eb' } } } } }</script>
    <style>body { font-family: 'Inter', sans-serif; } .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }</style>
</head>
<body class="bg-gray-50">

    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <a href="index.php" class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center">
                            <i class="fas fa-lightbulb text-white"></i>
                        </div>
                        <span class="font-bold text-xl text-gray-800">ELITE-4 <span class="text-blue-600">Nepal</span></span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-4">
                    <a href="mentor_dashboard.php" class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg font-medium">Dashboard</a>
                    <a href="mentor_messages.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg"><i class="fas fa-envelope mr-1"></i>Inbox</a>
                    <a href="team_leaderboard.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Leaderboard</a>
                    <a href="profile.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Profile</a>
                    <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Logout</a>
                </div>
                
                <button id="mobileMenuBtn" class="md:hidden text-gray-600 text-2xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div id="mobileMenu" class="hidden md:hidden pb-4 space-y-2">
                <a href="mentor_dashboard.php" class="block py-2 px-4 bg-purple-50 text-purple-700 rounded-lg">Dashboard</a>
                <a href="mentor_messages.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg"><i class="fas fa-envelope mr-1"></i>Inbox</a>
                <a href="team_leaderboard.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg">Leaderboard</a>
                <a href="profile.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg">Profile</a>
                <a href="logout.php" class="block py-2 px-4 bg-red-500 text-white rounded-lg">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-purple-600 to-violet-700 rounded-2xl p-8 text-white mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        <i class="fas fa-chalkboard-teacher mr-3"></i>Welcome, <?= e($user['name']) ?>!
                    </h1>
                    <p class="text-purple-100">Guide teams to success and help solve real-world problems.</p>
                </div>
                <a href="team_leaderboard.php" class="mt-4 md:mt-0 bg-white/20 hover:bg-white/30 text-white font-bold py-3 px-6 rounded-full transition-all border-2 border-white/50">
                    <i class="fas fa-chart-line mr-2"></i>View Leaderboard
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Teams Assigned</p>
                        <p class="text-3xl font-bold text-purple-600"><?= $teamsAssigned ?></p>
                    </div>
                    <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Solutions Reviewed</p>
                        <p class="text-3xl font-bold text-green-600"><?= $solutionsReviewed ?></p>
                    </div>
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-double text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pending Reviews</p>
                        <p class="text-3xl font-bold text-amber-600"><?= count($pendingSolutions) ?></p>
                    </div>
                    <div class="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-2xl text-amber-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Join Requests</p>
                        <p class="text-3xl font-bold text-blue-600"><?= count($pendingRequests) ?></p>
                    </div>
                    <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-plus text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Assigned Teams -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold mb-6"><i class="fas fa-users mr-2 text-purple-600"></i>My Teams</h2>

                <?php if (empty($assignedTeams)): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-user-friends text-5xl mb-4 text-gray-300"></i>
                    <p>No teams assigned to you yet.</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($assignedTeams as $team): ?>
                    <div class="border border-gray-200 rounded-xl p-4 hover:border-purple-400 transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-gray-800"><?= e($team['name']) ?></h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    <i class="fas fa-crown mr-1"></i>Leader ID: <?= $team['leader_id'] ?>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-star mr-1"></i><?= $team['rank_points'] ?> pts
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <a href="team_progress.php?team_id=<?= $team['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm">
                                    <i class="fas fa-chart-line"></i>
                                </a>
                                <a href="chat.php?team_id=<?= $team['id'] ?>" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded-lg text-sm">
                                    <i class="fas fa-comments"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pending Join Requests -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold mb-6"><i class="fas fa-user-plus mr-2 text-blue-500"></i>Pending Join Requests</h2>

                <?php if (empty($pendingRequests)): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-check-circle text-5xl mb-4 text-green-300"></i>
                    <p>No pending requests.</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pendingRequests as $req): ?>
                    <div class="border border-blue-200 bg-blue-50 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold"><?= e($req['student_name']) ?></span>
                            <span class="text-sm text-gray-500"><?= timeAgo($req['created_at']) ?></span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Wants to join: <strong><?= e($req['team_name']) ?></strong></p>
                        <p class="text-sm bg-white rounded p-2 mb-3">"<?= e(substr($req['proposal'], 0, 100)) ?>..."</p>
                        <div class="flex gap-2">
                            <form method="POST" action="api_approve_join.php" class="flex-1">
                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg text-sm font-semibold">
                                    <i class="fas fa-check mr-1"></i>Approve
                                </button>
                            </form>
                            <form method="POST" action="api_approve_join.php" class="flex-1">
                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg text-sm font-semibold">
                                    <i class="fas fa-times mr-1"></i>Reject
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Solutions to Review -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mt-8">
            <h2 class="text-xl font-bold mb-6"><i class="fas fa-clipboard-check mr-2 text-amber-500"></i>Pending Solution Reviews</h2>

            <?php if (empty($pendingSolutions)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-check-circle text-5xl mb-4 text-green-300"></i>
                <p>No pending solutions to review.</p>
            </div>
            <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($pendingSolutions as $sol): ?>
                <div class="border border-amber-200 bg-amber-50 rounded-xl p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg"><?= e($sol['title']) ?></h3>
                            <p class="text-sm text-gray-500">
                                By <?= e($sol['solver_name']) ?> from <?= e($sol['team_name'] ?? 'Individual') ?>
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending Review</span>
                    </div>
                    
                    <p class="text-gray-700 mb-4"><?= nl2br(e(substr($sol['description'], 0, 300))) ?>...</p>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-amber-200">
                        <span class="text-lg font-bold text-green-600">Budget: <?= formatCurrency($sol['budget_estimate']) ?></span>
                        <div class="flex gap-3">
                            <form method="POST" class="inline">
                                <input type="hidden" name="solution_id" value="<?= $sol['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <input type="text" name="feedback" placeholder="Feedback (optional)" class="px-3 py-2 border rounded-lg text-sm mr-2">
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                                    <i class="fas fa-times mr-1"></i>Reject
                                </button>
                            </form>
                            <form method="POST" class="inline">
                                <input type="hidden" name="solution_id" value="<?= $sol['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                                    <i class="fas fa-check mr-1"></i>Approve
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
    <div class="fixed bottom-6 right-6 z-50 p-4 rounded-xl shadow-2xl <?= $flash['type'] === 'success' ? 'bg-green-600' : 'bg-red-600' ?> text-white">
        <div class="flex items-center gap-3">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <span><?= e($flash['message']) ?></span>
        </div>
    </div>
    <script>setTimeout(() => { document.querySelector('.fixed.bottom-6').remove(); }, 4000);</script>
    <?php endif; ?>

    <script>
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });
    </script>

</body>
</html>