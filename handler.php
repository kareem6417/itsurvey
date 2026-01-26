<?php
// handler.php
require 'config.php';
header('Content-Type: application/json');

// Matikan error display agar JSON response tidak rusak
error_reporting(0);
ini_set('display_errors', 0);

$action = $_GET['action'] ?? '';

// =============================================================
// 1. PENCARIAN NIK (API)
// =============================================================
if ($action == 'search_nik') {
    $nik = $_GET['nik'] ?? '';
    
    // Konfigurasi API Mandiri Coal
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
        echo json_encode(['status' => 'error', 'message' => 'Gagal terhubung ke server API.']);
    } else {
        $data = json_decode($response, true);
        if ($data && isset($data['data']) && !empty($data['data'])) {
            $emp = $data['data'];
            
            // Format Tanggal Lahir untuk Verifikasi
            $dob = $emp['date_of_birth'] ?? $emp['birthDate'] ?? '';
            
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'name' => $emp['employee_name'] ?? $emp['cname'] ?? '',
                    'email' => $emp['email'] ?? $emp['umail'] ?? '',
                    'division' => $emp['division'] ?? $emp['orgtx'] ?? '',
                    'department' => $emp['department'] ?? '',
                    'position' => $emp['position'] ?? $emp['plstx'] ?? '',
                    'company_name' => $emp['company_name'] ?? '',
                    'dob_check' => $dob
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'NIK Tidak Ditemukan']);
        }
    }
}

// =============================================================
// 2. SUBMIT SURVEY (DATABASE SAVING)
// =============================================================
elseif ($action == 'submit_survey') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            throw new Exception("Data tidak valid atau kosong.");
        }

        // --- MAPPING VARIABEL (Agar Masuk Database dengan Benar) ---
        // Kita ambil data, jika kosong isi dengan default '-' atau null
        
        $nik = $input['nik'] ?? '-';
        
        // Perbaikan: Ambil 'name' untuk kolom 'full_name' di DB
        $name = $input['name'] ?? 'User'; 
        
        $email = $input['email'] ?? '-';
        $division = $input['division'] ?? '-';
        $companyId = $input['company_id'] ?? null;
        $companyName = $input['company_name'] ?? '-';

        // --- MULAI TRANSAKSI DATABASE ---
        $pdo->beginTransaction();

        // A. Insert ke tabel RESPONDENTS
        // Kolom di DB: submission_date, nik, full_name, email, division, company_id
        $stmt = $pdo->prepare("INSERT INTO respondents (submission_date, nik, full_name, email, division, company_id) VALUES (NOW(), ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nik, 
            $name,     // Masuk ke full_name
            $email, 
            $division, 
            $companyId
        ]);
        $respondent_id = $pdo->lastInsertId();

        // B. Insert ke tabel ANSWERS
        $stmtAnswer = $pdo->prepare("INSERT INTO answers (respondent_id, question_id, answer_value, respondent_nik, respondent_name, respondent_email, respondent_company, respondent_division) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $answers = $input['answers'] ?? [];

        if (is_array($answers)) {
            foreach ($answers as $q_id => $val) {
                // Handle Checkbox (Array ke String)
                if (is_array($val)) {
                    $final_val = implode(', ', $val);
                } else {
                    $final_val = $val;
                }

                $stmtAnswer->execute([
                    $respondent_id, 
                    $q_id, 
                    $final_val, 
                    $nik, 
                    $name,
                    $email, 
                    $companyName, 
                    $division
                ]);
            }
        }
        
        $pdo->commit();
        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log($e->getMessage()); // Log error di server
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
    }
}
?>