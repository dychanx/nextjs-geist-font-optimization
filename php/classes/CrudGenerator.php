<?php
require_once __DIR__ . '/Database.php';

class CrudGenerator {
    private $conn;
    private $tableName;
    private $primaryKey;

    public function __construct($tableName) {
        $this->conn = Database::getInstance()->getConnection();
        $this->tableName = $tableName;
        $this->setPrimaryKey();
    }

    // Set the primary key for the table
    private function setPrimaryKey() {
        try {
            $stmt = $this->conn->prepare("
                SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = ? AND CONSTRAINT_NAME = 'PRIMARY'
            ");
            $stmt->execute([$this->tableName]);
            $result = $stmt->fetch();
            $this->primaryKey = $result ? $result['COLUMN_NAME'] : 'id';
        } catch (PDOException $e) {
            error_log("Error getting primary key: " . $e->getMessage());
            $this->primaryKey = 'id';
        }
    }

    // Get table columns information
    public function getTableColumns() {
        try {
            $stmt = $this->conn->prepare("
                SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY, EXTRA
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_NAME = ?
            ");
            $stmt->execute([$this->tableName]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting table columns: " . $e->getMessage());
            return [];
        }
    }

    // Generate Create Form
    public function generateCreateForm() {
        $columns = $this->getTableColumns();
        $html = "<form action='crud.php?action=create&table={$this->tableName}' method='POST' class='max-w-lg mx-auto p-6 bg-white rounded-lg shadow-md'>";
        
        foreach ($columns as $column) {
            // Skip auto-increment columns
            if ($column['EXTRA'] === 'auto_increment') {
                continue;
            }

            $columnName = $column['COLUMN_NAME'];
            $isRequired = $column['IS_NULLABLE'] === 'NO' ? 'required' : '';
            $type = $this->getInputType($column['DATA_TYPE']);
            
            $html .= "
            <div class='mb-4'>
                <label class='block text-gray-700 text-sm font-bold mb-2' for='{$columnName}'>
                    " . ucfirst($columnName) . "
                </label>";

            if ($type === 'textarea') {
                $html .= "
                <textarea 
                    name='{$columnName}' 
                    id='{$columnName}'
                    class='shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline'
                    {$isRequired}
                ></textarea>";
            } else {
                $html .= "
                <input 
                    type='{$type}' 
                    name='{$columnName}' 
                    id='{$columnName}'
                    class='shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline'
                    {$isRequired}
                >";
            }

            $html .= "</div>";
        }

        $html .= "
            <div class='flex items-center justify-between'>
                <button type='submit' class='bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline'>
                    Create
                </button>
                <a href='crud.php?action=list&table={$this->tableName}' class='text-blue-500 hover:text-blue-800'>
                    Back to List
                </a>
            </div>
        </form>";

        return $html;
    }

    // Generate Edit Form
    public function generateEditForm($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch();

            if (!$record) {
                return "<div class='text-red-500'>Record not found.</div>";
            }

            $columns = $this->getTableColumns();
            $html = "<form action='crud.php?action=update&table={$this->tableName}&id={$id}' method='POST' class='max-w-lg mx-auto p-6 bg-white rounded-lg shadow-md'>";

            foreach ($columns as $column) {
                $columnName = $column['COLUMN_NAME'];
                $value = htmlspecialchars($record[$columnName]);
                $isRequired = $column['IS_NULLABLE'] === 'NO' ? 'required' : '';
                $isReadOnly = $column['EXTRA'] === 'auto_increment' ? 'readonly' : '';
                $type = $this->getInputType($column['DATA_TYPE']);

                $html .= "
                <div class='mb-4'>
                    <label class='block text-gray-700 text-sm font-bold mb-2' for='{$columnName}'>
                        " . ucfirst($columnName) . "
                    </label>";

                if ($type === 'textarea') {
                    $html .= "
                    <textarea 
                        name='{$columnName}' 
                        id='{$columnName}'
                        class='shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline'
                        {$isRequired}
                        {$isReadOnly}
                    >{$value}</textarea>";
                } else {
                    $html .= "
                    <input 
                        type='{$type}' 
                        name='{$columnName}' 
                        id='{$columnName}'
                        value='{$value}'
                        class='shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline'
                        {$isRequired}
                        {$isReadOnly}
                    >";
                }

                $html .= "</div>";
            }

            $html .= "
                <div class='flex items-center justify-between'>
                    <button type='submit' class='bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline'>
                        Update
                    </button>
                    <a href='crud.php?action=list&table={$this->tableName}' class='text-blue-500 hover:text-blue-800'>
                        Back to List
                    </a>
                </div>
            </form>";

            return $html;
        } catch (PDOException $e) {
            error_log("Error generating edit form: " . $e->getMessage());
            return "<div class='text-red-500'>Error generating edit form.</div>";
        }
    }

    // Generate List View
    public function generateListView($page = 1, $perPage = 10) {
        try {
            // Get total records
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM {$this->tableName}");
            $stmt->execute();
            $total = $stmt->fetch()['total'];
            
            // Calculate pagination
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;

            // Get records for current page
            $stmt = $this->conn->prepare("SELECT * FROM {$this->tableName} LIMIT ? OFFSET ?");
            $stmt->execute([$perPage, $offset]);
            $records = $stmt->fetchAll();

            $html = "
            <div class='container mx-auto px-4 py-8'>
                <div class='flex justify-between items-center mb-6'>
                    <h2 class='text-2xl font-bold'>" . ucfirst($this->tableName) . " List</h2>
                    <a href='crud.php?action=create&table={$this->tableName}' 
                       class='bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded'>
                        Add New
                    </a>
                </div>

                <div class='overflow-x-auto bg-white rounded-lg shadow'>
                    <table class='min-w-full leading-normal'>
                        <thead>
                            <tr>";
                            
            // Table headers
            $columns = $this->getTableColumns();
            foreach ($columns as $column) {
                $html .= "<th class='px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider'>" 
                      . ucfirst($column['COLUMN_NAME']) 
                      . "</th>";
            }
            $html .= "<th class='px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider'>Actions</th></tr></thead><tbody>";

            // Table rows
            foreach ($records as $record) {
                $html .= "<tr class='hover:bg-gray-50'>";
                foreach ($columns as $column) {
                    $value = htmlspecialchars($record[$column['COLUMN_NAME']]);
                    $html .= "<td class='px-5 py-5 border-b border-gray-200 text-sm'>{$value}</td>";
                }

                // Action buttons
                $id = $record[$this->primaryKey];
                $html .= "
                <td class='px-5 py-5 border-b border-gray-200 text-sm'>
                    <a href='crud.php?action=edit&table={$this->tableName}&id={$id}' 
                       class='text-blue-600 hover:text-blue-900 mr-4'>Edit</a>
                    <a href='crud.php?action=delete&table={$this->tableName}&id={$id}' 
                       class='text-red-600 hover:text-red-900'
                       onclick='return confirm(\"Are you sure you want to delete this record?\")'>Delete</a>
                </td>";
                $html .= "</tr>";
            }

            $html .= "</tbody></table></div>";

            // Pagination
            if ($totalPages > 1) {
                $html .= "<div class='mt-6 flex justify-center'>";
                for ($i = 1; $i <= $totalPages; $i++) {
                    $activeClass = $i === $page ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 hover:bg-blue-100';
                    $html .= "
                    <a href='crud.php?action=list&table={$this->tableName}&page={$i}' 
                       class='mx-1 px-4 py-2 border rounded {$activeClass}'>
                        {$i}
                    </a>";
                }
                $html .= "</div>";
            }

            $html .= "</div>";
            return $html;

        } catch (PDOException $e) {
            error_log("Error generating list view: " . $e->getMessage());
            return "<div class='text-red-500'>Error generating list view.</div>";
        }
    }

    // Create Record
    public function create($data) {
        try {
            $columns = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($data) - 1) . '?';
            
            $sql = "INSERT INTO {$this->tableName} (" . implode(',', $columns) . ") VALUES ($placeholders)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
            
            return ['success' => true, 'message' => 'Record created successfully'];
        } catch (PDOException $e) {
            error_log("Error creating record: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error creating record'];
        }
    }

    // Update Record
    public function update($id, $data) {
        try {
            $updates = [];
            $values = [];
            
            foreach ($data as $column => $value) {
                $updates[] = "$column = ?";
                $values[] = $value;
            }
            
            $values[] = $id;
            $sql = "UPDATE {$this->tableName} SET " . implode(',', $updates) . " WHERE {$this->primaryKey} = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
            
            return ['success' => true, 'message' => 'Record updated successfully'];
        } catch (PDOException $e) {
            error_log("Error updating record: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating record'];
        }
    }

    // Delete Record
    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = ?");
            $stmt->execute([$id]);
            
            return ['success' => true, 'message' => 'Record deleted successfully'];
        } catch (PDOException $e) {
            error_log("Error deleting record: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting record'];
        }
    }

    // Helper method to determine input type based on MySQL data type
    private function getInputType($mysqlType) {
        $typeMap = [
            'int' => 'number',
            'tinyint' => 'number',
            'smallint' => 'number',
            'mediumint' => 'number',
            'bigint' => 'number',
            'float' => 'number',
            'double' => 'number',
            'decimal' => 'number',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'timestamp' => 'datetime-local',
            'time' => 'time',
            'year' => 'number',
            'char' => 'text',
            'varchar' => 'text',
            'text' => 'textarea',
            'tinytext' => 'textarea',
            'mediumtext' => 'textarea',
            'longtext' => 'textarea',
            'boolean' => 'checkbox',
            'tinyint(1)' => 'checkbox',
            'email' => 'email'
        ];

        // Extract base type (remove size specification)
        $baseType = strtolower(preg_replace('/\(.*\)/', '', $mysqlType));
        
        return $typeMap[$baseType] ?? 'text';
    }
}
