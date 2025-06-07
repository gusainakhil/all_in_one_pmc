<!-- Sidebar for desktop -->
<style>
    body {
        /* font-weight:400; */
        font-size: 16px;
        font-variant: small-caps;
        /* font-family: "Times New Roman", Times, serif; */
    }
</style>
<aside class="w-64 bg-white shadow-lg hidden md:block">
    <!-- <div class="p-4 text-lg font-semibold border-b">Dashboard</div> -->
    <center><img src="https://pa.beatlebuddy.com/assets/railway_logo.jpg" alt="Logo" class="w-40 h-30 mr-2 center-block" style="padding: 8px;"></center>
    <hr>
    <nav class="p-4 space-y-2">
        <!-- logo -->
        <div class="flex items-center mb-4">
            <img src="https://pa.beatlebuddy.com/assets/railway_logo.jpg" alt="Logo" class="w-10 h-10 mr-2">
            <span class="text-xl font-bold">Beatle Analytics</span>
        </div>
        <a href="dashboard.php" class="flex items-center gap-2 text-gray-700 hover:text-blue-600"><i class="fas fa-home w-5"></i> Dashboard</a>
        <!-- User Dropdown (Desktop) -->
           <div class="relative group">
            <button type="button" class="flex items-center gap-2 text-gray-700 hover:text-blue-600 focus:outline-none">
                <i class="fas fa-user w-5"></i> create Division
                <i class="fas fa-chevron-down ml-1 text-xs"></i>
            </button>
            <div class="absolute left-0 mt-1 w-40 bg-white border rounded shadow-lg hidden group-hover:block group-focus-within:block z-10">
                <a href="create-division.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Create Division </a>
                <a href="list-division.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">List</a>
            </div>
        </div>
             <div class="relative group">
            <button type="button" class="flex items-center gap-2 text-gray-700 hover:text-blue-600 focus:outline-none">
                <i class="fas fa-user w-5"></i> create station
                <i class="fas fa-chevron-down ml-1 text-xs"></i>
            </button>
            <div class="absolute left-0 mt-1 w-40 bg-white border rounded shadow-lg hidden group-hover:block group-focus-within:block z-10">
                <a href="create-station.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Create station</a>
                <a href="list-station.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">List</a>
            </div>
        </div>

         <div class="relative group">
            <button type="button" class="flex items-center gap-2 text-gray-700 hover:text-blue-600 focus:outline-none">
                <i class="fas fa-user w-5"></i> create organization
                <i class="fas fa-chevron-down ml-1 text-xs"></i>
            </button>
            <div class="absolute left-0 mt-1 w-40 bg-white border rounded shadow-lg hidden group-hover:block group-focus-within:block z-10">
                <a href="create-organization.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Create organization</a>
                <a href="list-organisation.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">List</a>
            </div>
        </div>
         
         
        <div class="relative group">
            <button type="button" class="flex items-center gap-2 text-gray-700 hover:text-blue-600 focus:outline-none">
                <i class="fas fa-user w-5"></i> User
                <i class="fas fa-chevron-down ml-1 text-xs"></i>
            </button>
            <div class="absolute left-0 mt-1 w-40 bg-white border rounded shadow-lg hidden group-hover:block group-focus-within:block z-10">
                <a href="user-list.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Show Users</a>
                <a href="create-user.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Create User</a>
            </div>
        </div>
        <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-blue-600"><i class="fas fa-layer-group w-5"></i> Type</a>
        
    </nav>
</aside>

<!-- Sidebar for mobile -->
<div class="md:hidden">
    <button id="mobile-menu-btn" class="p-4 focus:outline-none">
        <i class="fas fa-bars text-2xl"></i>
    </button>
    <div id="mobile-sidebar" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden">
        <aside class="w-64 bg-white h-full shadow-lg p-4">
            <div class="flex justify-between items-center border-b pb-4 mb-4">
                <span class="text-lg font-semibold">Dashboard</span>
                <button id="close-mobile-sidebar" class="text-gray-600 text-2xl focus:outline-none">&times;</button>
            </div>
            <nav class="space-y-2">
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-blue-600"><i class="fas fa-home w-5"></i> Dashboard</a>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-blue-600"><i class="fas fa-user w-5"></i> User</a>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-blue-600"><i class="fas fa-layer-group w-5"></i> Type</a>
            </nav>
        </aside>
    </div>
</div>

<script>
    const menuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('mobile-sidebar');
    const closeBtn = document.getElementById('close-mobile-sidebar');

    menuBtn.addEventListener('click', () => {
        sidebar.classList.remove('hidden');
    });

    closeBtn.addEventListener('click', () => {
        sidebar.classList.add('hidden');
    });

    // Optional: close sidebar when clicking outside
    sidebar.addEventListener('click', (e) => {
        if (e.target === sidebar) {
            sidebar.classList.add('hidden');
        }
    });
</script>