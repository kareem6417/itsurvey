<?php
// handler.php
// Pastikan tidak ada spasi/baris kosong sebelum tag <?php di atas

require 'config.php';
header('Content-Type: application/json');

// Matikan error reporting agar tidak merusak format JSON
error_reporting(0);
ini_set('display_errors', 0);

$action = $_GET['action'] ?? '';

// =======================================================================
// 1. LOGIC PENCARIAN NIK (Sesuai Backup Anda)
// =======================================================================
if ($action == 'search_nik') {
    // Bersihkan output buffer untuk memastikan JSON bersih
    ob_clean();
    
    $nik = $_GET['nik'] ?? '';

    // API CONFIG (Sesuai Backup)
    $apiUrl = "http://mandiricoal.co.id:1880/master/employee/pernr/" . $nik;
    $apiKey = "ca6cda3462809fc894801c6f84e0cd8ecff93afb";

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => array("api_key: " . $apiKey),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo json_encode(['status' => 'error', 'message' => 'Koneksi API Gagal.']);
        exit;
    }

    $data = json_decode($response, true);

    if (isset($data['employee']) && count($data['employee']) > 0) {
        $emp = $data['employee'][0];
        
        // Mapping Data (Sesuai Backup)
        $result = [
            'nik' => $emp['pernr'],
            'name' => $emp['cname'],
            'email' => $emp['email_internet'],
            'company_name' => $emp['company_name'],
            'company_id' => $emp['company_id'],
            'division' => $emp['org_unit_name'],
            'join_date' => $emp['hire_date_formatted']
        ];

        echo json_encode(['status' => 'success', 'data' => $result]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'NIK tidak ditemukan.']);
    }
    exit;
}

// =======================================================================
// 2. LOGIC SUBMIT (DIPERBAIKI UNTUK CHECKBOX)
// =======================================================================
if ($action == 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bersihkan output buffer
    ob_clean();

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validasi input
    if (!$input) {
        echo json_encode(['status' => 'error', 'message' => 'Data input tidak valid']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // A. Simpan Data Responden
        $stmt = $pdo->prepare("INSERT INTO respondents (nik, full_name, email, division, company_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $input['nik'], 
            $input['name'], 
            $input['email'], 
            $input['division'], 
            $input['company_id'] ?: null
        ]);
        
        $respondent_id = $pdo->lastInsertId();

        // B. Simpan Jawaban
        $stmtAnswer = $pdo->prepare("INSERT INTO answers (respondent_id, question_id, answer_value, respondent_nik, respondent_name, respondent_email, respondent_company, respondent_division) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $companyName = $input['company_name'] ?? '-';

        // Cek apakah ada jawaban yang dikirim
        if (isset($input['answers']) && is_array($input['answers'])) {
            foreach ($input['answers'] as $q_id => $val) {
                
                // --- BAGIAN INI YANG MEMPERBAIKI ERROR CHECKBOX ---
                if (is_array($val)) {
                    // Jika data berupa array (checkbox), gabungkan jadi string koma
                    // Contoh: ['FICO', 'HR'] menjadi "FICO, HR"
                    $final_val = implode(', ', $val);
                } else {
                    // Jika data biasa (text/radio), biarkan saja
                    $final_val = $val;
                }
                // --------------------------------------------------

                $stmtAnswer->execute([
                    $respondent_id, 
                    $q_id, 
                    $final_val, // Gunakan variabel yang sudah divalidasi
                    $input['nik'], 
                    $input['name'], 
                    $input['email'], 
                    $companyName, 
                    $input['division']
                ]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan']);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    }
    exit;
}
?>