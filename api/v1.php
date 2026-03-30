<?php
// api/v1.php
require_once __DIR__ . '/config.php';

// Authenticate request
check_auth();

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($resource)) {
    send_response(400, ['error' => 'Missing resource parameter.']);
}

// Map resources to database tables
$resource_table_map = [
    'surat_masuk' => 'surat_masuk',
    'surat_keluar' => 'surat_keluar',
    'notulen' => 'notulen',
    'klasifikasi' => 'klasifikasi_surat',
    'arsip_berkas' => 'arsip_berkas'
];

if (!isset($resource_table_map[$resource])) {
    send_response(404, ['error' => 'Resource not found.']);
}

$table = $resource_table_map[$resource];

// White-list allowed columns for each resource for security
$resource_columns = [
    'surat_masuk' => ['nomor_arsip', 'nomor_surat', 'perihal', 'asal_surat', 'tanggal_diterima', 'acc_kepada', 'nama_file_pdf'],
    'surat_keluar' => ['kode_arsip', 'nomor_surat', 'perihal', 'tujuan_surat', 'tanggal_kirim', 'klasifikasi_id', 'nama_file_pdf'],
    'notulen' => ['tanggal', 'kegiatan', 'nama_file'],
    'klasifikasi' => ['kode', 'jenis_surat'],
    'arsip_berkas' => ['no_berkas', 'nama_berkas', 'tanggal_berkas', 'file_path']
];

switch ($method) {
    case 'GET':
        handle_get($table, $id);
        break;
    case 'POST':
        handle_post($table);
        break;
    default:
        send_response(405, ['error' => 'Method not allowed. Use GET or POST.']);
        break;
}

/**
 * Handle GET requests (Read)
 */
function handle_get($table, $id) {
    global $koneksi;
    if ($id > 0) {
        $stmt = $koneksi->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result) {
            send_response(200, $result);
        } else {
            send_response(404, ['error' => 'Record not found.']);
        }
    } else {
        $query = "SELECT * FROM $table ORDER BY id DESC";
        $result = mysqli_query($koneksi, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        send_response(200, $data);
    }
}

/**
 * Handle POST requests (Create)
 */
function handle_post($table) {
    global $koneksi, $resource, $resource_columns;
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        send_response(400, ['error' => 'Invalid JSON input.']);
    }

    // Filter input against the allowed white-list
    $allowed_cols = $resource_columns[$resource];
    $insert_data = [];
    foreach ($allowed_cols as $col) {
        if (isset($input[$col])) {
            $insert_data[$col] = $input[$col];
        }
    }

    if (empty($insert_data)) {
        send_response(400, ['error' => 'No valid columns provided for this resource.']);
    }

    $columns = array_keys($insert_data);
    $values = array_values($insert_data);

    // Construct safe column names with backticks
    $safe_columns = array_map(function($c) { return "`$c`"; }, $columns);
    $placeholders = str_repeat('?,', count($columns) - 1) . '?';

    $types = '';
    foreach ($values as $val) {
        if (is_int($val)) $types .= 'i';
        elseif (is_double($val)) $types .= 'd';
        else $types .= 's';
    }

    $sql = "INSERT INTO `$table` (" . implode(',', $safe_columns) . ") VALUES ($placeholders)";
    $stmt = $koneksi->prepare($sql);
    if (!$stmt) {
        send_response(500, ['error' => 'SQL Error: ' . $koneksi->error]);
    }

    $stmt->bind_param($types, ...$values);
    if ($stmt->execute()) {
        send_response(201, ['message' => 'Record created successfully.', 'id' => $koneksi->insert_id]);
    } else {
        send_response(500, ['error' => 'Execution error: ' . $stmt->error]);
    }
}