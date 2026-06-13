<?php
/**
 * ELITE InnovHub - Mentor Chat (Private)
 */
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
global $conn;

$sub = getUserSubscription($user['id']);
$remainingMessages = getRemainingMentorMessages($user['id']);

// Get all mentors
$qm = $conn->query("SELECT * FROM users WHERE role = 'mentor'"); $mentors = $qm ? $qm->fetch_all(MYSQLI_ASSOC) : [];

// Selected mentor
$mentorId = (int)($_GET['mentor_id'] ?? 0);
$selectedMentor = $mentorId ? getUserById($mentorId) : null;

// Handle send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message'] ?? '');
    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    
    if ($message && $receiverId) {
        // Check if can message
        if (!canMessageMentor($user['id']) && $user['role'] === 'student') {
            setFlash('error', 'You have reached your weekly mentor message limit. Upgrade to Plus/Premium for unlimited!');
            header("Location: subscription.php");
            exit;
        }
        
        // Insert message
        $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user['id'], $receiverId, $message);
        $stmt->execute();
        
        // Increment message count if student (not mentor)
        if ($user['role'] === 'student' && !$sub['is_plus']) {
            incrementMentorMessages($user['id']);
        }
        
        setFlash('success', 'Message sent!');
        header("Location: mentor_chat.php?mentor_id=" . $receiverId);
        exit;
    }
}

// Get messages with selected mentor
$messages = [];
if ($selectedMentor) {
    $stmt = $conn->prepare("SELECT m.*, u.name as sender_name FROM chat_messages m JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) ORDER BY m.created_at ASC");
    $stmt->bind_param("iiii", $user['id'], $mentorId, $mentorId, $user['id']);
    $stmt->execute();
    $r = $stmt->get_result();
    $messages = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

getHeader('Mentor Chat');
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Mentor List Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="gradient-bg p-4 text-white">
                    <h2 class="font-bold"><i class="fas fa-chalkboard-teacher mr-2"></i>Available Mentors</h2>
                </div>
                
                <!-- Message Limit Info -->
                <?php if (!$sub['is_plus']): ?>
                    <div class="p-4 bg-amber-50 border-b">
                        <p class="text-sm text-amber-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <?= $remainingMessages ?> messages remaining this week
                        </p>
                        <a href="subscription.php" class="text-xs text-amber-600 hover:underline">Upgrade for unlimited</a>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-green-50 border-b">
                        <p class="text-sm text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            Unlimited messages (<?= ucfirst($sub['plan']) ?>)
                        </p>
                    </div>
                <?php endif; ?>
                
                <div class="divide-y max-h-96 overflow-y-auto">
                    <?php foreach ($mentors as $mentor): ?>
                        <a href="mentor_chat.php?mentor_id=<?= $mentor['id'] ?>" 
                           class="flex items-center gap-3 p-4 hover:bg-gray-50 transition-all <?= $mentorId === $mentor['id'] ? 'bg-primary/10' : '' ?>">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <?php if ($mentor['profile_photo']): ?>
                                    <img src="<?= e($mentor['profile_photo']) ?>" class="w-12 h-12 rounded-full object-cover">
                                <?php else: ?>
                                    <span class="font-bold text-purple-600"><?= getInitials($mentor['name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold truncate"><?= e($mentor['name']) ?></p>
                                <p class="text-xs text-gray-500 truncate"><?= e($mentor['skills'] ?? 'Mentor') ?></p>
                            </div>
                            <?php if ($mentorId === $mentor['id']): ?>
                                <i class="fas fa-comment text-primary"></i>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Chat Area -->
        <div class="lg:col-span-3">
            <?php if ($selectedMentor): ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden" style="height: 500px; display: flex; flex-direction: column;">
                    <!-- Chat Header -->
                    <div class="gradient-bg p-4 text-white flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <?php if ($selectedMentor['profile_photo']): ?>
                                <img src="<?= e($selectedMentor['profile_photo']) ?>" class="w-12 h-12 rounded-full object-cover">
                            <?php else: ?>
                                <span class="font-bold"><?= getInitials($selectedMentor['name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3 class="font-bold"><?= e($selectedMentor['name']) ?></h3>
                            <p class="text-sm text-purple-100"><?= e($selectedMentor['skills'] ?? 'Mentor') ?></p>
                        </div>
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
                                    <div class="max-w-[70%] <?= $isOwn ? 'message-bubble-own text-white' : 'message-bubble-other text-gray-800' ?> rounded-2xl px-4 py-3">
                                        <?php if (!$isOwn): ?>
                                            <p class="text-xs font-semibold mb-1 opacity-75"><?= e($msg['sender_name']) ?></p>
                                        <?php endif; ?>
                                        <p class="break-words"><?= e($msg['message']) ?></p>
                                        <p class="text-xs mt-1 opacity-50"><?= timeAgo($msg['created_at']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Message Input -->
                    <div class="border-t p-4 bg-gray-50">
                        <?php if (!canMessageMentor($user['id']) && $user['role'] === 'student' && !$sub['is_plus']): ?>
                            <div class="bg-amber-100 text-amber-800 p-4 rounded-lg text-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                You've used all your free mentor messages this week.
                                <a href="subscription.php" class="font-bold hover:underline ml-2">Upgrade Now</a>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="flex gap-4">
                                <input type="hidden" name="receiver_id" value="<?= $mentorId ?>">
                                <input type="text" name="message" required
                                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                    placeholder="Type your message...">
                                <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg transition-all">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <div class="w-24 h-24 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comment-dots text-4xl text-purple-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Select a Mentor</h2>
                    <p class="text-gray-500">Choose a mentor from the left to start chatting</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php getFooter(); ?>