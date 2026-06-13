<?php
/**
 * ELITE-4 Nepal - Public Landing Page
 * "The Problem Solver" - Community-Driven Innovation Platform
 */
require_once 'config.php';

$successStories = getSuccessStories(5);
$recentProblems = getRecentProblems(6);
$stats = getStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELITE-4 Nepal - The Problem Solver</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        elite: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' },
                        success: { 500: '#10b981', 600: '#059669' }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        .hero-gradient {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 50%, #7c3aed 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .carousel-container {
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .carousel-container::-webkit-scrollbar { display: none; }
        .carousel-item { scroll-snap-align: start; }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            backdrop-filter: blur(10px);
        }
        
        . SDG-icons { animation: float 3s ease-in-out infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        .pulse-ring {
            animation: pulse-ring 2s infinite;
        }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        
        .slide-up {
            animation: slideUp 0.6s ease-out forwards;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .story-card {
            min-width: 350px;
            max-width: 400px;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #3b82f6, #7c3aed); border-radius: 4px; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="fixed w-full z-50 bg-white/95 backdrop-blur-md shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-lightbulb text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="font-bold text-xl text-gray-800">ELITE-4</span>
                        <span class="text-sm text-blue-600 block -mt-1">Nepal</span>
                    </div>
                </div>
                
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#features" class="text-gray-600 hover:text-blue-600 transition-colors font-medium">Features</a>
                    <a href="#success" class="text-gray-600 hover:text-blue-600 transition-colors font-medium">Success Stories</a>
                    <a href="#stats" class="text-gray-600 hover:text-blue-600 transition-colors font-medium">Impact</a>
                    <a href="login.php" class="text-blue-600 hover:text-blue-700 font-medium">Login</a>
                    <a href="register.php" class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-5 py-2 rounded-full font-medium hover:shadow-lg transition-all">
                        Get Started
                    </a>
                </div>
                
                <button id="mobileMenuBtn" class="md:hidden text-gray-600 text-2xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t">
            <div class="px-4 py-4 space-y-3">
                <a href="#features" class="block py-2 text-gray-600 hover:text-blue-600">Features</a>
                <a href="#success" class="block py-2 text-gray-600 hover:text-blue-600">Success Stories</a>
                <a href="#stats" class="block py-2 text-gray-600 hover:text-blue-600">Impact</a>
                <a href="login.php" class="block py-2 text-blue-600 font-medium">Login</a>
                <a href="register.php" class="block bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-5 py-3 rounded-full font-medium text-center">Get Started</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient min-h-screen flex items-center pt-16 relative overflow-hidden">
        <!-- Background decorations -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 left-10 w-72 h-72 bg-purple-500/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-white">
                    <div class="inline-flex items-center bg-white/10 px-4 py-2 rounded-full mb-6">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                        <span class="text-sm">Platform Live • Join 500+ Innovators</span>
                    </div>
                    
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">
                        <span class="gradient-text">The Problem Solver</span>
                        <br>for Nepal
                    </h1>
                    
                    <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                        Citizens post real-world problems. Students form teams to solve them. 
                        Sponsors fund innovations. Mentors guide success. All in one powerful platform.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="register.php" class="inline-flex items-center justify-center bg-amber-500 hover:bg-amber-600 text-white font-bold py-4 px-8 rounded-full text-lg transition-all transform hover:scale-105 shadow-xl pulse-ring">
                            <i class="fas fa-rocket mr-3"></i>Start Innovating
                        </a>
                        <a href="#success" class="inline-flex items-center justify-center bg-white/10 hover:bg-white/20 text-white font-semibold py-4 px-8 rounded-full text-lg transition-all border-2 border-white/30">
                            <i class="fas fa-play-circle mr-3"></i>See Success Stories
                        </a>
                    </div>
                    
                    <div class="flex items-center gap-8 mt-10 text-sm text-blue-100">
                        <div class="flex items-center">
                            <i class="fas fa-users mr-2 text-amber-400"></i>
                            <span>500+ Students</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-hand-holding-usd mr-2 text-green-400"></i>
                            <span>Rs. 5M+ Rewarded</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2 text-blue-400"></i>
                            <span>200+ Solutions</span>
                        </div>
                    </div>
                </div>
                
                <div class="hidden lg:block relative">
                    <div class="absolute -top-10 -right-10 w-72 h-72 bg-gradient-to-br from-purple-500/30 to-pink-500/30 rounded-full blur-2xl"></div>
                    <div class="relative bg-white/10 backdrop-blur-lg rounded-3xl p-8 border border-white/20">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                                <i class="fas fa-lightbulb text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-white font-bold text-lg">Active Problem</p>
                                <p class="text-blue-200 text-sm">Garbage crisis in Kapan</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between bg-white/10 rounded-xl p-4">
                                <span class="text-blue-100">Category</span>
                                <span class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-sm">Waste</span>
                            </div>
                            <div class="flex items-center justify-between bg-white/10 rounded-xl p-4">
                                <span class="text-blue-100">Urgency</span>
                                <span class="bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-sm">High</span>
                            </div>
                            <div class="flex items-center justify-between bg-white/10 rounded-xl p-4">
                                <span class="text-blue-100">Solutions</span>
                                <span class="text-white font-bold">3 Teams Interested</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex items-center gap-3">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 bg-amber-500 rounded-full border-2 border-white/20 flex items-center justify-center text-xs text-white font-bold">SK</div>
                                <div class="w-8 h-8 bg-green-500 rounded-full border-2 border-white/20 flex items-center justify-center text-xs text-white font-bold">GT</div>
                                <div class="w-8 h-8 bg-purple-500 rounded-full border-2 border-white/20 flex items-center justify-center text-xs text-white font-bold">+1</div>
                            </div>
                            <span class="text-blue-200 text-sm">3 students working on this</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Stats Section -->
    <section id="stats" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="stat-card p-6 rounded-2xl border border-gray-200 text-center slide-up" style="animation-delay: 0.1s">
                    <div class="text-4xl font-bold text-blue-600 mb-2" id="statProblems">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="text-gray-500 font-medium">Problems Posted</div>
                </div>
                <div class="stat-card p-6 rounded-2xl border border-gray-200 text-center slide-up" style="animation-delay: 0.2s">
                    <div class="text-4xl font-bold text-green-600 mb-2" id="statSolutions">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="text-gray-500 font-medium">Solutions Found</div>
                </div>
                <div class="stat-card p-6 rounded-2xl border border-gray-200 text-center slide-up" style="animation-delay: 0.3s">
                    <div class="text-4xl font-bold text-amber-600 mb-2" id="statRewards">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="text-gray-500 font-medium">Rewards Given</div>
                </div>
                <div class="stat-card p-6 rounded-2xl border border-gray-200 text-center slide-up" style="animation-delay: 0.4s">
                    <div class="text-4xl font-bold text-purple-600 mb-2" id="statSponsors">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="text-gray-500 font-medium">Active Sponsors</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Success Stories Carousel -->
    <section id="success" class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="inline-block bg-blue-100 text-blue-700 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    <i class="fas fa-trophy mr-2"></i>Success Stories
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Real Impact, Real Change</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">See how our platform has helped startups make a difference in Nepal's communities</p>
            </div>
            
            <div class="relative">
                <!-- Carousel Controls -->
                <button onclick="scrollCarousel(-1)" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-white shadow-lg rounded-full flex items-center justify-center text-gray-600 hover:text-blue-600 transition-colors -ml-6">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button onclick="scrollCarousel(1)" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-white shadow-lg rounded-full flex items-center justify-center text-gray-600 hover:text-blue-600 transition-colors -mr-6">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Carousel -->
                <div id="successCarousel" class="carousel-container flex gap-6 pb-4">
                    <?php foreach ($successStories as $story): ?>
                    <div class="carousel-item story-card glass-card rounded-2xl p-6 card-hover">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-user-graduate text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg"><?= e($story['title']) ?></h3>
                                <p class="text-sm text-gray-500"><?= e($story['author_name']) ?><?= !empty($story['author_role']) ? ' - ' . e($story['author_role']) : '' ?></p>
                            </div>
                        </div>
                        
                        <p class="text-gray-600 mb-4 leading-relaxed line-clamp-4"><?= e($story['story']) ?></p>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div>
                                <p class="text-xs text-gray-500">Total Funded</p>
                                <p class="text-xl font-bold text-green-600"><?= formatCurrency($story['reward_amount']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">Impact</p>
                                <p class="text-sm font-semibold text-blue-600"><?= e($story['impact_metric']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Call to action card -->
                    <div class="carousel-item story-card bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 text-white flex flex-col justify-center items-center min-h-[280px]">
                        <i class="fas fa-rocket text-5xl mb-4 opacity-80"></i>
                        <h3 class="font-bold text-xl mb-2">Want to Make Impact?</h3>
                        <p class="text-blue-100 text-center mb-6">Join as a sponsor and fund the next big solution</p>
                        <a href="register.php?role=sponsor" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-6 rounded-full transition-all">
                            Become a Sponsor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    <i class="fas fa-star mr-2"></i>Platform Features
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">How ELITE-4 Works</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">A complete ecosystem connecting problem owners, solution builders, and change makers</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Citizens -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8 card-hover border border-blue-100">
                    <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Citizens Post Problems</h3>
                    <p class="text-gray-600 mb-4">Share real-world issues with photos, voice notes, and location. AI auto-categorizes for you.</p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Voice input support</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Photo upload</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Track progress</li>
                    </ul>
                </div>
                
                <!-- Students -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-8 card-hover border border-green-100">
                    <div class="w-16 h-16 bg-green-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-graduation-cap text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Students Form Teams</h3>
                    <p class="text-gray-600 mb-4">Browse problems, form teams, submit solutions, and earn rewards while building your portfolio.</p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Public team listings</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Milestone tracking</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Leaderboard ranking</li>
                    </ul>
                </div>
                
                <!-- Sponsors -->
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-8 card-hover border border-amber-100">
                    <div class="w-16 h-16 bg-amber-500 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-hand-holding-usd text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Sponsors Fund Innovation</h3>
                    <p class="text-gray-600 mb-4">Create challenges with rewards, sponsor promising teams, and track your impact analytics.</p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Analytics dashboard</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Direct sponsorship</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Progress tracking</li>
                    </ul>
                </div>
                
                <!-- Mentors -->
                <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-2xl p-8 card-hover border border-purple-100">
                    <div class="w-16 h-16 bg-purple-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-chalkboard-teacher text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Mentors Guide Teams</h3>
                    <p class="text-gray-600 mb-4">Review solutions, provide feedback, chat with teams, and earn consultation fees.</p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Team assignment</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Solution review</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Private chat</li>
                    </ul>
                </div>
                
                <!-- Micro Gigs -->
                <div class="bg-gradient-to-br from-cyan-50 to-teal-50 rounded-2xl p-8 card-hover border border-cyan-100">
                    <div class="w-16 h-16 bg-cyan-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-briefcase text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Micro Gigs (SDG 8)</h3>
                    <p class="text-gray-600 mb-4">Citizens post small paid tasks. Students apply and earn. No commission on gig payments!</p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>100% payment to students</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Various categories</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Decent work & growth</li>
                    </ul>
                </div>
                
                <!-- Admin -->
                <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-2xl p-8 card-hover border border-gray-200">
                    <div class="w-16 h-16 bg-gray-700 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Full Moderation</h3>
                    <p class="text-gray-600 mb-4">Admin dashboard with chat moderation, team management, and platform settings control.</p>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Message moderation</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>User management</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Commission control</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Problems -->
    <section class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-12">
                <div>
                    <span class="inline-block bg-orange-100 text-orange-700 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                        <i class="fas fa-fire mr-2"></i>Active Problems
                    </span>
                    <h2 class="text-3xl font-bold text-gray-800">Recent Community Problems</h2>
                </div>
                <a href="<?= isLoggedIn() ? 'citizen_dashboard.php' : 'register.php' ?>" class="hidden md:inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-full transition-all">
                    <i class="fas fa-plus mr-2"></i>Post Problem
                </a>
            </div>
            
            <div id="problemsGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Loading state -->
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                    <p class="text-gray-500">Loading problems...</p>
                </div>
            </div>
            
            <div class="text-center mt-8 md:hidden">
                <a href="<?= isLoggedIn() ? 'citizen_dashboard.php' : 'register.php' ?>" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-full transition-all">
                    <i class="fas fa-plus mr-2"></i>Post Problem
                </a>
            </div>
        </div>
    </section>

    <!-- Governance Section - NEW -->
    <section class="py-16 bg-gradient-to-br from-indigo-900 to-purple-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="inline-block bg-amber-500/20 text-amber-300 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    <i class="fas fa-shield-alt mr-2"></i>New: Governance Rules
                </span>
                <h2 class="text-2xl md:text-3xl font-bold mb-4">Enterprise-Grade Trust & Accountability</h2>
                <p class="text-indigo-200 max-w-2xl mx-auto">11 governance rules designed for Nepal's startup ecosystem. Build trust with every interaction.</p>
            </div>
            
            <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6 mb-10">
                <!-- Trust Score -->
                <div class="bg-white/10 backdrop-blur rounded-xl p-5 border border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-amber-400 to-amber-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-star text-white"></i>
                        </div>
                        <span class="font-bold">Trust Score</span>
                    </div>
                    <p class="text-sm text-indigo-200">Everyone starts at 100. Earn points for milestones (+5), progress updates (+2), and gold badges (+15).</p>
                </div>
                
                <!-- PoP System -->
                <div class="bg-white/10 backdrop-blur rounded-xl p-5 border border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                        <span class="font-bold">Proof of Progress</span>
                    </div>
                    <p class="text-sm text-indigo-200">Teams submit progress updates every 14 days. Inactive teams get -10 points and marked inactive.</p>
                </div>
                
                <!-- First-Look Rights -->
                <div class="bg-white/10 backdrop-blur rounded-xl p-5 border border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-amber-400 to-orange-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-eye text-white"></i>
                        </div>
                        <span class="font-bold">First-Look Rights</span>
                    </div>
                    <p class="text-sm text-indigo-200">Loyal sponsors get 48-hour early access to top problems and teams before public release.</p>
                </div>
                
                <!-- Startup Verification -->
                <div class="bg-white/10 backdrop-blur rounded-xl p-5 border border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-certificate text-white"></i>
                        </div>
                        <span class="font-bold">Startup Verification</span>
                    </div>
                    <p class="text-sm text-indigo-200">Verified startups get badge, premium access. Unverified can't post above Rs. 25,000.</p>
                </div>
                
                <!-- Escrow Deposit -->
                <div class="bg-white/10 backdrop-blur rounded-xl p-5 border border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-yellow-400 to-amber-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-lock text-white"></i>
                        </div>
                        <span class="font-bold">Escrow Deposit</span>
                    </div>
                    <p class="text-sm text-indigo-200">Sponsors deposit 10-20% upfront. Funds locked until milestone completion or dispute resolved.</p>
                </div>
                
                <!-- IP Protection -->
                <div class="bg-white/10 backdrop-blur rounded-xl p-5 border border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shield-alt text-white"></i>
                        </div>
                        <span class="font-bold">IP Protection</span>
                    </div>
                    <p class="text-sm text-indigo-200">Every submission gets timestamp, team ownership record, and digital proof for legal protection.</p>
                </div>
                
                <!-- Gold Badge -->
                <div class="bg-white/10 backdrop-blur rounded-xl p-5 border border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-yellow-400 to-amber-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-award text-white"></i>
                        </div>
                        <span class="font-bold">Gold Badge</span>
                    </div>
                    <p class="text-sm text-indigo-200">Complete 3+ projects to earn Gold Badge. Get priority hiring and recruitment dashboard access.</p>
                </div>
                
                <!-- Mentor Validation -->
                <div class="bg-white/10 backdrop-blur rounded-xl p-5 border border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-check text-white"></i>
                        </div>
                        <span class="font-bold">Mentor Validation</span>
                    </div>
                    <p class="text-sm text-indigo-200">Challenges above Rs. 100,000 require approved mentor to reduce disputes and improve quality.</p>
                </div>
            </div>
            
            <div class="text-center">
                <a href="governance.php" class="inline-flex items-center bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-4 px-8 rounded-full transition-all shadow-lg">
                    <i class="fas fa-book-open mr-3"></i>Read Full Governance Rules
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 hero-gradient text-white text-center">
        <div class="max-w-4xl mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Make a Difference?</h2>
            <p class="text-xl text-blue-100 mb-10">Join thousands of innovators building a better Nepal, one solution at a time.</p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="register.php" class="inline-flex items-center justify-center bg-amber-500 hover:bg-amber-600 text-white font-bold py-4 px-10 rounded-full text-lg transition-all transform hover:scale-105 shadow-xl">
                    <i class="fas fa-user-plus mr-3"></i>Create Account
                </a>
                <a href="login.php" class="inline-flex items-center justify-center bg-white/10 hover:bg-white/20 text-white font-semibold py-4 px-10 rounded-full text-lg transition-all border-2 border-white/30">
                    <i class="fas fa-sign-in-alt mr-3"></i>Login
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center">
                            <i class="fas fa-lightbulb text-white text-lg"></i>
                        </div>
                        <div>
                            <span class="font-bold text-xl">ELITE-4</span>
                            <span class="text-sm text-blue-400 block">Nepal</span>
                        </div>
                    </div>
                    <p class="text-gray-400">The Problem Solver for Nepal. Community-driven innovation platform.</p>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="index.php" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="register.php" class="hover:text-white transition-colors">Register</a></li>
                        <li><a href="login.php" class="hover:text-white transition-colors">Login</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4">For Teams</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="team_public_listing.php" class="hover:text-white transition-colors">Browse Teams</a></li>
                        <li><a href="team_formation.php" class="hover:text-white transition-colors">Form Team</a></li>
                        <li><a href="micro_gigs.php" class="hover:text-white transition-colors">Find Gigs</a></li>
                        <li><a href="team_leaderboard.php" class="hover:text-white transition-colors">Leaderboard</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-envelope mr-2"></i>support@elite4nepal.com</li>
                        <li><i class="fas fa-phone mr-2"></i>+977-1-XXXXXXX</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i>Kathmandu, Nepal</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
                <p>&copy; <?= date('Y') ?> ELITE-4 Nepal. All rights reserved. Built for innovation.</p>
            </div>
        </div>
    </footer>

    <!-- Flash Messages -->
    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
    <div id="flashMessage" class="fixed bottom-6 right-6 z-50 p-4 rounded-xl shadow-2xl <?= $flash['type'] === 'success' ? 'bg-green-600' : 'bg-red-600' ?> text-white animate-bounce">
        <div class="flex items-center gap-3">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> text-xl"></i>
            <span><?= e($flash['message']) ?></span>
        </div>
    </div>
    <script>
        setTimeout(() => { document.getElementById('flashMessage').remove(); }, 4000);
    </script>
    <?php endif; ?>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });

        // Carousel scroll
        function scrollCarousel(direction) {
            const carousel = document.getElementById('successCarousel');
            const scrollAmount = 420;
            carousel.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
        }

        // Fetch stats via AJAX
        fetch('api_stats.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('statProblems').textContent = data.problems;
                document.getElementById('statSolutions').textContent = data.solutions;
                document.getElementById('statRewards').textContent = 'Rs. ' + (data.rewards || 0).toLocaleString();
                document.getElementById('statSponsors').textContent = data.sponsors;
            })
            .catch(() => {
                document.querySelectorAll('[id^="stat"]').forEach(el => el.innerHTML = '-');
            });

        // Fetch recent problems via AJAX
        fetch('api_get_recent_problems.php')
            .then(response => response.json())
            .then(data => {
                const grid = document.getElementById('problemsGrid');
                if (data.length === 0) {
                    grid.innerHTML = '<div class="col-span-full text-center py-12 text-gray-500"><i class="fas fa-inbox text-4xl mb-4"></i><p>No problems posted yet.</p></div>';
                } else {
                    grid.innerHTML = data.map(p => `
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                            ${p.photo ? `<img src="${p.photo}" alt="Problem" class="w-full h-40 object-cover">` : '<div class="w-full h-40 bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center"><i class="fas fa-image text-4xl text-gray-400"></i></div>'}
                            <div class="p-5">
                                <div class="flex gap-2 mb-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold ${getCategoryClass(p.category)}">${p.category}</span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold ${getUrgencyClass(p.urgency)}">${p.urgency}</span>
                                </div>
                                <h3 class="font-bold text-gray-800 mb-2 line-clamp-2">${escapeHtml(p.title)}</h3>
                                <p class="text-sm text-gray-500 line-clamp-2 mb-4">${escapeHtml(p.description)}</p>
                                <div class="flex items-center justify-between text-xs text-gray-400">
                                    <span><i class="fas fa-user mr-1"></i>${escapeHtml(p.user_name)}</span>
                                    <span><i class="fas fa-clock mr-1"></i>${timeAgo(p.created_at)}</span>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
            })
            .catch(() => {
                document.getElementById('problemsGrid').innerHTML = '<div class="col-span-full text-center py-12 text-red-400"><i class="fas fa-exclamation-triangle text-4xl mb-4"></i><p>Failed to load problems</p></div>';
            });

        function getCategoryClass(cat) {
            const classes = { 'Waste': 'bg-green-100 text-green-800', 'Road': 'bg-blue-100 text-blue-800', 'Health': 'bg-red-100 text-red-800', 'Water': 'bg-cyan-100 text-cyan-800', 'Other': 'bg-gray-100 text-gray-800' };
            return classes[cat] || 'bg-gray-100 text-gray-800';
        }

        function getUrgencyClass(urg) {
            const classes = { 'High': 'bg-red-500 text-white', 'Medium': 'bg-amber-500 text-white', 'Low': 'bg-green-100 text-green-800' };
            return classes[urg] || 'bg-gray-100 text-gray-800';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function timeAgo(dateStr) {
            const date = new Date(dateStr);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            if (diff < 60) return 'just now';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }
    </script>
</body>
</html>