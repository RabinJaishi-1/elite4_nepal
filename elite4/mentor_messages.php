<?php
/**
 * ELITE-4 Nepal - Mentor Inbox (All Student Conversations)
 */
require_once 'config.php';
requireRole('mentor');

$user = getCurrentUser();
global $conn;

$message = '';
$success = '';

// Handle send reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $message = trim($_POST['reply_message'] ?? '');
    
    if ($receiverId && $message) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        if ($stmt !== false) {
            $stmt->bind_param("iis", $user['id'], $receiverId, $message);
            if ($stmt->execute()) {
                setFlash('success', 'Reply sent successfully!');
                header("Location: mentor_messages.php?student_id=" . $receiverId);
                exit;
            }
        }
    }
}

// Get all students who messaged this mentor
$stmt = $conn->prepare("
    SELECT DISTINCT 
        CASE 
            WHEN cm.sender_id = ? THEN cm.receiver_id 
            ELSE cm.sender_id 
        END as student_id,
        u.name as student_name,
        u.email as student_email,
        u.profile_photo,
        u.skills,
        (SELECT MAX(created_at) FROM chat_messages WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?)) as last_message_time,
        (SELECT COUNT(*) FROM chat_messages WHERE sender_id = u.id AND receiver_id = ? AND is_deleted = 0) as unread_count
    FROM chat_messages cm
    JOIN users u ON (cm.sender_id = u.id OR cm.receiver_id = u.id) AND u.id != ?
    WHERE (cm.sender_id = ? OR cm.receiver_id = ?)
    ORDER BY last_message_time DESC
