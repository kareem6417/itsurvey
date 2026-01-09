<?php
// handler.php
require 'config.php';
header('Content-Type: application/json');

// Matikan display error agar tidak merusak format JSON
error_reporting(0);
ini_set('display_errors', 0);

$action = $_GET['action'] ?? '';

// =======================================================================
// 1. LOGIC PENCARIAN NIK (TIDAK DIUBAH - SESUAI BACKUP ASLI)
// =======================================================================
if ($action == 'search_nik') {
    $nik = $_GET['nik'] ?? '';

    // API CONFIG ASLI DARI BACKUP ANDA
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

    // MAPPING ASLI SESUAI BACKUP ANDA
    if (isset($data['employee']) && count($data['employee']) > 0) {
        $emp = $data['employee'][0];
        
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
// 2. LOGIC SUBMIT (DITAMBAHKAN PENANGANAN CHECKBOX)
// =======================================================================
if ($action == 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        $pdo->beginTransaction();

        // 1. Simpan Data Responden
        $stmt = $pdo->prepare("INSERT INTO respondents (nik, full_name, email, division, company_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $input['nik'], 
            $input['name'], 
            $input['email'], 
            $input['division'], 
            $input['company_id'] ?: null
        ]);
        
        $respondent_id = $pdo->lastInsertId();

        // 2. Simpan Jawaban
        $stmtAnswer = $pdo->prepare("INSERT INTO answers (respondent_id, question_id, answer_value, respondent_nik, respondent_name, respondent_email, respondent_company, respondent_division) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $companyName = $input['company_name'] ?? '-';

        foreach ($input['answers'] as $q_id => $val) {
            
            // --- [MODIFIKASI: PENANGANAN CHECKBOX] ---
            // Cek apakah $val adalah Array (Checkbox)?
            if (is_array($val)) {
                // Jika ya, gabungkan jadi string (contoh: "FICO, HR")
                $final_value = implode(', ', $val);
            } else {
                // Jika bukan array, biarkan apa adanya
                $final_value = $val;
            }
            // -----------------------------------------

            $stmtAnswer->execute([
                $respondent_id, 
                $q_id, 
                $final_value, // Gunakan variabel yang sudah di-cek ($final_value)
                $input['nik'], 
                $input['name'], 
                $input['email'], 
                $companyName, 
                $input['division']
            ]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan']);

    } catch (Exception $e) {
        $pdo->rollBack();
        // Catat error asli di log server, tapi kirim pesan aman ke user
        error_log($e->getMessage()); 
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data ke database.']);
    }
    exit;
}
?>