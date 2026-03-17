<?php
require '../config/database.php';
require '../includes/session.php';

// Fetch User Info to check roles
$user_id = $_SESSION['user_id'];
$u = $conn->prepare("SELECT full_name, role FROM users WHERE id=?");
$u->bind_param("i", $user_id);
$u->execute();
$user = $u->get_result()->fetch_assoc();
$role = (int)($user['role'] ?? 3);

// Set the variable that the sidebar checks!
$is_superadmin = ($role === 0);

// Query to find ALL periods where this user has data
// We join task_accomplishments with login_periods to get readable dates
$sql = "
SELECT DISTINCT 
    p.id as period_id,
    p.month, 
    p.year,
    MAX(ta.created_at) as last_updated,
    AVG(ta.final_rating) as approximate_rating -- Optional rough estimate
FROM task_accomplishments ta
JOIN login_periods p ON ta.period_id = p.id
WHERE ta.user_id = ?
GROUP BY p.id
ORDER BY p.year DESC, p.month DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result();

// === INCLUDE MODULAR UI COMPONENTS ===
require '../includes/header.php';
require '../includes/sidebar.php';
?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10 border-b border-slate-200">
            <h1 class="text-xl font-bold text-slate-800">Submission History</h1>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-slate-500">Welcome, <?= htmlspecialchars($user['full_name']) ?></span>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="max-w-5xl mx-auto">
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Period</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Last Updated</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            <?php if($history->num_rows > 0): ?>
                                <?php while($row = $history->fetch_assoc()): 
                                    $period_text = strtoupper($row['month'] . ' ' . $row['year']);
                                    $date = date("M d, Y h:i A", strtotime($row['last_updated']));
                                ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-slate-900"><?= $period_text ?></div>
                                                <div class="text-xs text-slate-500">ID: #<?= $row['period_id'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-500"><?= $date ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Submitted
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="print_ipcr.php?period_id=<?= $row['period_id'] ?>" target="_blank" class="text-blue-600 hover:text-blue-900 font-bold hover:underline flex items-center justify-end">
                                            View Copy
                                            <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-500">
                                        No historical IPCRs found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </main>
</body>
</html>