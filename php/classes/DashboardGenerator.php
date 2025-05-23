<?php
require_once __DIR__ . '/Database.php';

class DashboardGenerator {
    private $conn;
    private $tables;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
        $this->tables = $this->getTables();
    }

    // Get all tables from the database
    private function getTables() {
        try {
            $stmt = $this->conn->query("SHOW TABLES");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error fetching tables: " . $e->getMessage());
            return [];
        }
    }

    // Generate the main dashboard content
    public function generateDashboard() {
        $html = $this->generateHeader();
        $html .= $this->generateStatCards();
        $html .= $this->generateTableList();
        return $html;
    }

    // Generate dashboard header with user info and navigation
    private function generateHeader() {
        $username = htmlspecialchars($_SESSION['username'] ?? 'Guest');
        $userLevel = htmlspecialchars($_SESSION['user_level'] ?? 'guest');

        return <<<HTML
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-700 mr-4">Welcome, {$username}</span>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                            {$userLevel}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    // Generate statistical cards for each table
    private function generateStatCards() {
        $html = '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                 <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">';

        foreach ($this->tables as $table) {
            $count = $this->getTableCount($table);
            $lastUpdate = $this->getLastUpdateTime($table);
            
            $html .= <<<HTML
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    {$table}
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        {$count}
                                    </div>
                                    <div class="ml-2 flex items-baseline text-sm font-semibold text-gray-600">
                                        records
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="crud.php?action=list&table={$table}" class="font-medium text-blue-600 hover:text-blue-900">
                            View all
                        </a>
                    </div>
                </div>
            </div>
            HTML;
        }

        $html .= '</div></div>';
        return $html;
    }

    // Generate quick access table list
    private function generateTableList() {
        $html = '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                 <div class="bg-white shadow overflow-hidden sm:rounded-md">
                 <ul class="divide-y divide-gray-200">';

        foreach ($this->tables as $table) {
            $html .= <<<HTML
            <li>
                <div class="px-4 py-4 flex items-center sm:px-6">
                    <div class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <div class="flex text-sm">
                                <p class="font-medium text-blue-600 truncate">{$table}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex-shrink-0 sm:mt-0">
                            <div class="flex space-x-4">
                                <a href="crud.php?action=create&table={$table}" 
                                   class="font-medium text-green-600 hover:text-green-900">Add New</a>
                                <a href="crud.php?action=list&table={$table}" 
                                   class="font-medium text-blue-600 hover:text-blue-900">View All</a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            HTML;
        }

        $html .= '</ul></div></div>';
        return $html;
    }

    // Get record count for a table
    private function getTableCount($table) {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM `$table`");
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error counting records: " . $e->getMessage());
            return 0;
        }
    }

    // Get last update time for a table
    private function getLastUpdateTime($table) {
        try {
            // This assumes your tables have an updated_at or similar timestamp column
            $stmt = $this->conn->query("SHOW COLUMNS FROM `$table` LIKE 'updated_at'");
            if ($stmt->rowCount() > 0) {
                $stmt = $this->conn->query("SELECT updated_at FROM `$table` ORDER BY updated_at DESC LIMIT 1");
                $result = $stmt->fetch();
                return $result['updated_at'] ?? 'Never';
            }
            return 'N/A';
        } catch (PDOException $e) {
            error_log("Error getting last update time: " . $e->getMessage());
            return 'N/A';
        }
    }

    // Generate system statistics
    public function generateSystemStats() {
        $stats = [
            'Total Tables' => count($this->tables),
            'Database Size' => $this->getDatabaseSize(),
            'PHP Version' => phpversion(),
            'Server Time' => date('Y-m-d H:i:s')
        ];

        $html = '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                 <h2 class="text-lg font-medium text-gray-900 mb-4">System Statistics</h2>
                 <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                 <div class="px-4 py-5 sm:p-6">
                 <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">';

        foreach ($stats as $label => $value) {
            $html .= <<<HTML
            <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">
                    {$label}
                </dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">
                    {$value}
                </dd>
            </div>
            HTML;
        }

        $html .= '</dl></div></div></div>';
        return $html;
    }

    // Get database size
    private function getDatabaseSize() {
        try {
            $stmt = $this->conn->query("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            $result = $stmt->fetch();
            return $result['size'] . ' MB';
        } catch (PDOException $e) {
            error_log("Error getting database size: " . $e->getMessage());
            return 'N/A';
        }
    }
}