");
$stmt->bind_param("iiiiiii", $user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id']);
$stmt->execute();
$r = $stmt->get_result();
$conversations = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

// Get selected student and messages
$studentId = (int)($_GET['student_id'] ?? 0);
$selectedStudent = null;
$messages = [];

if ($studentId) {
    $selectedStudent = getUserById($studentId);
    if ($selectedStudent && $selectedStudent['role'] === 'student') {
        // Mark messages as read
        $stmt = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
        $stmt->bind_param("ii", $studentId, $user['id']);
        $stmt->execute();
        
        // Get conversation
        $stmt = $conn->prepare("
            SELECT cm.*, u.name as sender_name 
            FROM chat_messages cm 
            JOIN users u ON cm.sender_id = u.id 
            WHERE (cm.sender_id = ? AND cm.receiver_id = ?) OR (cm.sender_id = ? AND cm.receiver_id = ?)
            ORDER BY cm.created_at ASC
        ");
        $stmt->bind_param("iiii", $studentId, $user['id'], $user['id'], $studentId);
        $stmt->execute();
        $r = $stmt->get_result();
        $messages = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }
}

// Get teams assigned to this mentor
$stmt = $conn->prepare("
    SELECT t.*, u.name as leader_name,
    (SELECT COUNT(*) FROM team_milestones WHERE team_id = t.id AND status = 'completed') as completed
    FROM mentor_assignments ma
    JOIN teams t ON ma.team_id = t.id
    JOIN users u ON t.leader_id = u.id
    WHERE ma.mentor_id = ?
");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$r = $stmt->get_result();
$assignedTeams = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

getHeader('Mentor Inbox');
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conversations List -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="gradient-bg p-4 text-white">
                    <h2 class="font-bold"><i class="fas fa-inbox mr-2"></i>Student Messages</h2>
                    <p class="text-sm text-purple-200"><?= count($conversations) ?> conversations</p>
                </div>
                
                <div class="divide-y max-h-[600px] overflow-y-auto">
                    <?php if (empty($conversations)): ?>
                    <div class="p-8 text-center text-gray-400">
                        <i class="fas fa-comments text-3xl mb-2"></i>
                        <p>No messages yet</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                    <a href="mentor_messages.php?student_id=<?= $conv['student_id'] ?>" 
                       class="flex items-center gap-3 p-4 hover:bg-gray-50 transition-all <?= $studentId === $conv['student_id'] ? 'bg-purple-50' : '' ?>">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <?php if ($conv['profile_photo']): ?>
                                <img src="<?= e($conv['profile_photo']) ?>" class="w-12 h-12 rounded-full object-cover">
                            <?php else: ?>
                                <span class="font-bold text-blue-600"><?= getInitials($conv['student_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center">
                                <p class="font-semibold truncate"><?= e($conv['student_name']) ?></p>
                                <?php if ($conv['unread_count'] > 0): ?>
                                <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $conv['unread_count'] ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-500 truncate"><?= e($conv['skills'] ?? 'Student') ?></p>
                        </div>
                        <?php if ($studentId === $conv['student_id']): ?>
                        <i class="fas fa-comment text-purple-600"></i>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="lg:col-span-2">
            <?php if ($selectedStudent): ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden" style="height: 600px; display: flex; flex-direction: column;">
                <!-- Chat Header -->
                <div class="gradient-bg p-4 text-white flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <?php if ($selectedStudent['profile_photo']): ?>
                            <img src="<?= e($selectedStudent['profile_photo']) ?>" class="w-12 h-12 rounded-full object-cover">
                        <?php else: ?>
                            <span class="font-bold"><?= getInitials($selectedStudent['name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="font-bold"><?= e($selectedStudent['name']) ?></h3>
                        <p class="text-sm text-purple-100"><?= e($selectedStudent['skills'] ?? 'Student') ?></p>
                    </div>
                    <a href="profile.php?user_id=<?= $selectedStudent['id'] ?>" class="ml-auto bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg text-sm">
                        <i class="fas fa-user mr-1"></i>View Profile
                    </a>
                </div>

                <!-- Messages -->
                <div id="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                    <?php if (empty($messages)): ?>
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-comments text-4xl mb-4"></i>
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                    <?php $isOwn = $msg['sender_id'] == $user['id']; ?>
                    <div class="flex <?= $isOwn ? 'justify-end' : 'justify-start' ?>">
                        <div class="max-w-[70%] <?= $isOwn ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-800' ?> rounded-2xl px-4 py-3">
                            <?php if (!$isOwn): ?>
                            <p class="text-xs font-semibold mb-1 opacity-75"><?= e($msg['sender_name']) ?></p>
                            <?php endif; ?>
                            <p class="break-words"><?= e($msg['message']) ?></p>
                            <p class="text-xs mt-1 opacity-50"><?= date('M j, g:i A', strtotime($msg['created_at'])) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Reply Form -->
                <div class="border-t p-4 bg-gray-50">
                    <form method="POST" class="flex gap-4">
                        <input type="hidden" name="receiver_id" value="<?= $selectedStudent['id'] ?>">
                        <input type="text" name="reply_message" required
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Type your reply...">
                        <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg transition-all">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center h-full flex items-center justify-center">
                <div>
                    <div class="w-24 h-24 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comment-dots text-4xl text-purple-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Select a Student</h2>
                    <p class="text-gray-500">Choose a student from the left to view conversation</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Assigned Teams Section -->
    <?php if (!empty($assignedTeams)): ?>
    <div class="mt-8">
        <h2 class="text-xl font-bold mb-4"><i class="fas fa-users mr-2 text-purple-600"></i>My Assigned Teams</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($assignedTeams as $team): ?>
            <a href="team_progress.php?team_id=<?= $team['id'] ?>" class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-all">
                <h4 class="font-bold text-gray-800"><?= e($team['name']) ?></h4>
                <p class="text-sm text-gray-500">Leader: <?= e($team['leader_name']) ?></p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">
                        <?= $team['completed'] ?> completed
                    </span>
                    <span class="text-amber-600 font-semibold text-sm">
                        <?= $team['rank_points'] ?? 0 ?> pts
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php getFooter(); ?>