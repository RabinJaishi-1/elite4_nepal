<?php
/**
 * ELITE-4 Nepal - Post a Problem with AI Classification & Voice Input
 */
require_once 'config.php';
requireRole('citizen');

$user = getCurrentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $voiceNote = trim($_POST['voice_note'] ?? '');
    
    if (empty($title)) $errors[] = "Title is required";
    if (empty($description)) $errors[] = "Description is required";
    
    if (empty($errors)) {
        $classification = aiClassifyProblem($title, $description);
        $category = $_POST['category'] ?? $classification['category'];
        $urgency = $_POST['urgency'] ?? $classification['urgency'];
        
        $photo = uploadFile('photo', 'uploads/');
        
        global $conn;
        $stmt = $conn->prepare("INSERT INTO problems (user_id, title, description, location, photo, voice_note, category, urgency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user['id'], $title, $description, $location, $photo, $voiceNote, $category, $urgency);
        
        if ($stmt->execute()) {
            setFlash('success', "Problem posted! AI classified as {$category} / {$urgency} urgency.");
            header("Location: citizen_dashboard.php");
            exit;
        } else {
            $errors[] = "Failed to post problem. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Problem - ELITE-4 Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>tailwind.config = { theme: { extend: { colors: { elite: { 600: '#2563eb' } } } } }</script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50">

    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-lightbulb text-white"></i>
                    </div>
                    <span class="font-bold text-xl text-gray-800">ELITE-4 <span class="text-blue-600">Nepal</span></span>
                </a>
                <a href="citizen_dashboard.php" class="text-gray-600 hover:text-blue-600 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 py-12">
        <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-12">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-circle text-3xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Post a Problem</h1>
                <p class="text-gray-500 mt-2">Share your community issue and get help from innovative teams</p>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-6">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-heading mr-2 text-blue-600"></i>Problem Title *
                    </label>
                    <input type="text" name="title" id="title" required value="<?= e($_POST['title'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all"
                        placeholder="Brief title of your problem">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-align-left mr-2 text-blue-600"></i>Description *
                    </label>
                    <textarea name="description" id="description" required rows="5"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all"
                        placeholder="Detailed description of the problem..."><?= e($_POST['description'] ?? '') ?></textarea>
                    
                    <button type="button" id="voiceBtn" onclick="toggleVoice()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-all">
                        <i class="fas fa-microphone mr-2"></i>Voice Input
                    </button>
                    <span id="voiceStatus" class="ml-3 text-sm text-gray-500"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>Location
                    </label>
                    <input type="text" name="location" value="<?= e($_POST['location'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-all"
                        placeholder="Area/Street name, City">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-camera mr-2 text-blue-600"></i>Photo
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-all">
                        <input type="file" name="photo" id="photo" accept="image/*" class="hidden" onchange="previewImage(this)">
                        <label for="photo" class="cursor-pointer">
                            <div id="previewContainer">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500">Click to upload a photo (JPG, PNG, GIF - Max 5MB)</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                    <h3 class="font-bold text-gray-800 mb-4"><i class="fas fa-robot mr-2 text-blue-600"></i>AI Auto-Classification</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-2">Predicted Category</p>
                            <select name="category" id="categorySelect" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="Waste" <?= ($_POST['category'] ?? '') === 'Waste' ? 'selected' : '' ?>>Waste Management</option>
                                <option value="Road" <?= ($_POST['category'] ?? '') === 'Road' ? 'selected' : '' ?>>Road & Transport</option>
                                <option value="Health" <?= ($_POST['category'] ?? '') === 'Health' ? 'selected' : '' ?>>Health & Sanitation</option>
                                <option value="Water" <?= ($_POST['category'] ?? '') === 'Water' ? 'selected' : '' ?>>Water & Irrigation</option>
                                <option value="Other" <?= ($_POST['category'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-2">Predicted Urgency</p>
                            <select name="urgency" id="urgencySelect" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="High" <?= ($_POST['urgency'] ?? '') === 'High' ? 'selected' : '' ?>>High</option>
                                <option value="Medium" <?= ($_POST['urgency'] ?? '') === 'Medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="Low" <?= ($_POST['urgency'] ?? '') === 'Low' ? 'selected' : '' ?>>Low</option>
                            </select>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3"><i class="fas fa-info-circle mr-1"></i>AI analyzes your text. Adjust if needed.</p>
                </div>

                <input type="hidden" name="voice_note" id="voiceNote" value="">

                <div class="flex gap-4">
                    <a href="citizen_dashboard.php" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-4 rounded-xl text-center transition-all">Cancel</a>
                    <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-bold py-4 rounded-xl transition-all transform hover:scale-[1.02] shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Post Problem
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    let recognition, isRecording = false;

    function toggleVoice() {
        if (!('webkitSpeechRecognition' in window)) {
            alert('Voice input not supported. Please use Chrome.');
            return;
        }
        if (isRecording) {
            recognition?.stop();
            isRecording = false;
            document.getElementById('voiceBtn').classList.remove('bg-red-500');
            document.getElementById('voiceBtn').classList.add('bg-blue-500');
            document.getElementById('voiceBtn').innerHTML = '<i class="fas fa-microphone mr-2"></i>Voice Input';
            document.getElementById('voiceStatus').textContent = 'Recording stopped';
        } else {
            recognition = new webkitSpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = true;
            recognition.onstart = function() {
                isRecording = true;
                document.getElementById('voiceBtn').classList.remove('bg-blue-500');
                document.getElementById('voiceBtn').classList.add('bg-red-500');
                document.getElementById('voiceBtn').innerHTML = '<i class="fas fa-stop mr-2"></i>Stop';
                document.getElementById('voiceStatus').textContent = 'Listening...';
            };
            recognition.onresult = function(event) {
                let transcript = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    transcript += event.results[i][0].transcript;
                }
                const desc = document.getElementById('description');
                desc.value = desc.value ? desc.value + ' ' + transcript : transcript;
            };
            recognition.onerror = function() { stopVoice(); };
            recognition.onend = function() { stopVoice(); };
            recognition.start();
        }
    }

    function stopVoice() {
        isRecording = false;
        document.getElementById('voiceBtn').classList.remove('bg-red-500');
        document.getElementById('voiceBtn').classList.add('bg-blue-500');
        document.getElementById('voiceBtn').innerHTML = '<i class="fas fa-microphone mr-2"></i>Voice Input';
        document.getElementById('voiceStatus').textContent = 'Recording stopped';
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewContainer').innerHTML = '<img src="' + e.target.result + '" class="max-h-40 mx-auto rounded-lg">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    let classifyTimeout;
    function autoClassify() {
        clearTimeout(classifyTimeout);
        classifyTimeout = setTimeout(function() {
            const text = (document.getElementById('title').value + ' ' + document.getElementById('description').value).toLowerCase();
            if (text.length > 20) {
                if (text.includes('garbage') || text.includes('waste') || text.includes('trash')) document.getElementById('categorySelect').value = 'Waste';
                else if (text.includes('road') || text.includes('pothole') || text.includes('street')) document.getElementById('categorySelect').value = 'Road';
                else if (text.includes('water') || text.includes('river') || text.includes('contaminated')) document.getElementById('categorySelect').value = 'Water';
                else if (text.includes('health') || text.includes('hospital') || text.includes('sick')) document.getElementById('categorySelect').value = 'Health';
                
                if (text.includes('urgent') || text.includes('emergency') || text.includes('danger') || text.includes('accident')) document.getElementById('urgencySelect').value = 'High';
                else if (text.includes('slow') || text.includes('eventually')) document.getElementById('urgencySelect').value = 'Low';
                else document.getElementById('urgencySelect').value = 'Medium';
            }
        }, 1000);
    }

    document.getElementById('title').addEventListener('input', autoClassify);
    document.getElementById('description').addEventListener('input', autoClassify);
    </script>

</body>
</html>