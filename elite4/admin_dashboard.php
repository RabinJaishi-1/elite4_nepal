<?php
/**
 * ELITE-4 Nepal - Admin Dashboard with Full Moderation
 */
require_once 'config.php';
requireRole(['admin', 'moderator']);

$user = getCurrentUser();
global $conn;
$isAdmin = $user['role'] === 'admin';

// Stats
$stats = getStats();

$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM teams");
$stmt->execute();
$r = $stmt->get_result();
$totalTeams = $r ? ($r->fetch_assoc()['cnt']) : null;

$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE is_active = 1");
$stmt->execute();
$r = $stmt->get_result();
$totalUsers = $r ? ($r->fetch_assoc()['cnt']) : null;

// Get recent unread admin messages
$stmt = $conn->prepare("SELECT am.*, u.name as sender_name, u.role as sender_role FROM admin_messages am JOIN users u ON am.sender_id = u.id WHERE am.is_read = 0 ORDER BY am.created_at DESC LIMIT 5");
$stmt->execute();
$r = $stmt->get_result();
$unreadMessages = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

// Commission
$commission = getCommissionPercent();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ELITE-4 Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>tailwind.config = { theme: { extend: { colors: { elite: { 600: '#2563eb' } } } } }</script>
    <style>body { font-family: 'Inter', sans-serif; } .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }</style>
