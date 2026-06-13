<?php
/**
 * ELITE-4 Nepal - Citizen Dashboard
 */
require_once 'config.php';
requireRole('citizen');

$user = getCurrentUser();
global $conn;

// Get citizen's problems
$stmt = $conn->prepare("SELECT * FROM problems WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$r = $stmt->get_result();
$problems = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

// Stats
$totalProblems = count($problems);
$solvedProblems = count(array_filter($problems, fn($p) => $p['status'] === 'solved'));

// Get solutions to their problems
$problemIds = array_column($problems, 'id');
$solutions = [];
if (!empty($problemIds)) {
    $placeholders = implode(',', array_fill(0, count($problemIds), '?'));
    $stmt = $conn->prepare("SELECT s.*, p.title as problem_title FROM solutions s JOIN problems p ON s.problem_id = p.id WHERE s.problem_id IN ($placeholders) ORDER BY s.created_at DESC LIMIT 5");
    $stmt->bind_param(str_repeat('i', count($problemIds)), ...$problemIds);
    $stmt->execute();
    $r = $stmt->get_result();
    $solutions = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// Get their gigs
$stmt = $conn->prepare("SELECT * FROM micro_gigs WHERE citizen_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$r = $stmt->get_result();
$gigs = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Dashboard - ELITE-4 Nepal</title>
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
                    <a href="citizen_dashboard.php" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg font-medium">Dashboard</a>
                    <a href="post_problem.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Post Problem</a>
                    <a href="post_gig.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Post Gig</a>
                    <a href="my_gigs.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">My Gigs</a>
                    <a href="profile.php" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Profile</a>
                    <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Logout</a>
                </div>
                
                <button id="mobileMenuBtn" class="md:hidden text-gray-600 text-2xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div id="mobileMenu" class="hidden md:hidden pb-4 space-y-2">
                <a href="citizen_dashboard.php" class="block py-2 px-4 bg-blue-50 text-blue-700 rounded-lg">Dashboard</a>
                <a href="post_problem.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg">Post Problem</a>
                <a href="post_gig.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg">Post Gig</a>
                <a href="my_gigs.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg">My Gigs</a>
                <a href="profile.php" class="block py-2 px-4 text-gray-600 hover:bg-gray-100 rounded-lg">Profile</a>
                <a href="logout.php" class="block py-2 px-4 bg-red-500 text-white rounded-lg">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-8 text-white mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        <i class="fas fa-home mr-3"></i>Welcome, <?= e($user['name']) ?>!
                    </h1>
                    <p class="text-blue-100">Your voice matters. Help us solve your community problems.</p>
                </div>
                <a href="post_problem.php" class="mt-4 md:mt-0 bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-6 rounded-full transition-all transform hover:scale-105 shadow-lg">
                    <i class="fas fa-plus-circle mr-2"></i>Post New Problem
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Problems</p>
                        <p class="text-3xl font-bold text-blue-600"><?= $totalProblems ?></p>
                    </div>
                    <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Solved</p>
                        <p class="text-3xl font-bold text-green-600"><?= $solvedProblems ?></p>
                    </div>
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Solutions Received</p>
                        <p class="text-3xl font-bold text-purple-600"><?= count($solutions) ?></p>
                    </div>
                    <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-lightbulb text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">My Gigs</p>
                        <p class="text-3xl font-bold text-amber-600"><?= count($gigs) ?></p>
                    </div>
                    <div class="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-briefcase text-2xl text-amber-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- My Problems -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold"><i class="fas fa-list-alt mr-2 text-blue-600"></i>My Problems</h2>
                    <a href="post_problem.php" class="text-blue-600 hover:underline text-sm font-medium">+ Post New</a>
                </div>

                <?php if (empty($problems)): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-inbox text-5xl mb-4 text-gray-300"></i>
                    <p>You haven't posted any problems yet.</p>
                    <a href="post_problem.php" class="inline-block mt-4 bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-700 transition-all">
                        Post Your First Problem
                    </a>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach (array_slice($problems, 0, 5) as $problem): ?>
                    <div class="border border-gray-200 rounded-xl p-4 hover:border-blue-400 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?= getCategoryClass($problem['category']) ?>"><?= e($problem['category']) ?></span>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?= getUrgencyClass($problem['urgency']) ?>"><?= e($problem['urgency']) ?></span>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?= getStatusClass($problem['status']) ?>"><?= ucfirst(e($problem['status'])) ?></span>
                                </div>
                                <h3 class="font-semibold text-gray-800"><?= e($problem['title']) ?></h3>
                                <p class="text-xs text-gray-400 mt-1"><?= timeAgo($problem['created_at']) ?></p>
                            </div>
                            <a href="problem_detail.php?id=<?= $problem['id'] ?>" class="text-blue-600 hover:text-blue-800 ml-4">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($problems) > 5): ?>
                <p class="text-center mt-4 text-gray-500 text-sm">+ <?= count($problems) - 5 ?> more problems</p>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Solutions Received -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold mb-6"><i class="fas fa-lightbulb mr-2 text-amber-500"></i>Solutions Received</h2>

                <?php if (empty($solutions)): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-hourglass-half text-5xl mb-4 text-gray-300"></i>
                    <p>No solutions yet. Keep posting problems!</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($solutions as $solution): ?>
                    <div class="border border-gray-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-gray-800"><?= e($solution['title']) ?></span>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= getSolutionStatusClass($solution['status']) ?>">
                                <?= ucfirst(e($solution['status'])) ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">For: <?= e($solution['problem_title']) ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?= timeAgo($solution['created_at']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Gigs Section -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mt-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold"><i class="fas fa-briefcase mr-2 text-amber-500"></i>My Micro Gigs</h2>
                <div class="flex gap-2">
                    <a href="my_gigs.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-all text-sm">
                        <i class="fas fa-users mr-1"></i>View Applications
                    </a>
                    <a href="post_gig.php" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg transition-all">
                        <i class="fas fa-plus mr-2"></i>Post Gig
                    </a>
                </div>
            </div>

            <?php if (empty($gigs)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-briefcase text-4xl mb-4 text-gray-300"></i>
                <p>No micro gigs posted yet.</p>
            </div>
            <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($gigs as $gig): ?>
                <div class="border border-gray-200 rounded-xl p-4 hover:border-amber-400 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold"><?= e($gig['title']) ?></h3>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= getGigStatusClass($gig['status']) ?>">
                            <?= ucfirst(e($gig['status'])) ?>
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3"><?= e(substr($gig['description'], 0, 80)) ?>...</p>
                    <div class="flex items-center justify-between">
                        <span class="text-amber-600 font-bold"><?= formatCurrency($gig['budget']) ?></span>
                        <span class="text-xs text-gray-400"><?= timeAgo($gig['created_at']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
            <a href="post_problem.php" class="bg-blue-50 p-4 rounded-xl text-center hover:bg-blue-100 transition-all card-hover">
                <i class="fas fa-plus-circle text-2xl text-blue-600 mb-2"></i>
                <p class="text-sm font-semibold text-blue-800">Post Problem</p>
            </a>
            <a href="post_gig.php" class="bg-amber-50 p-4 rounded-xl text-center hover:bg-amber-100 transition-all card-hover">
                <i class="fas fa-briefcase text-2xl text-amber-600 mb-2"></i>
                <p class="text-sm font-semibold text-amber-800">Post Gig</p>
            </a>
            <a href="profile.php" class="bg-green-50 p-4 rounded-xl text-center hover:bg-green-100 transition-all card-hover">
                <i class="fas fa-user-edit text-2xl text-green-600 mb-2"></i>
                <p class="text-sm font-semibold text-green-800">Edit Profile</p>
            </a>
            <a href="my_sponsorships.php" class="bg-purple-50 p-4 rounded-xl text-center hover:bg-purple-100 transition-all card-hover">
                <i class="fas fa-hand-holding-usd text-2xl text-purple-600 mb-2"></i>
                <p class="text-sm font-semibold text-purple-800">Sponsorships</p>
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
function getStatusClass($status) {
    $classes = ['open' => 'bg-blue-100 text-blue-800', 'in_progress' => 'bg-amber-100 text-amber-800', 'solved' => 'bg-green-100 text-green-800'];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}
function getSolutionStatusClass($status) {
    $classes = ['pending' => 'bg-amber-100 text-amber-800', 'approved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800', 'rewarded' => 'bg-purple-100 text-purple-800'];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}
function getGigStatusClass($status) {
    $classes = ['open' => 'bg-green-100 text-green-800', 'assigned' => 'bg-blue-100 text-blue-800', 'completed' => 'bg-purple-100 text-purple-800'];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}
?>

</body>
</html>