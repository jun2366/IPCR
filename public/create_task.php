<?php
require '../config/database.php';
require '../includes/session.php';

// 1. Security Check & User Data
$user_id = $_SESSION['user_id'];
$u = $conn->prepare("SELECT full_name, role FROM users WHERE id=?");
$u->bind_param("i", $user_id);
$u->execute();
$user = $u->get_result()->fetch_assoc();
$role = (int)($user['role'] ?? 3);

// THIS IS CRITICAL FOR THE SIDEBAR TO KNOW YOU ARE AN ADMIN
$is_superadmin = ($role === 0); 

if (!$is_superadmin) { header("Location: ipcr.php"); exit(); }

// 2. Fetch all active periods
$periods_query = $conn->query("SELECT id, month, year FROM login_periods ORDER BY year ASC, id ASC");

// 3. Fetch all users
$users_query = $conn->query("SELECT id, full_name, role FROM users ORDER BY full_name ASC");

// --- PLACEHOLDER DATA FOR THE RATING MATRIX ---
$q_placeholders = [
    5 => 'e.g., with no error',
    4 => 'e.g., with 1-2 minor errors',
    3 => 'e.g., with minor error',
    2 => 'e.g., with 3-4 minor errors',
    1 => 'e.g., with major error'
];
$e_placeholders = [
    5 => 'e.g., 100%',
    4 => 'e.g., 90-99.99%',
    3 => 'e.g., 80-89.99%',
    2 => 'e.g., 70-79.99%',
    1 => 'e.g., below 70%'
];
$t_placeholders = [
    5 => 'e.g., 30 minutes',
    4 => 'e.g., 45 minutes',
    3 => 'e.g., 1 hour',
    2 => 'e.g., 1 hour and 15 minutes',
    1 => 'e.g., 1 hour and 30 minutes'
];

