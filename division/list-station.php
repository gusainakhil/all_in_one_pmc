<?php
$division_id = 16;
session_start();

include "connection.php";

// Get user type filter (default to 'owner')
$userType = isset($_GET['type']) ? $_GET['type'] : 'owner';
$validUserTypes = ['owner', 'auditor', 'all'];
if (!in_array($userType, $validUserTypes)) $userType = 'owner';

// Build the query to fetch stations with user information for dashboard access
$sql = "SELECT DISTINCT s.stationId, s.stationName, u.login_token, u.db_userLoginName, u.db_username,
        o.db_Orgname as organization_name,
        d.DivisionName as division_name
        FROM baris_station s
        LEFT JOIN baris_userlogin u ON s.stationId = u.StationId AND u.db_usertype = 'owner'
        LEFT JOIN baris_organization o ON u.OrgID = o.OrgID
        LEFT JOIN baris_division d ON s.DivisionId = d.DivisionId
        WHERE s.DivisionId = $division_id";

$sql .= " ORDER BY s.stationId ASC";
$result = $conn->query($sql);

// Count total stations and users by type
$countStations = $conn->query("SELECT COUNT(*) as count FROM baris_station WHERE DivisionId = $division_id")->fetch_assoc()['count'];
$countOwners = $conn->query("SELECT COUNT(*) as count FROM baris_userlogin WHERE db_usertype = 'owner' AND DivisionId = $division_id")->fetch_assoc()['count'];
$countAuditors = $conn->query("SELECT COUNT(*) as count FROM baris_userlogin WHERE db_usertype = 'auditor' AND DivisionId = $division_id")->fetch_assoc()['count'];
$countAllUsers = $countOwners + $countAuditors;

// Get search term if provided
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Division Management | Station Cleaning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            text-transform: capitalize;

        }
        
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background-color: white;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        th {
            background-color: #f9fafb;
            font-weight: 500;
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            color: #1f2937;
            font-size: 0.875rem;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background-color: #f9fafb;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        .btn-login {
            background-color: #e0e7ff;
            color: #4f46e5;
        }
        
        .btn-login:hover {
            background-color: #c7d2fe;
        }
        
        .btn-edit {
            background-color: #d1fae5;
            color: #059669;
        }
        
        .btn-edit:hover {
            background-color: #a7f3d0;
        }
        
        .btn-delete {
            background-color: #fee2e2;
            color: #ef4444;
        }
        
        .btn-delete:hover {
            background-color: #fecaca;
        }
        
        .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: opacity 0.2s, transform 0.2s, visibility 0.2s;
        }
        
        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .active-badge {
            background-color: #d1fae5;
            color: #059669;
        }
        
        .expired-badge {
            background-color: #fee2e2;
            color: #ef4444;
        }
        
        .tab-button {
            position: relative;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .tab-button.active {
            color: #3b82f6;
        }
        
        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #3b82f6;
        }
        
        .tab-button:hover {
            color: #3b82f6;
        }
        
        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>
