<?php
// Database connection details
$db1 = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'root_admin',
    'name' => 'pacos_clean'
];

$db2 = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'root_admin',
    'name' => 'pacos'
];

// Connect to databases
$conn1 = new mysqli($db1['host'], $db1['user'], $db1['pass'], $db1['name']);
$conn2 = new mysqli($db2['host'], $db2['user'], $db2['pass'], $db2['name']);

if ($conn1->connect_error || $conn2->connect_error) {
    die("Connection failed: " . $conn1->connect_error . " / " . $conn2->connect_error);
}

// Fetch table names
function getTables($conn, $dbname) {
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    return $tables;
}

$tables1 = getTables($conn1, $db1['name']);
$tables2 = getTables($conn2, $db2['name']);

// Compare tables
$missingInDb2 = array_diff($tables1, $tables2);
$missingInDb1 = array_diff($tables2, $tables1);
$commonTables = array_intersect($tables1, $tables2);

echo "📌 Missing Tables:\n";
echo "In {$db2['name']} but present in {$db1['name']}:\n";
print_r($missingInDb2);

echo "In {$db1['name']} but present in {$db2['name']}:\n";
print_r($missingInDb1);

// Function to get columns of a table
function getColumns($conn, $table) {
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    return $columns;
}

echo "<br>\n🔍 Column Differences in Common Tables:\n";
foreach ($commonTables as $table) {
    $cols1 = getColumns($conn1, $table);
    $cols2 = getColumns($conn2, $table);

    $missingInDb2 = array_diff($cols1, $cols2);
    $missingInDb1 = array_diff($cols2, $cols1);

    if (!empty($missingInDb1) || !empty($missingInDb2)) {
        echo "Table: $table\n";
        if (!empty($missingInDb2)) {
            echo "  Missing in {$db2['name']}: " . implode(', ', $missingInDb2) . "\n";
        }
        if (!empty($missingInDb1)) {
            echo "  Missing in {$db1['name']}: " . implode(', ', $missingInDb1) . "\n";
        }
    }
}

// Close connections
$conn1->close();
$conn2->close();
?>