</head>
<body class="bg-gray-50">

    <nav class="bg-gray-900 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white"></i>
                    </div>
                    <span class="font-bold text-xl text-white">ELITE-4 <span class="text-blue-400">Admin</span></span>
                </div>
                
                <div class="hidden md:flex items-center space-x-4">
                    <a href="admin_dashboard.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium">Dashboard</a>
                    <?php if ($isAdmin): ?>
                    <a href="admin_chat_moderation.php" class="px-4 py-2 text-gray-300 hover:bg-gray-800 rounded-lg">Chat Moderation</a>
                    <a href="admin_teams.php" class="px-4 py-2 text-gray-300 hover:bg-gray-800 rounded-lg">Teams</a>
                    <a href="admin_commission.php" class="px-4 py-2 text-gray-300 hover:bg-gray-800 rounded-lg">Settings</a>
                    <?php endif; ?>
                    <a href="profile.php" class="px-4 py-2 text-gray-300 hover:bg-gray-800 rounded-lg">Profile</a>
                    <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Logout</a>
                </div>
                
                <button id="mobileMenuBtn" class="md:hidden text-white text-2xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div id="mobileMenu" class="hidden md:hidden pb-4 space-y-2">
                <a href="admin_dashboard.php" class="block py-2 px-4 bg-blue-600 text-white rounded-lg">Dashboard</a>
                <?php if ($isAdmin): ?>
                <a href="admin_chat_moderation.php" class="block py-2 px-4 text-gray-300 hover:bg-gray-800 rounded-lg">Chat Moderation</a>
                <a href="admin_teams.php" class="block py-2 px-4 text-gray-300 hover:bg-gray-800 rounded-lg">Teams</a>
                <a href="admin_commission.php" class="block py-2 px-4 text-gray-300 hover:bg-gray-800 rounded-lg">Settings</a>
                <?php endif; ?>
                <a href="profile.php" class="block py-2 px-4 text-gray-300 hover:bg-gray-800 rounded-lg">Profile</a>
                <a href="logout.php" class="block py-2 px-4 bg-red-500 text-white rounded-lg">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-2xl p-8 text-white mb-8">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-alt text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Admin Dashboard</h1>
                    <p class="text-gray-400">Welcome, <?= e($user['name']) ?> • Full moderation access</p>
                </div>
            </div>
        </div>

        <!-- Platform Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Problems</p>
                        <p class="text-3xl font-bold text-blue-600"><?= $stats['problems'] ?></p>
                    </div>
                    <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Solutions</p>
                        <p class="text-3xl font-bold text-green-600"><?= $stats['solutions'] ?></p>
                    </div>
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Active Teams</p>
                        <p class="text-3xl font-bold text-purple-600"><?= $totalTeams ?></p>
                    </div>
                    <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Users</p>
                        <p class="text-3xl font-bold text-amber-600"><?= $totalUsers ?></p>
                    </div>
                    <div class="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-friends text-2xl text-amber-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isAdmin): ?>
        <!-- Admin Actions Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <a href="admin_chat_moderation.php" class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all border-l-4 border-blue-500">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-comments text-2xl text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Chat Moderation</h3>
                        <p class="text-sm text-gray-500">Review team chats & messages</p>
                    </div>
                </div>
            </a>

            <a href="admin_teams.php" class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all border-l-4 border-purple-500">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users-cog text-2xl text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Team Management</h3>
                        <p class="text-sm text-gray-500">Assign mentors & moderators</p>
                    </div>
                </div>
            </a>

            <a href="admin_commission.php" class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all border-l-4 border-amber-500">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-percentage text-2xl text-amber-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Commission: <?= $commission ?>%</h3>
                        <p class="text-sm text-gray-500">Platform settings</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Team Work Progress Section -->
        <?php
        // Get all teams with their progress
        $stmt = $conn->prepare("
            SELECT t.*, u.name as leader_name,
            (SELECT COUNT(*) FROM team_milestones WHERE team_id = t.id) as total_milestones,
            (SELECT COUNT(*) FROM team_milestones WHERE team_id = t.id AND status = 'completed') as completed_milestones,
            (SELECT COUNT(*) FROM team_milestones WHERE team_id = t.id AND status = 'in_progress') as inprogress_milestones
            FROM teams t
            JOIN users u ON t.leader_id = u.id
            WHERE t.status = 'active'
            ORDER BY t.rank_points DESC
            LIMIT 10
        ");
        $stmt->execute();
        $r = $stmt->get_result();
        $allTeams = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
        ?>

        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold"><i class="fas fa-chart-line mr-2 text-purple-600"></i>Team Work Progress</h2>
                <a href="admin_teams.php" class="text-blue-600 hover:underline text-sm">View All Teams</a>
            </div>

            <?php if (empty($allTeams)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                <p>No active teams yet.</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-sm text-gray-500">
                            <th class="px-4 py-3">Team</th>
                            <th class="px-4 py-3">Leader</th>
                            <th class="px-4 py-3">Points</th>
                            <th class="px-4 py-3">Milestones</th>
                            <th class="px-4 py-3">Progress</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($allTeams as $team): ?>
                        <?php 
                        $total = $team['total_milestones'] ?: 1;
                        $completed = $team['completed_milestones'] ?: 0;
                        $progress = round(($completed / $total) * 100);
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">
                                <a href="team_progress.php?team_id=<?= $team['id'] ?>" class="font-semibold text-blue-600 hover:underline">
                                    <?= e($team['name']) ?>
                                </a>
                            </td>
                            <td class="px-4 py-4 text-gray-600"><?= e($team['leader_name']) ?></td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded text-sm font-bold">
                                    <?= $team['rank_points'] ?? 0 ?> pts
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600">
                                <span class="text-green-600"><?= $completed ?></span> / <?= $total ?>
                            </td>
                            <td class="px-4 py-4">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: <?= $progress ?>%"></div>
                                </div>
                                <span class="text-xs text-gray-500"><?= $progress ?>%</span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-1 rounded text-xs font-semibold 
                                    <?= $team['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                       ($team['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') ?>">
                                    <?= ucfirst($team['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Solutions Submitted -->
        <?php
        $stmt = $conn->prepare("
            SELECT s.*, u.name as solver_name, p.title as problem_title, c.title as challenge_title
            FROM solutions s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN problems p ON s.problem_id = p.id
            LEFT JOIN challenges c ON s.challenge_id = c.id
            ORDER BY s.created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        $r = $stmt->get_result();
        $recentSolutions = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
        ?>

        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold"><i class="fas fa-lightbulb mr-2 text-green-600"></i>Recent Solutions</h2>
            </div>

            <?php if (empty($recentSolutions)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                <p>No solutions submitted yet.</p>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($recentSolutions as $sol): ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800"><?= e($sol['title']) ?></h4>
                        <p class="text-sm text-gray-500">
                            By <?= e($sol['solver_name']) ?> 
                            <?php if ($sol['problem_title']): ?> for "<?= e($sol['problem_title']) ?>"<?php endif; ?>
                            <?php if ($sol['challenge_title']): ?> on Challenge "<?= e($sol['challenge_title']) ?>"<?php endif; ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold 
                            <?= $sol['status'] === 'rewarded' ? 'bg-green-100 text-green-800' : 
                               ($sol['status'] === 'approved' ? 'bg-blue-100 text-blue-800' : 
                               ($sol['status'] === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')) ?>">
                            <?= ucfirst($sol['status']) ?>
                        </span>
                        <p class="text-xs text-gray-400 mt-1"><?= timeAgo($sol['created_at']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Recent Admin Messages -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold"><i class="fas fa-envelope mr-2 text-blue-600"></i>Recent Sponsor Messages</h2>
                <?php if (!empty($unreadMessages)): ?>
                <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm"><?= count($unreadMessages) ?> new</span>
                <?php endif; ?>
            </div>

            <?php if (empty($unreadMessages)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                <p>No unread messages.</p>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($unreadMessages as $msg): ?>
                <div class="border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition-all">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold"><?= e($msg['sender_name']) ?></span>
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs"><?= ucfirst(e($msg['sender_role'])) ?></span>
                            </div>
                            <h4 class="font-medium text-gray-800"><?= e($msg['subject']) ?></h4>
                            <p class="text-sm text-gray-600 mt-1"><?= e(substr($msg['message'], 0, 100)) ?>...</p>
                        </div>
                        <span class="text-xs text-gray-400"><?= timeAgo($msg['created_at']) ?></span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <form method="POST" action="api_sponsor_message.php" class="inline">
                            <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                            <input type="hidden" name="action" value="read">
                            <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                <i class="fas fa-check mr-1"></i>Mark as Read
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="index.php" class="bg-blue-50 p-4 rounded-xl text-center hover:bg-blue-100 transition-all card-hover">
                <i class="fas fa-globe text-2xl text-blue-600 mb-2"></i>
                <p class="text-sm font-semibold text-blue-800">View Site</p>
            </a>
            <a href="team_leaderboard.php" class="bg-green-50 p-4 rounded-xl text-center hover:bg-green-100 transition-all card-hover">
                <i class="fas fa-trophy text-2xl text-green-600 mb-2"></i>
                <p class="text-sm font-semibold text-green-800">Leaderboard</p>
            </a>
            <a href="admin_teams.php" class="bg-purple-50 p-4 rounded-xl text-center hover:bg-purple-100 transition-all card-hover">
                <i class="fas fa-users text-2xl text-purple-600 mb-2"></i>
                <p class="text-sm font-semibold text-purple-800">All Teams</p>
            </a>
            <a href="logout.php" class="bg-red-50 p-4 rounded-xl text-center hover:bg-red-100 transition-all card-hover">
                <i class="fas fa-sign-out-alt text-2xl text-red-600 mb-2"></i>
                <p class="text-sm font-semibold text-red-800">Logout</p>
            </a>
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