// === INCLUDE UI COMPONENTS ===
require '../includes/header.php';
require '../includes/sidebar.php';
?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden relative bg-slate-50">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10">
            <h1 class="text-xl font-bold text-slate-800">Create & Assign New Task</h1>
        </header>

        <main class="flex-1 overflow-y-auto p-6 flex justify-center">
            <div class="w-full max-w-6xl">

                <?php if (isset($_GET['err']) && $_GET['err'] === 'duplicate_code'): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Task Creation Failed</h3>
                            <p class="text-sm text-red-700 mt-1">
                                The Task Code you entered already exists in the database. Please use a unique Task Code (e.g., 1.5, 2.1).
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            
                <form action="create_task_backend.php" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col lg:flex-row">
                    
                    <div class="p-6 lg:w-3/4 space-y-6 border-r border-slate-200">
                        <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2">1. Define the Task</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Task Code / Number *</label>
                                <input type="text" name="task_code" required placeholder="e.g., 1.4"
                                       class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-sm font-semibold focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Output Category</label>
                                <input type="text" name="output_category" placeholder="e.g., Network Uptime"
                                       class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Output / Task Title *</label>
                            <textarea name="task_title" rows="2" required placeholder="Enter the main task description..."
                                      class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Success Indicators (General Target) *</label>
                            <textarea name="success_indicator" rows="2" required placeholder="Enter the overall success indicator..."
                                      class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm"></textarea>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <h3 class="text-sm font-bold text-slate-700 mb-3">Target Standards (Rating of 5)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Quality</label>
                                    <input type="text" name="qet_quality" placeholder="e.g., with no error" class="w-full p-2 border border-slate-300 rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Efficiency</label>
                                    <input type="text" name="qet_efficiency" placeholder="e.g., 100%" class="w-full p-2 border border-slate-300 rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Timeliness</label>
                                    <input type="text" name="qet_timeliness" placeholder="e.g., 30 minutes" class="w-full p-2 border border-slate-300 rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-lg border border-slate-200 overflow-hidden mt-6">
                            <div class="bg-slate-100 px-4 py-3 border-b border-slate-200">
                                <h3 class="text-sm font-bold text-slate-700">Rating Matrix Calibration</h3>
                                <p class="text-xs text-slate-500">Define the exact phrases for Auto-Generation. Leave blank if a specific rating does not apply.</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wider border-b border-slate-200">
                                            <th class="px-4 py-3 font-bold w-16 text-center">Score</th>
                                            <th class="px-4 py-3 font-bold w-1/3 border-l border-slate-200">Quality (Q)</th>
                                            <th class="px-4 py-3 font-bold w-1/3 border-l border-slate-200">Efficiency (E)</th>
                                            <th class="px-4 py-3 font-bold w-1/3 border-l border-slate-200">Timeliness (T)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 text-sm">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <tr class="hover:bg-white transition">
                                            <td class="px-4 py-2 text-center font-black text-slate-700 bg-slate-100"><?= $i ?></td>
                                            <td class="p-2 border-l border-slate-200">
                                                <input type="text" name="matrix[Q][<?= $i ?>]" placeholder="<?= $q_placeholders[$i] ?>" class="w-full p-2 bg-transparent border border-transparent hover:border-slate-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-200 rounded outline-none transition">
                                            </td>
                                            <td class="p-2 border-l border-slate-200">
                                                <input type="text" name="matrix[E][<?= $i ?>]" placeholder="<?= $e_placeholders[$i] ?>" class="w-full p-2 bg-transparent border border-transparent hover:border-slate-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-200 rounded outline-none transition">
                                            </td>
                                            <td class="p-2 border-l border-slate-200">
                                                <input type="text" name="matrix[T][<?= $i ?>]" placeholder="<?= $t_placeholders[$i] ?>" class="w-full p-2 bg-transparent border border-transparent hover:border-slate-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-200 rounded outline-none transition">
                                            </td>
                                        </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <div class="p-6 lg:w-1/4 bg-slate-50 flex flex-col">
                        <h2 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4">2. Assign Task</h2>

                        <div class="mb-5">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Time Period *</label>
                            <select name="period_id" required class="w-full p-2.5 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm cursor-pointer">
                                <option value="" disabled selected>Choose Semester...</option>
                                <?php while ($p = $periods_query->fetch_assoc()): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['month'] . ' ' . $p['year']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="flex-1 flex flex-col min-h-0">
                            <div class="flex justify-between items-end mb-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Employees</label>
                                <button type="button" id="selectAllBtn" class="text-xs text-blue-600 hover:text-blue-800 font-semibold transition">Select All</button>
                            </div>
                            
                            <div class="flex-1 overflow-y-auto bg-white border border-slate-300 rounded-lg p-3 shadow-inner space-y-2 max-h-64">
                                <?php while ($u = $users_query->fetch_assoc()): 
                                    $roleText = ($u['role'] == 0) ? "Superadmin" : (($u['role'] == 1) ? "Admin" : (($u['role'] == 2) ? "Moderator" : "User"));
                                    $roleColor = ($u['role'] == 0 || $u['role'] == 1) ? "text-purple-600" : "text-slate-400";
                                ?>
                                <label class="flex items-start p-2 hover:bg-slate-50 rounded cursor-pointer transition">
                                    <input type="checkbox" name="assigned_users[]" value="<?= $u['id'] ?>" class="user-checkbox mt-1 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-slate-700 leading-none"><?= htmlspecialchars($u['full_name']) ?></span>
                                        <span class="block text-[10px] font-semibold uppercase tracking-wider mt-1 <?= $roleColor ?>"><?= $roleText ?></span>
                                    </div>
                                </label>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-200">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                Save & Assign
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <?php require '../includes/ipcr_modals.php'; ?>

    <script>
        document.getElementById('selectAllBtn').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
            this.textContent = allChecked ? "Select All" : "Deselect All";
        });
    </script>
</body>
</html>