<?php
require '../config/database.php';
require '../includes/session.php';

// Fetch current logged-in user info
$user_id = $_SESSION['user_id'];
$u = $conn->prepare("SELECT full_name, role FROM users WHERE id=?");
$u->bind_param("i", $user_id);
$u->execute();
$user = $u->get_result()->fetch_assoc();

$role = (int)$user['role'];
$is_superadmin = ($role === 0);
$can_create    = ($role === 0);

require '../includes/header.php';
require '../includes/sidebar.php';
?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        
        <header class="h-16 bg-white shadow-sm flex items-center px-6 z-10 border-b border-slate-200">
            <h1 class="text-xl font-bold text-slate-800">System Dashboard</h1>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-5xl mx-auto">
                
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl shadow-lg p-8 mb-8 text-white flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-black mb-2">Welcome back, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>!</h2>
                        <p class="text-blue-100 text-lg">Select a module below to manage employee performance records.</p>
                    </div>
                    <div class="hidden md:block opacity-80">
                        <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <a href="employees.php" class="group bg-white rounded-2xl p-8 border border-slate-200 shadow-sm hover:shadow-xl hover:border-blue-500 hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center">
                        <div class="h-20 w-20 bg-blue-50 rounded-full flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors">
                            <svg class="h-10 w-10 text-blue-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 mb-2">Employee IPCRs</h3>
                        <p class="text-slate-500 font-medium">View, edit, and evaluate Individual Performance Commitment and Review forms for all staff.</p>
                    </a>

                    <a href="#" class="group bg-white rounded-2xl p-8 border border-slate-200 shadow-sm hover:shadow-xl hover:border-purple-500 hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center">
                        <div class="h-20 w-20 bg-purple-50 rounded-full flex items-center justify-center mb-6 group-hover:bg-purple-600 transition-colors">
                            <svg class="h-10 w-10 text-purple-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 mb-2">Performance Analytics</h3>
                        <p class="text-slate-500 font-medium">Coming soon. View organizational statistics, ranking, and semestral performance graphs.</p>
                    </a>

                </div>

            </div>
        </main>
    </div>

</body>
</html>