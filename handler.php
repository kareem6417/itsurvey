<?php
require 'config.php';
header('Content-Type: application/json');

// Matikan error reporting agar output JSON bersih
error_reporting(0);
ini_set('display_errors', 0);

$action = $_GET['action'] ?? '';

// =============================================================
// 1. BAGIAN SEARCH NIK (API)
// =============================================================
if ($action == 'search_nik') {
    $nik = $_GET['nik'] ?? '';
    
    // Config API
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
        echo json_encode(['status' => 'error', 'message' => 'Gagal koneksi API']);
    } else {
        $data = json_decode($response, true);
        if ($data && isset($data['data']) && !empty($data['data'])) {
            $emp = $data['data'];
            
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'name' => $emp['employee_name'] ?? $emp['cname'] ?? '',
                    'email' => $emp['email'] ?? $emp['umail'] ?? '', // Jika kosong, akan dikirim string kosong
                    'division' => $emp['division'] ?? $emp['orgtx'] ?? '',
                    'department' => $emp['department'] ?? '',
                    'position' => $emp['position'] ?? $emp['plstx'] ?? '',
                    'company_name' => $emp['company_name'] ?? '',
                    'dob_check' => $emp['date_of_birth'] ?? $emp['birthDate'] ?? ''
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'NIK Tidak Ditemukan']);
        }
    }
}

// =============================================================
// 2. BAGIAN SUBMIT SURVEY (SIMPAN KE DATABASE)
// =============================================================
elseif ($action == 'submit_survey') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            throw new Exception("Data tidak valid.");
        }

        // --- AMBIL DATA ---
        // Kita ambil langsung dari input karena Index.php sudah mengirim data final
        // (Entah itu dari API atau hasil ketikan manual user)
        $nik = $input['nik'] ?? '-';
        $name = $input['name'] ?? 'User';
        $email = $input['email'] ?? '-';
        $division = $input['division'] ?? '-';
        $companyId = $input['company_id'] ?? null;
        $companyName = $input['company_name'] ?? '-';

        $pdo->beginTransaction();

        // A. Insert ke tabel RESPONDENTS
        $stmt = $pdo->prepare("INSERT INTO respondents (submission_date, nik, full_name, email, division, company_id) VALUES (NOW(), ?, ?, ?, ?, ?)");
        $stmt->execute([$nik, $name, $email, $division, $companyId]);
        $respondent_id = $pdo->lastInsertId();

        // B. Insert ke tabel ANSWERS
        $stmtAnswer = $pdo->prepare("INSERT INTO answers (respondent_id, question_id, answer_value, respondent_nik, respondent_name, respondent_email, respondent_company, respondent_division) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $answers = $input['answers'] ?? [];

        if (is_array($answers)) {
            foreach ($answers as $q_id => $val) {
                // Handle Checkbox (Array -> String)
                $final_val = is_array($val) ? implode(', ', $val) : $val;

                $stmtAnswer->execute([
                    $respondent_id, 
                    $q_id, 
                    $final_val, 
                    $nik, 
                    $name,  // Nama pasti terisi (API atau Manual)
                    $email, // Email pasti terisi (API atau Manual)
                    $companyName, 
                    $division
                ]);
            }
        }
        
        $pdo->commit();
        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
    }
}
?>