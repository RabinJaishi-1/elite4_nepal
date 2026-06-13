<?php
/**
 * ELITE-4 Nepal - User Registration
 */
require_once 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'citizen';
    $dob = $_POST['dob'] ?? null;
    $skills = trim($_POST['skills'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    // Validation
    if (empty($name)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (!in_array($role, ['citizen', 'student', 'sponsor', 'mentor'])) {
        $errors[] = "Please select a valid role";
    }
    
    // Check email uniqueness
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $r = $stmt->get_result();
    if ($r && $r->num_rows > 0)
        $errors[] = "This email is already registered";
    
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, date_of_birth, skills, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $name, $email, $phone, $hashedPassword, $role, $dob, $skills, $bio);
        
        if ($stmt->execute()) {
            $userId = $conn->insert_id;
            
            // Create free subscription
            $stmtSub = $conn->prepare("INSERT INTO user_subscriptions (user_id, plan, start_date, end_date, mentor_messages_used, mentor_messages_reset_at) VALUES (?, 'free', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 0, CURDATE())");
            $stmtSub->bind_param("i", $userId);
            $stmtSub->execute();
            
            // Create chat group for mentors
            if ($role === 'mentor') {
                $stmtGroup = $conn->prepare("INSERT INTO chat_groups (name, type) VALUES (?, 'mentor')");
                $mentorName = $name . "'s Chat";
                $stmtGroup->bind_param("s", $mentorName);
                $stmtGroup->execute();
            }
            
            $_SESSION['user_id'] = $userId;
            setFlash('success', 'Welcome to ELITE-4 Nepal! Your account has been created.');
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

$selectedRole = $_GET['role'] ?? $_POST['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ELITE-4 Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { elite: { 600: '#2563eb', 700: '#1d4ed8' } }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .step-indicator { transition: all 0.3s ease; }
        .step-indicator.active { background: #2563eb; color: white; }
        .step-indicator.completed { background: #10b981; color: white; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm py-4">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
            <a href="index.php" class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-lightbulb text-white"></i>
                </div>
                <span class="font-bold text-xl text-gray-800">ELITE-4 <span class="text-blue-600">Nepal</span></span>
            </a>
            <a href="login.php" class="text-blue-600 hover:text-blue-700 font-medium">
                Already have an account? <span class="font-bold">Login</span>
            </a>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto px-4 py-12">
        <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-12">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-plus text-3xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Create Your Account</h1>
                <p class="text-gray-500 mt-2">Join the problem-solving revolution</p>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-6">
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6" id="registrationForm">
                <!-- Step 1: Basic Info -->
                <div id="step1" class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                        <span class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm mr-3">1</span>
                        Basic Information
                    </h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Full Name *
                        </label>
                        <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all"
                            placeholder="Enter your full name">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-blue-600"></i>Email Address *
                        </label>
                        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all"
                            placeholder="you@example.com">
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-phone mr-2 text-blue-600"></i>Phone Number
                            </label>
                            <input type="text" name="phone" value="<?= e($_POST['phone'] ?? '') ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all"
                                placeholder="+977-98XXXXXXXX">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-2 text-blue-600"></i>Date of Birth
                            </label>
                            <input type="date" name="dob" value="<?= e($_POST['dob'] ?? '') ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-blue-600"></i>Password *
                            </label>
                            <input type="password" name="password" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all"
                                placeholder="Minimum 6 characters">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-blue-600"></i>Confirm Password *
                            </label>
                            <input type="password" name="confirm_password" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all"
                                placeholder="Re-enter your password">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Role Selection -->
                <div id="step2" class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                        <span class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm mr-3">2</span>
                        Select Your Role
                    </h3>

                    <div class="grid grid-cols-2 gap-4">
                        <?php
                        $roles = [
                            'citizen' => ['icon' => 'users', 'title' => 'Citizen', 'desc' => 'Post problems from your community'],
                            'student' => ['icon' => 'graduation-cap', 'title' => 'Student', 'desc' => 'Solve problems, earn rewards'],
                            'sponsor' => ['icon' => 'hand-holding-usd', 'title' => 'Sponsor', 'desc' => 'Fund challenges & solutions'],
                            'mentor' => ['icon' => 'chalkboard-teacher', 'title' => 'Mentor', 'desc' => 'Guide teams to success']
                        ];
                        foreach ($roles as $value => $r):
                        ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="<?= $value ?>" class="sr-only peer" <?= $selectedRole === $value ? 'checked' : '' ?>>
                            <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-blue-300">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 mx-auto">
                                    <i class="fas fa-<?= $r['icon'] ?> text-blue-600 text-xl"></i>
                                </div>
                                <h4 class="font-bold text-center text-gray-800"><?= $r['title'] ?></h4>
                                <p class="text-xs text-gray-500 text-center mt-1"><?= $r['desc'] ?></p>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Step 3: Profile Info (for students/mentors) -->
                <div id="step3" class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                        <span class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm mr-3">3</span>
                        Additional Profile Info
                    </h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tools mr-2 text-blue-600"></i>Skills (comma separated)
                        </label>
                        <input type="text" name="skills" value="<?= e($_POST['skills'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all"
                            placeholder="e.g., Programming, Design, Research">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>Bio
                        </label>
                        <textarea name="bio" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all"
                            placeholder="Tell us about yourself..."><?= e($_POST['bio'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-bold py-4 rounded-xl transition-all transform hover:scale-[1.02] shadow-lg">
                        <i class="fas fa-user-plus mr-2"></i>Create Account
                    </button>
                    <a href="index.php" class="px-6 py-4 border border-gray-300 rounded-xl text-gray-600 hover:bg-gray-50 transition-all flex items-center">
                        Cancel
                    </a>
                </div>
            </form>

            <div class="mt-8 p-4 bg-blue-50 rounded-xl">
                <p class="text-sm text-blue-800 font-medium mb-2"><i class="fas fa-info-circle mr-2"></i>Demo Account</p>
                <p class="text-xs text-blue-600">Password for all demo accounts: <code class="bg-blue-100 px-2 py-1 rounded">password123</code></p>
                <div class="mt-2 text-xs text-blue-600 grid grid-cols-2 gap-1">
                    <span>citizen@elite4.com - Citizen</span>
                    <span>student@elite4.com - Student</span>
                    <span>sponsor@elite4.com - Sponsor</span>
                    <span>mentor@elite4.com - Mentor</span>
                    <span>admin@elite4.com - Admin</span>
                </div>
            </div>
        </div>
    </div>

</body>
</html>