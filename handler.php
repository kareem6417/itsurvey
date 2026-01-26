<?php
// handler.php
require 'config.php';
header('Content-Type: application/json');

// Matikan error reporting agar JSON tidak rusak oleh warning PHP
error_reporting(0);
ini_set('display_errors', 0);

$action = $_GET['action'] ?? '';

// =============================================================
// 1. BAGIAN SEARCH NIK (INI SUDAH BENAR & SESUAI CODE ANDA)
// =============================================================
if ($action == 'search_nik') {
    $nik = $_GET['nik'] ?? '';

    // API CONFIG
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
        $userData = $data['employee'][0];
        
        $companyNameFromApi = isset($userData['BUTXT']) ? trim($userData['BUTXT']) : (isset($userData['ABKTX']) ? trim($userData['ABKTX']) : 'Unknown Company');
        
        // Parsing Tanggal Lahir (GBPAS)
        $dobRaw = isset($userData['GBPAS']) ? trim($userData['GBPAS']) : ''; 
        $dobFormatted = '';
        $cleanDate = preg_replace('/[^0-9]/', '', $dobRaw);

        if (strlen($cleanDate) === 8) {
            $y = substr($cleanDate, 0, 4);
            $m = substr($cleanDate, 4, 2);
            $d = substr($cleanDate, 6, 2);
            $dobFormatted = "$y-$m-$d"; 
        } 

        // Cek Database Perusahaan
        $stmt = $pdo->prepare("SELECT id FROM companies WHERE name = ? LIMIT 1");
        $stmt->execute([$companyNameFromApi]);
        $companyDB = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($companyDB) {
            $companyId = $companyDB['id'];
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO companies (name, code) VALUES (?, ?)");
            $codeDummy = strtoupper(substr(str_replace(' ', '', $companyNameFromApi), 0, 5)); 
            $stmtInsert->execute([$companyNameFromApi, $codeDummy]);
            $companyId = $pdo->lastInsertId();
        }

        $result = [
            'name' => $userData['CNAME'] ?? '',
            'email' => $userData['UMAIL'] ?? '',
            'division' => $userData['ORGTX'] ?? '',
            'company_name' => $companyNameFromApi,
            'company_id' => $companyId,
            'dob_check' => $dobFormatted
        ];

        echo json_encode(['status' => 'success', 'data' => $result]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'NIK tidak ditemukan.']);
    }
    exit;
}

// =============================================================
// 2. BAGIAN SUBMIT (SAYA PERBAIKI DI SINI UTK CHECKBOX)
// =============================================================
if ($action == 'submit_survey') {
    try {
        // Ambil Data JSON
        $input = json_decode(file_get_contents('php://input'), true);

        // Validasi Dasar
        if (!$input) {
            throw new Exception("Data JSON tidak valid atau kosong.");
        }

        // Definisi Variabel dengan Fallback yang Kuat
        // Ini memastikan jika JS mengirim 'name' atau 'respondent_name', tetap terbaca
        $nik = $input['nik'] ?? '-';
        $name = $input['respondent_name'] ?? $input['name'] ?? $input['full_name'] ?? 'User';
        $email = $input['respondent_email'] ?? $input['email'] ?? '-';
        $division = $input['division'] ?? '-';
        $companyId = $input['company_id'] ?: null;
        $companyName = $input['company_name'] ?? '-';

        $pdo->beginTransaction();

        // 1. SIMPAN KE TABEL RESPONDENTS
        $stmt = $pdo->prepare("INSERT INTO respondents (submission_date, nik, full_name, email, division, company_id) VALUES (NOW(), ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nik, 
            $name, 
            $email, 
            $division, 
            $companyId
        ]);
        $respondent_id = $pdo->lastInsertId();

        // 2. SIMPAN KE TABEL ANSWERS
        $stmtAnswer = $pdo->prepare("INSERT INTO answers (respondent_id, question_id, answer_value, respondent_nik, respondent_name, respondent_email, respondent_company, respondent_division) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        if (isset($input['answers']) && is_array($input['answers'])) {
            foreach ($input['answers'] as $q_id => $val) {
                
                // Logika Checkbox (Array to String)
                if (is_array($val)) {
                    $final_val = implode(', ', $val); 
                } else {
                    $final_val = $val; 
                }

                $stmtAnswer->execute([
                    $respondent_id, 
                    $q_id, 
                    $final_val, 
                    $nik,         // [FIX] Gunakan variabel $nik yang konsisten
                    $name,        // [FIX] Gunakan variabel $name (bukan $input['name'] mentah)
                    $email,       // [FIX] Gunakan variabel $email (bukan $input['email'] mentah)
                    $companyName, 
                    $division     // [FIX] Gunakan variabel $division
                ]);
            }
        }
        
        $pdo->commit();
        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Log error asli ke file server log untuk debugging admin
        error_log($e->getMessage());
        
        // Kirim pesan user-friendly
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data: ' . $e->getMessage()]);
    }
}
?>