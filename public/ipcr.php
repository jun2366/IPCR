<?php
require '../config/database.php';
require '../includes/session.php';

$user_id   = $_SESSION['user_id'];
$period_id = $_SESSION['period_id'];

// 1. Fetch User Info
$u = $conn->prepare("SELECT full_name, position, division FROM users WHERE id=?");
$u->bind_param("i", $user_id);
$u->execute();
$user = $u->get_result()->fetch_assoc();

// 2. Fetch Period Info
$p = $conn->prepare("SELECT month, year FROM login_periods WHERE id=?");
$p->bind_param("i", $period_id);
$p->execute();
$period = $p->get_result()->fetch_assoc();

$period_display = $period['month'] . ' ' . $period['year']; 

// 3. Fetch Tasks
$sql = "
SELECT 
    t.id AS task_id,
    t.task_code, 
    t.task_title, 
    t.success_indicator
FROM user_tasks ut
JOIN tasks t ON t.id = ut.task_id
WHERE ut.user_id = ? 
ORDER BY 
  CAST(SUBSTRING_INDEX(t.task_code,'.',1) AS UNSIGNED), 
  CAST(SUBSTRING_INDEX(t.task_code,'.',-1) AS UNSIGNED)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tasks = $stmt->get_result();

// 4. Fetch Matrix Data
$ratingMatrix = [];
$matrixRes = $conn->query("SELECT success_indicator, category, input_value, rating FROM rating_matrix");
if ($matrixRes) {
    while ($row = $matrixRes->fetch_assoc()) {
        $si   = trim((string)$row['success_indicator']);
        $cat  = trim((string)$row['category']);
        $rate = (int)$row['rating'];
        $val  = trim((string)$row['input_value']);
        if ($si !== '' && $cat !== '' && $rate > 0) {
            $ratingMatrix[$si][$cat][$rate] = $val;
        }
    }
}
$ratingMatrixJson = json_encode($ratingMatrix, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPCR Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        
        /* Custom Scrollbar for the Smart Area */
        .smart-area {
            min-height: 100px;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            outline: none;
        }
        .smart-area u {
            text-decoration: none;
            border-bottom: 2px solid #3b82f6;
            background-color: #eff6ff;
            color: #1e3a8a;
            font-weight: 600;
            padding: 0 2px;
            border-radius: 2px;
        }
        
        /* Hide number input arrows */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col shadow-xl z-20">
        <div class="h-16 flex items-center px-6 border-b border-slate-800">
            <div class="font-bold text-xl tracking-wider text-blue-400">DPWH<span class="text-white">IPCR</span></div>
        </div>
        
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="px-3 space-y-1">
                <a href="#" class="group flex items-center px-3 py-2 text-sm font-medium bg-blue-600 rounded-md text-white">
                    <svg class="mr-3 h-5 w-5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    My IPCR
                </a>
                <a href="#" class="group flex items-center px-3 py-2 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white rounded-md transition">
                    <svg class="mr-3 h-5 w-5 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Tasks
                </a>
                <a href="#" class="group flex items-center px-3 py-2 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white rounded-md transition">
                    <svg class="mr-3 h-5 w-5 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" /></svg>
                    Performance Analytics
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center">
                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-sm font-bold">
                    <?= substr($user['full_name'], 0, 1) ?>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white"><?= explode(' ', $user['full_name'])[0] ?></p>
                    <a href="logout.php" class="text-xs text-slate-400 hover:text-white">Sign out</a>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10">
            <h1 class="text-xl font-bold text-slate-800">Performance Review <span class="text-slate-400 text-sm font-normal ml-2">Period: <?= $period_display ?></span></h1>
            
            <div class="flex items-center space-x-6">
                <div class="text-right">
                    <p class="text-xs text-slate-500 uppercase tracking-wide font-semibold">Final Rating</p>
                    <p class="text-2xl font-bold text-blue-600 leading-none" id="header-grand-avg">0.00</p>
                </div>
                <div class="h-10 w-px bg-slate-200"></div>
                <div class="text-right">
                    <p class="text-xs text-slate-500 uppercase tracking-wide font-semibold">Adjectival</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800" id="header-adjectival">
                        ---
                    </span>
                </div>
                <button type="submit" form="ipcr-form" class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    Save Changes
                </button>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto bg-slate-50 p-6">
            <div class="max-w-6xl mx-auto">
                
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6 border-l-4 border-blue-500">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Commitment Statement</h3>
                            <div class="mt-2 text-sm text-blue-700 leading-relaxed">
                                I, <span class="font-bold border-b border-blue-300"><?= htmlspecialchars($user['full_name']) ?></span>, 
                                <span class="font-bold border-b border-blue-300"><?= htmlspecialchars($user['position']) ?></span> of 
                                <span class="font-bold border-b border-blue-300"><?= htmlspecialchars($user['division']) ?></span>, 
                                commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measures.
                            </div>
                        </div>
                    </div>
                </div>

                <form id="ipcr-form" method="POST" action="save_ipcr.php">
                    <div class="space-y-6">
                        
                        <?php while ($t = $tasks->fetch_assoc()): 
                            $siCode = trim($t['task_code']);
                        ?>
                        
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow duration-200 task-row" id="row-<?= $t['task_id'] ?>">
                            <div class="grid grid-cols-12 divide-x divide-slate-100">
                                
                                <div class="col-span-12 lg:col-span-4 p-5 bg-slate-50/50">
                                    <div class="flex items-center mb-3">
                                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                                            <?= htmlspecialchars($t['task_code']) ?>
                                        </span>
                                    </div>
                                    <h4 class="text-sm font-semibold text-slate-900 mb-2 leading-snug">
                                        <?= nl2br(htmlspecialchars($t['task_title'])) ?>
                                    </h4>
                                    <div class="text-xs text-slate-500 italic mt-3 border-t border-slate-200 pt-3">
                                        <span class="font-bold text-slate-600 block mb-1">Success Indicator:</span>
                                        <?= nl2br(htmlspecialchars($t['success_indicator'])) ?>
                                    </div>
                                </div>

                                <div class="col-span-12 lg:col-span-6 p-5 relative smart-cell"
                                     data-task-id="<?= $t['task_id'] ?>"
                                     data-si-code="<?= htmlspecialchars($siCode) ?>"
                                     data-success-indicator="<?= htmlspecialchars($t['success_indicator']) ?>">
                                    
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">
                                        Actual Accomplishment
                                    </label>
                                    
                                    <div class="smart-area w-full p-3 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-inner" 
                                         contenteditable="true" 
                                         id="div-<?= $t['task_id'] ?>"
                                         placeholder="Accomplishments will auto-generate here based on ratings..."></div>
                                         
                                    <input type="hidden" name="narrative[<?= $t['task_id'] ?>]" id="input-<?= $t['task_id'] ?>">
                                    
                                    <p class="text-[10px] text-slate-400 mt-2 text-right">
                                        Auto-generated based on QET ratings
                                    </p>
                                </div>

                                <div class="col-span-12 lg:col-span-2 bg-slate-50 p-4 flex flex-col justify-center items-center space-y-3">
                                    
                                    <div class="w-full space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-500 w-4">Q</span>
                                            <input type="number" name="q[<?= $t['task_id'] ?>]" step="1" min="1" max="5" 
                                                   class="rating-input w-12 h-8 text-center text-sm font-bold text-slate-700 bg-white border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-500 w-4">E</span>
                                            <input type="number" name="e[<?= $t['task_id'] ?>]" step="1" min="1" max="5" 
                                                   class="rating-input w-12 h-8 text-center text-sm font-bold text-slate-700 bg-white border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-500 w-4">T</span>
                                            <input type="number" name="t[<?= $t['task_id'] ?>]" step="1" min="1" max="5" 
                                                   class="rating-input w-12 h-8 text-center text-sm font-bold text-slate-700 bg-white border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                                        </div>
                                    </div>

                                    <div class="w-full pt-3 border-t border-slate-200 mt-2 text-center">
                                        <span class="text-[10px] text-slate-400 uppercase font-bold block">AVG</span>
                                        <span class="text-xl font-black text-slate-800 avg-cell" id="avg-<?= $t['task_id'] ?>">0.00</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <?php endwhile; ?>

                    </div>
                    
                    <div class="mt-8 flex justify-end space-x-4 mb-12">
                    <a href="print_ipcr.php" target="_blank" class="inline-flex items-center px-4 py-2 border border-slate-300 shadow-sm text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none transition">
    <svg class="mr-2 -ml-1 h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2-2h-2m2-4h6a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2zm9-12h2m-6 0h-2" />
    </svg>
    Print IPCR Form
</a>
                        <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-slate-900 hover:bg-slate-800 focus:outline-none transition">
                            Save Final Review
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const ratingMatrix = <?= $ratingMatrixJson ?: '{}' ?>;

        // --- RegEx Helpers ---
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function getVariations(text) {
            if (!text) return [];
            const variations = [text.trim()]; 
            const numMap = { '1':'one (1)', '2':'two (2)', '3':'three (3)', '4':'four (4)', '5':'five (5)', '6':'six (6)', '7':'seven (7)', '8':'eight (8)', '9':'nine (9)', '10':'ten (10)' };
            
            const match = text.match(/^(\d+)\s+(.*)/);
            if (match) {
                const number = match[1];
                const rest = match[2];
                if (numMap[number]) variations.push(`${numMap[number]} ${rest}`); 
            }
            return variations;
        }

        // --- Smart Replace ---
        function smartReplace(sentence, siCode, qVal, eVal, tVal) {
            if (!ratingMatrix[siCode]) return sentence;
            let newHtml = sentence;

            const replaceCategory = (category, userRating, standardRatingToFind) => {
                const r = Math.round(userRating);
                if (!r || !ratingMatrix[siCode][category]) return;

                const actualText = ratingMatrix[siCode][category][r];
                const standardText = ratingMatrix[siCode][category][standardRatingToFind];

                if (!actualText || !standardText) return;

                const searchPhrases = getVariations(standardText);
                let found = false;

                for (const phrase of searchPhrases) {
                    if (!phrase) continue;
                    const regex = new RegExp(escapeRegExp(phrase), 'i');
                    if (regex.test(newHtml)) {
                        const replacement = `<u>${actualText}</u>`;
                        newHtml = newHtml.replace(regex, replacement);
                        found = true;
                        break; 
                    }
                }
                if (!found && category === 'E' && standardText.includes('100%') && newHtml.includes('100%')) {
                     const replacement = `<u>${actualText}</u>`;
                     newHtml = newHtml.replace('100%', replacement);
                }
            };

            replaceCategory('T', tVal, 3);
            replaceCategory('E', eVal, 5);
            replaceCategory('Q', qVal, 3);
            return newHtml;
        }

        // --- Calculations ---
        function updateGrandTotal() {
            let totalSum = 0;
            let totalCount = 0;

            document.querySelectorAll('.avg-cell').forEach(cell => {
                const val = parseFloat(cell.textContent);
                if (val > 0) {
                    totalSum += val;
                    totalCount++;
                }
            });

            const grandAvg = totalCount > 0 ? (totalSum / totalCount).toFixed(2) : "0.00";
            
            // Update Header Score
            document.getElementById('header-grand-avg').textContent = grandAvg;

            // Determine Adjectival Rating
            const avgNum = parseFloat(grandAvg);
            let adjectival = "---";
            let badgeClass = "bg-gray-100 text-gray-800";
            
            if (avgNum >= 4.51) { adjectival = "Outstanding"; badgeClass = "bg-green-100 text-green-800"; }
            else if (avgNum >= 3.51) { adjectival = "Very Satisfactory"; badgeClass = "bg-blue-100 text-blue-800"; }
            else if (avgNum >= 2.51) { adjectival = "Satisfactory"; badgeClass = "bg-yellow-100 text-yellow-800"; }
            else if (avgNum >= 1.51) { adjectival = "Unsatisfactory"; badgeClass = "bg-orange-100 text-orange-800"; }
            else if (avgNum > 0) { adjectival = "Poor"; badgeClass = "bg-red-100 text-red-800"; }
            
            const badge = document.getElementById('header-adjectival');
            badge.textContent = adjectival;
            badge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}`;
        }

        // --- Event Listeners ---
        document.querySelectorAll('.rating-input').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('.task-row'); // Updated selector for new card layout
                const taskId = row.querySelector('.smart-cell').dataset.taskId;
                const siCode = row.querySelector('.smart-cell').dataset.siCode;
                const successIndicator = row.querySelector('.smart-cell').dataset.successIndicator;
                
                const divArea = document.getElementById('div-' + taskId);
                const hiddenInput = document.getElementById('input-' + taskId);
                const avgCell = document.getElementById('avg-' + taskId);

                const qInput = row.querySelector(`input[name="q[${taskId}]"]`);
                const eInput = row.querySelector(`input[name="e[${taskId}]"]`);
                const tInput = row.querySelector(`input[name="t[${taskId}]"]`);

                const q = qInput.value ? parseFloat(qInput.value) : 0;
                const e = eInput.value ? parseFloat(eInput.value) : 0;
                const t = tInput.value ? parseFloat(tInput.value) : 0;

                // Row Average
                let sum = 0, count = 0;
                if (q) { sum += q; count++; }
                if (e) { sum += e; count++; }
                if (t) { sum += t; count++; }
                const avg = count > 0 ? (sum / count).toFixed(2) : "0.00";
                avgCell.textContent = avg;

                // Colorize Average
                if (avg >= 4.5) avgCell.className = "text-xl font-black text-green-600 avg-cell";
                else if (avg > 0 && avg < 3) avgCell.className = "text-xl font-black text-red-600 avg-cell";
                else avgCell.className = "text-xl font-black text-slate-800 avg-cell";

                // Smart Text Generation
                if (q || e || t) {
                    const generatedHtml = smartReplace(successIndicator, siCode, q, e, t);
                    divArea.innerHTML = generatedHtml;
                    hiddenInput.value = generatedHtml; 
                } else {
                    divArea.innerHTML = "";
                    hiddenInput.value = "";
                }

                updateGrandTotal();
            });
        });
        
        // Sync manual edits
        document.querySelectorAll('.smart-area').forEach(div => {
            div.addEventListener('input', function() {
                const id = this.id.replace('div-', '');
                document.getElementById('input-' + id).value = this.innerHTML;
            });
        });
    });
    </script>
</body>
</html>