<body>

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="ml-0 md:ml-72 flex-1">
        <!-- Top Navigation -->
        <header class="bg-white shadow-sm sticky top-0 z-10">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center">
                    <button id="mobile-menu-button" class="mr-4 text-gray-600 md:hidden">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="text-xl font-bold text-gray-800">Division Management</h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- <a href="create-user.php" class="btn btn-primary">
                        <i class="fas fa-user-plus mr-2"></i>
                        Add New User
                    </a> -->
                    
                    <!-- User Menu -->
                    <div class="relative dropdown">
                        <button class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                                <span class="font-bold text-white">A</span>
                            </div>
                            <span class="hidden md:inline-block font-medium text-sm">Admin</span>
                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg overflow-hidden z-20">
                            <a href="#" class="block p-3 hover:bg-gray-50">
                                <div class="flex items-center">
                                    <i class="fas fa-user-circle w-5 mr-2 text-gray-500"></i>
                                    <span>Profile</span>
                                </div>
                            </a>
                            <a href="#" class="block p-3 hover:bg-gray-50">
                                <div class="flex items-center">
                                    <i class="fas fa-cog w-5 mr-2 text-gray-500"></i>
                                    <span>Settings</span>
                                </div>
                            </a>
                            <a href="#" class="block p-3 hover:bg-gray-50 border-t border-gray-200">
                                <div class="flex items-center">
                                    <i class="fas fa-sign-out-alt w-5 mr-2 text-gray-500"></i>
                                    <span>Logout</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <div class="p-6">
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="dashboard.php" class="text-gray-500 hover:text-blue-600">
                                <i class="fas fa-home mr-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                                <span class="text-gray-700">Division Management</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            
            <!-- Overview Section -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Station Overview</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="card p-5">
                        <div class="flex items-center">
                            <div class="rounded-full bg-blue-100 p-3 mr-4">
                                <i class="fas fa-building text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Total Stations</p>
                                <h3 class="text-2xl font-bold"><?php echo $countStations; ?></h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card p-5">
                        <div class="flex items-center">
                            <div class="rounded-full bg-indigo-100 p-3 mr-4">
                                <i class="fas fa-user-tie text-indigo-600"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Station Owners</p>
                                <h3 class="text-2xl font-bold"><?php echo $countOwners; ?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="card p-5">
                        <div class="flex items-center">
                            <div class="rounded-full bg-purple-100 p-3 mr-4">
                                <i class="fas fa-user-check text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Auditors</p>
                                <h3 class="text-2xl font-bold"><?php echo $countAuditors; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Station List -->
            <div class="card mb-6">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Station Performance Dashboard</h3>
                            
                            <!-- Tabs -->
                            <div class="flex border-b">
                                <a href="?type=stations" class="tab-button active">
                                    <i class="fas fa-building mr-2"></i>All Stations
                                </a>
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="relative">
                                <form action="" method="GET" id="searchForm">
                                    <input type="hidden" name="type" value="stations">
                                    <input 
                                        type="text" 
                                        name="search" 
                                        id="searchInput" 
                                        placeholder="Search stations..." 
                                        class="search-input"
                                        value="<?php echo htmlspecialchars($searchTerm); ?>"
                                    >
                                    <button type="submit" class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            </div>
                            
                            <button id="refreshButton" class="btn bg-gray-100 text-gray-700 hover:bg-gray-200">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <div class="flex items-center">
                                        <span>Station Name</span>
                                        <button class="ml-1 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-sort"></i>
                                        </button>
                                    </div>
                                </th>
                                <th class="text-center">Cleanliness Score</th>
                                <th class="text-center">Maintenance Score</th>
                                <th class="text-center">Overall Rating</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result && $result->num_rows > 0) {
                                $counter = 1;
                                while ($row = $result->fetch_assoc()) {
                                    // Skip if searching and no match
                                    if ($searchTerm !== '') {
                                        $searchIn = $row['stationName'] . ' ' . $row['organization_name'];
                                        if (stripos($searchIn, $searchTerm) === false) {
                                            continue;
                                        }
                                    }
                                    
                                    // Generate random performance scores for demonstration
                                    $cleanlinessScore = rand(75, 95);
                                    $maintenanceScore = rand(80, 92);
                                    $overallRating = rand(78, 90);
                                    
                                    // Determine color based on score ranges
                                    function getScoreColor($score) {
                                        if ($score < 80) return 'text-red-600';
                                        if ($score >= 80 && $score <= 85) return 'text-yellow-600';
                                        return 'text-green-600';
                                    }
                                    
                                    echo "<tr class='station-row'>";
                                    echo "<td class='px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium'>" . htmlspecialchars($row['stationName']) . "</td>";
                                    echo "<td class='px-4 md:px-6 py-4 whitespace-nowrap text-sm text-center'>
                                            <span class='" . getScoreColor($cleanlinessScore) . " font-medium'>" . $cleanlinessScore . "%</span>
                                          </td>";
                                    echo "<td class='px-4 md:px-6 py-4 whitespace-nowrap text-sm text-center'>
                                            <span class='" . getScoreColor($maintenanceScore) . " font-medium'>" . $maintenanceScore . "%</span>
                                          </td>";
                                    echo "<td class='px-4 md:px-6 py-4 whitespace-nowrap text-sm text-center'>
                                            <span class='" . getScoreColor($overallRating) . " font-medium'>" . $overallRating . "%</span>
                                          </td>";
                                    echo "<td class='px-4 md:px-6 py-4 whitespace-nowrap text-sm'>
                                            <div class='flex space-x-2'>";
                                    
                                    // Show dashboard link only if station has an owner with login token
                                    if ($row['login_token']) {
                                        echo "<a href='../dashboard/user-dashboard/index.php?token=" . $row['login_token'] . "' target='_blank' class='btn btn-login' title='Open Station Dashboard'>
                                                <i class='fas fa-sign-in-alt'></i>
                                              </a>";
                                    } else {
                                        echo "<span class='btn bg-gray-200 text-gray-500 cursor-not-allowed' title='No owner assigned'>
                                                <i class='fas fa-sign-in-alt'></i>
                                              </span>";
                                    }
                                    
                                    echo "</div>
                                          </td>";
                                    echo "</tr>";
                                    $counter++;
                                }
                                
                                // If search was performed but no results found
                                if ($searchTerm !== '' && $counter === 1) {
                                    echo "<tr><td colspan='5' class='py-4 text-center text-gray-500'>No stations found matching '<strong>" . htmlspecialchars($searchTerm) . "</strong>'. <a href='?type=stations' class='text-blue-500 hover:underline'>Clear search</a></td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='py-4 text-center text-gray-500'>";
                                echo "No stations found in this division.";
                                echo "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="card p-4">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                            <i class="fas fa-user-shield text-blue-600"></i>
                        </div>
                        <h3 class="font-semibold">User Permissions</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Users have different permissions based on their type:
                        <ul class="list-disc pl-5 mt-2 text-sm text-gray-600 space-y-1">
                            <li><span class="font-medium">Owners</span>: Can manage their own organization, divisions, and stations</li>
                            <li><span class="font-medium">Auditors</span>: Can view reports and conduct audits</li>
                        </ul>
                    </p>
                    <a href="#" class="text-sm text-blue-600 font-medium hover:text-blue-800 flex items-center">
                        <span>Learn more about user permissions</span>
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="card p-4">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                            <i class="fas fa-question-circle text-purple-600"></i>
                        </div>
                        <h3 class="font-semibold">Need Help?</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Our support team is here to help you with any questions about managing users
                        or setting up user permissions.
                    </p>
                    <a href="#" class="text-sm text-purple-600 font-medium hover:text-purple-800 flex items-center">
                        <i class="fas fa-headset mr-2"></i>
                        <span>Contact Support</span>
                    </a>
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="mt-8 border-t border-gray-200 pt-6 pb-4">
                <p class="text-sm text-gray-500 text-center">
                    &copy; 2025 BeatleBuddy. All rights reserved.
                </p>
            </footer>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-red-100 rounded-full mx-auto flex items-center justify-center mb-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Confirm Deletion</h3>
            <p class="text-gray-500" id="deleteMessage">Are you sure you want to delete this user?</p>
        </div>
        <div class="flex justify-end space-x-3">
            <button id="cancelDelete" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                Cancel
            </button>
            <a id="confirmDeleteBtn" href="#" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                Yes, Delete
            </a>
        </div>
    </div>
</div>

<script>
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const sidebar = document.querySelector('aside');
    
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
    }
    
    // Delete confirmation modal
    function confirmDelete(userId, userName) {
        const deleteModal = document.getElementById('deleteModal');
        const cancelDelete = document.getElementById('cancelDelete');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteMessage = document.getElementById('deleteMessage');
        
        deleteMessage.textContent = `Are you sure you want to delete the user "${userName}"?`;
        confirmDeleteBtn.href = `delete-user.php?id=${userId}`;
        deleteModal.classList.remove('hidden');
        
        cancelDelete.addEventListener('click', () => {
            deleteModal.classList.add('hidden');
        });
        
        // Close modal when clicking outside
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.add('hidden');
            }
        });
    }
    
    // Refresh button action
    document.getElementById('refreshButton').addEventListener('click', function() {
        window.location.reload();
    });
    
    // Client-side search (in addition to server-side)
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        if (searchTerm.length < 2) return; // Only search when at least 2 characters
        
        const rows = document.querySelectorAll('.station-row');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>

<?php $conn->close(); ?>