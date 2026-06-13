<?php
/**
 * ELITE-4 Nepal - Student Dashboard
 */
require_once 'config.php';
requireRole('student');

$user = getCurrentUser();
global $conn;
$sub = getUserSubscription($user['id']);
$remainingMessages = getRemainingMentorMessages($user['id']);

// Get stats
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM solutions WHERE user_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$r = $stmt->get_result();
$mySolutions = $r ? ($r->fetch_assoc()['cnt']) : null;

$stmt = $conn->prepare("SELECT SUM(reward_net) as total FROM solutions WHERE user_id = ? AND status = 'rewarded'");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$r = $stmt->get_result();
$totalEarnings = $r ? ($r->fetch_assoc()['total'] ?? 0) : null;

// Get my team
$myTeam = getUserTeam($user['id']);

// Get open problems
$stmt = $conn->prepare("SELECT p.*, u.name as user_name FROM problems p JOIN users u ON p.user_id = u.id WHERE p.status = 'open' ORDER BY p.created_at DESC LIMIT 8");
$stmt->execute();
$r = $stmt->get_result();
$openProblems = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

// Get open challenges
$stmt = $conn->prepare("SELECT c.*, u.name as sponsor_name FROM challenges c JOIN users u ON c.sponsor_id = u.id WHERE c.status = 'open' ORDER BY c.created_at DESC LIMIT 5");
$stmt->execute();
$r = $stmt->get_result();
$challenges = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

// Get my solutions
$stmt = $conn->prepare("SELECT s.*, p.title as problem_title, c.title as challenge_title FROM solutions s LEFT JOIN problems p ON s.problem_id = p.id LEFT JOIN challenges c ON s.challenge_id = c.id WHERE s.user_id = ? ORDER BY s.created_at DESC LIMIT 5");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$r = $stmt->get_result();
$mySolutionsList = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

