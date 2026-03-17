<?php
// Get the name of the current file
$current_page = basename($_SERVER['PHP_SELF']);

// Define our CSS classes for active vs inactive states
$active_class = "bg-blue-600 text-white";
$inactive_class = "text-slate-300 hover:bg-slate-800 hover:text-white transition";

$active_icon = "text-blue-200";
$inactive_icon = "text-slate-400 group-hover:text-white transition";
?>

<style>
    #app-sidebar { transition: width 0.3s ease; }
    .sidebar-text, #sidebar-logo, #sidebar-profile-info, .admin-label { 
        transition: opacity 0.2s ease; 
        white-space: nowrap; 
    }
    
    /* Collapsed State Styles */
    #app-sidebar.collapsed { width: 5rem; /* Equivalent to w-20 */ }
    #app-sidebar.collapsed .sidebar-text,
    #app-sidebar.collapsed #sidebar-logo,
    #app-sidebar.collapsed #sidebar-profile-info,
    #app-sidebar.collapsed .admin-label,
    #app-sidebar.collapsed .logout-text { 
        display: none; 
    }
    
    #app-sidebar.collapsed .nav-link { justify-content: center; padding-left: 0; padding-right: 0; }
    #app-sidebar.collapsed .nav-icon { margin-right: 0; }
    #app-sidebar.collapsed .header-container { justify-content: center; padding: 0; }
    #app-sidebar.collapsed .profile-container { justify-content: center; padding-left: 0; padding-right: 0; flex-direction: column; gap: 0.5rem; }
    #app-sidebar.collapsed .logout-btn { margin-left: 0; padding: 0.5rem; }
</style>

<aside id="app-sidebar" class="w-64 bg-slate-900 text-white hidden md:flex flex-col shadow-xl z-20 flex-shrink-0">
    
    <script>
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            const sidebarElement = document.getElementById('app-sidebar');
            sidebarElement.classList.add('collapsed');
            sidebarElement.style.transition = 'none'; // Stop the slide animation on initial load
            
            // Restore the CSS transition after the page is done loading
            setTimeout(() => {
                sidebarElement.style.transition = '';
            }, 100);
        }
    </script>

    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800 header-container">
        <div id="sidebar-logo" class="font-bold text-xl tracking-wider text-blue-400">DPWH<span class="text-white">IPCR</span></div>
        <button id="toggle-sidebar-btn" class="text-slate-400 hover:text-white focus:outline-none transition p-1 rounded-md hover:bg-slate-800" title="Toggle Sidebar">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
    
    <div class="flex-1 overflow-y-auto py-4 overflow-x-hidden">
        <nav class="px-3 space-y-2">
            
            <a href="ipcr.php" title="My IPCR" class="nav-link group flex items-center px-3 py-2 text-sm font-medium rounded-md <?= ($current_page == 'ipcr.php') ? $active_class : $inactive_class ?>">
                <svg class="nav-icon mr-3 h-5 w-5 flex-shrink-0 <?= ($current_page == 'ipcr.php') ? $active_icon : $inactive_icon ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span class="sidebar-text">My IPCR</span>
            </a>
            
            <a href="history.php" title="History / Copies" class="nav-link group flex items-center px-3 py-2 text-sm font-medium rounded-md <?= ($current_page == 'history.php') ? $active_class : $inactive_class ?>">
                <svg class="nav-icon mr-3 h-5 w-5 flex-shrink-0 <?= ($current_page == 'history.php') ? $active_icon : $inactive_icon ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="sidebar-text">History / Copies</span>
            </a>

            <?php if(isset($is_superadmin) && $is_superadmin): ?>
            <div class="mt-6 pt-6 border-t border-slate-800">
                <p class="admin-label px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Admin Controls</p>
                
                <a href="create_task.php" title="Create New Task" class="nav-link group flex items-center px-3 py-2 text-sm font-medium rounded-md <?= ($current_page == 'create_task.php') ? $active_class : $inactive_class ?>">
                    <svg class="nav-icon mr-3 h-5 w-5 flex-shrink-0 <?= ($current_page == 'create_task.php') ? $active_icon : $inactive_icon ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span class="sidebar-text">Create New Task</span>
                </a>
            </div>
            <?php endif; ?>
        </nav>
    </div>

    <div class="p-4 border-t border-slate-800 profile-container flex items-center transition-all duration-300">
        <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-sm font-bold flex-shrink-0" title="<?= htmlspecialchars($user['full_name'] ?? 'User') ?>">
            <?= substr($user['full_name'] ?? 'U', 0, 1) ?>
        </div>
        <div id="sidebar-profile-info" class="ml-3 flex-1 overflow-hidden">
            <p class="text-sm font-medium text-white truncate"><?= explode(' ', $user['full_name'] ?? 'User')[0] ?></p>
             <p class="text-xs text-slate-400 truncate">
                <?php 
                if(isset($is_superadmin) && $is_superadmin) echo "Superadmin"; 
                elseif(isset($role) && $role===1) echo "Admin"; 
                elseif(isset($role) && $role===2) echo "Moderator"; 
                else echo "Employee"; 
                ?>
            </p>
        </div>
        <a href="logout.php" class="logout-btn ml-auto text-slate-400 hover:text-white p-2 rounded-md hover:bg-slate-800 transition flex items-center" title="Sign out">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span class="logout-text text-xs ml-1">Sign out</span>
        </a>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('app-sidebar');
        const toggleBtn = document.getElementById('toggle-sidebar-btn');

        // Toggle logic
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            
            // Save the user's preference to their browser
            if (sidebar.classList.contains('collapsed')) {
                localStorage.setItem('sidebar-collapsed', 'true');
            } else {
                localStorage.setItem('sidebar-collapsed', 'false');
            }
        });
    });
</script>