// Get pending join requests
$stmt = $conn->prepare("SELECT jr.*, t.name as team_name FROM join_requests jr JOIN teams t ON jr.team_id = t.id WHERE jr.user_id = ? AND jr.status = 'pending'");
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
    <title>Student Dashboard - ELITE-4 Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>tailwind.config = { theme: { extend: { colors: { elite: { 600: '#2563eb' } } } } }</script>
    <style>body { font-family: 'Inter', sans-serif; } .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }</style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
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
                    <a href="student_dashboard.php" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg font-medium">Dashboard</a>
                    <a href="team_public_listing.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Browse Teams</a>
                    <a href="team_formation.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">My Teams</a>
                    <a href="micro_gigs.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Gigs</a>
                    <a href="mentor_chat.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                        Mentor Chat <?= $remainingMessages < 3 && !$sub['is_plus'] ? '<span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">'.$remainingMessages.'</span>' : '' ?>
                    </a>
                    <a href="profile.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Profile</a>
                    <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Logout</a>
                </div>
                
                <button id="mobileMenuBtn" class="md:hidden text-gray-600 text-2xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div id="mobileMenu" class="hidden md:hidden pb-4 space-y-2">
                <a href="student_dashboard.php" class="block py-2 px-4 bg-blue-50 text-blue-700 rounded-lg">Dashboard</a>
                <a href="team_public_listing.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg">Browse Teams</a>
                <a href="team_formation.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg">My Teams</a>
                <a href="micro_gigs.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg">Gigs</a>
                <a href="mentor_chat.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg">Mentor Chat</a>
                <a href="profile.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg">Profile</a>
                <a href="logout.php" class="block py-2 px-4 bg-red-500 text-white rounded-lg">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-700 rounded-2xl p-8 text-white mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        <i class="fas fa-graduation-cap mr-3"></i>Welcome, <?= e($user['name']) ?>!
                    </h1>
                    <p class="text-green-100">Ready to solve problems and earn rewards? Let's go!</p>
                    <div class="flex items-center gap-4 mt-3">
                        <span class="px-3 py-1 rounded-full text-sm bg-white/20">
                            <i class="fas fa-crown mr-1"></i><?= ucfirst($sub['plan']) ?> Plan
                        </span>
                        <?php if ($sub['is_plus']): ?>
                        <span class="px-3 py-1 rounded-full text-sm bg-amber-500 text-white">
                            <i class="fas fa-infinity mr-1"></i>Unlimited Mentor Messages
                        </span>
                        <?php else: ?>
                        <span class="px-3 py-1 rounded-full text-sm bg-red-500 text-white">
                            <i class="fas fa-comment mr-1"></i><?= $remainingMessages ?>/3 Messages Left
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-4 md:mt-0 flex flex-col gap-2">
                    <?php if (!$myTeam): ?>
                    <a href="team_formation.php" class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-6 rounded-full transition-all">
                        <i class="fas fa-users mr-2"></i>Form a Team
                    </a>
                    <?php else: ?>
                    <a href="team_progress.php?team_id=<?= $myTeam['id'] ?>" class="inline-block bg-white/20 hover:bg-white/30 text-white font-bold py-3 px-6 rounded-full transition-all">
                        <i class="fas fa-chart-line mr-2"></i>Team Progress
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pending Join Requests -->
        <?php if (!empty($pendingRequests)): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-8">
            <h3 class="font-bold text-amber-800 mb-3"><i class="fas fa-clock mr-2"></i>Pending Team Join Requests</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <?php foreach ($pendingRequests as $req): ?>
                <div class="bg-white rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold"><?= e($req['team_name']) ?></p>
                        <p class="text-sm text-gray-500">Waiting for approval</p>
                    </div>
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-sm">Pending</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">My Solutions</p>
                        <p class="text-3xl font-bold text-blue-600"><?= $mySolutions ?></p>
                    </div>
                    <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-alt text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Earnings</p>
                        <p class="text-3xl font-bold text-green-600"><?= formatCurrency($totalEarnings) ?></p>
                    </div>
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-coins text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">My Team</p>
                        <p class="text-2xl font-bold text-purple-600"><?= $myTeam ? e(substr($myTeam['name'], 0, 12)) : 'None' ?></p>
                    </div>
                    <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Open Problems</p>
                        <p class="text-3xl font-bold text-amber-600"><?= count($openProblems) ?></p>
                    </div>
                    <div class="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-2xl text-amber-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Open Problems -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold"><i class="fas fa-exclamation-circle mr-2 text-orange-500"></i>Open Problems</h2>
                    <a href="team_public_listing.php" class="text-blue-600 hover:underline text-sm">View All</a>
                </div>

                <?php if (empty($openProblems)): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-check-circle text-5xl mb-4 text-green-300"></i>
                    <p>No open problems at the moment.</p>
                </div>
                <?php else: ?>
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <?php foreach ($openProblems as $problem): ?>
                    <div class="border border-gray-200 rounded-xl p-4 hover:border-blue-400 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?= getCategoryClass($problem['category']) ?>"><?= e($problem['category']) ?></span>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?= getUrgencyClass($problem['urgency']) ?>"><?= e($problem['urgency']) ?></span>
                                </div>
                                <h3 class="font-semibold text-gray-800"><?= e($problem['title']) ?></h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-user mr-1"></i><?= e($problem['user_name']) ?>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock mr-1"></i><?= timeAgo($problem['created_at']) ?>
                                </p>
                            </div>
                            <a href="submit_solution.php?problem_id=<?= $problem['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-all ml-4">
                                Solve
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Active Challenges -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold"><i class="fas fa-trophy mr-2 text-amber-500"></i>Active Challenges</h2>
                </div>

                <?php if (empty($challenges)): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-trophy text-5xl mb-4 text-gray-300"></i>
                    <p>No active challenges.</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($challenges as $challenge): ?>
                    <div class="border border-amber-200 bg-amber-50 rounded-xl p-4">
                        <h3 class="font-bold text-gray-800"><?= e($challenge['title']) ?></h3>
                        <p class="text-sm text-gray-600 mt-1"><?= e(substr($challenge['description'], 0, 80)) ?>...</p>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xl font-bold text-amber-600"><?= formatCurrency($challenge['reward_amount']) ?></span>
                            <span class="text-xs text-gray-500">By: <?= e($challenge['sponsor_name']) ?></span>
                        </div>
                        <a href="submit_solution.php?challenge_id=<?= $challenge['id'] ?>" class="mt-3 block text-center bg-amber-500 hover:bg-amber-600 text-white py-2 rounded-lg text-sm transition-all">
                            Submit Solution
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Solutions -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mt-8">
            <h2 class="text-xl font-bold mb-6"><i class="fas fa-list-check mr-2 text-blue-600"></i>My Solutions</h2>

            <?php if (empty($mySolutionsList)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-file-alt text-4xl mb-4 text-gray-300"></i>
                <p>You haven't submitted any solutions yet.</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Budget</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($mySolutionsList as $sol): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium"><?= e($sol['title']) ?></td>
                            <td class="px-4 py-3 text-sm"><?= $sol['problem_title'] ? 'Problem' : 'Challenge' ?></td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?= getSolutionStatusClass($sol['status']) ?>">
                                    <?= ucfirst(e($sol['status'])) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm"><?= formatCurrency($sol['budget_estimate']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-500"><?= timeAgo($sol['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
            <a href="team_public_listing.php" class="bg-blue-50 p-4 rounded-xl text-center hover:bg-blue-100 transition-all card-hover">
                <i class="fas fa-users text-2xl text-blue-600 mb-2"></i>
                <p class="text-sm font-semibold text-blue-800">Browse Teams</p>
            </a>
            <a href="team_formation.php" class="bg-green-50 p-4 rounded-xl text-center hover:bg-green-100 transition-all card-hover">
                <i class="fas fa-plus-circle text-2xl text-green-600 mb-2"></i>
                <p class="text-sm font-semibold text-green-800"><?= $myTeam ? 'My Team' : 'Form Team' ?></p>
            </a>
            <a href="micro_gigs.php" class="bg-amber-50 p-4 rounded-xl text-center hover:bg-amber-100 transition-all card-hover">
                <i class="fas fa-briefcase text-2xl text-amber-600 mb-2"></i>
                <p class="text-sm font-semibold text-amber-800">Browse Gigs</p>
            </a>
            <a href="subscription.php" class="bg-purple-50 p-4 rounded-xl text-center hover:bg-purple-100 transition-all card-hover">
                <i class="fas fa-crown text-2xl text-purple-600 mb-2"></i>
                <p class="text-sm font-semibold text-purple-800">Upgrade Plan</p>
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

<?php
function getCategoryClass($cat) {
    $classes = ['Waste' => 'bg-green-100 text-green-800', 'Road' => 'bg-blue-100 text-blue-800', 'Health' => 'bg-red-100 text-red-800', 'Water' => 'bg-cyan-100 text-cyan-800', 'Other' => 'bg-gray-100 text-gray-800'];
    return $classes[$cat] ?? 'bg-gray-100 text-gray-800';
}
function getUrgencyClass($urg) {
    $classes = ['High' => 'bg-red-500 text-white', 'Medium' => 'bg-amber-500 text-white', 'Low' => 'bg-green-100 text-green-800'];
    return $classes[$urg] ?? 'bg-gray-100 text-gray-800';
}
function getSolutionStatusClass($status) {
    $classes = ['pending' => 'bg-amber-100 text-amber-800', 'approved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800', 'rewarded' => 'bg-purple-100 text-purple-800'];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}
?>

</body>
</html>