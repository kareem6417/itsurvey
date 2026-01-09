<?php
// handler.php
// Pastikan tidak ada spasi sebelum tag <?php

// 1. Mulai buffering (Tangkap semua output liar)
ob_start();

require 'config.php';

// Matikan error report yang merusak JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// =======================================================================
// 1. LOGIC PENCARIAN NIK (API)
// =======================================================================
if ($action == 'search_nik') {
    // BERSIHKAN BUFFER SEBELUM KIRIM JSON
    ob_clean(); 
    
    $nik = $_GET['nik'] ?? '';
    if (empty($nik)) {
        echo json_encode(['status' => 'error', 'message' => 'NIK kosong']);
        exit;
    }

    // API CONFIG
    $apiUrl = "http://mandiricoal.co.id:1880/master/employee/pernr/" . $nik;
    $apiKey = "ca6cda3462809fc894801c6f84e0cd8ecff93afb";

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15, // Naikkan timeout
        CURLOPT_HTTPHEADER => array("api_key: " . $apiKey),
        CURLOPT_FAILONERROR => false, // Agar kita bisa baca error body nya
    ));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);

    // Cek Error Koneksi
    if ($err || $response === false) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Gagal koneksi ke API Server. Cek apakah Port 1880 diizinkan oleh Hosting Anda.',
            'debug' => $err
        ]);
        exit;
    }

    $data = json_decode($response, true);

    // Cek Data
    if (isset($data['employee']) && is_array($data['employee']) && count($data['employee']) > 0) {
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
        echo json_encode(['status' => 'error', 'message' => 'NIK tidak ditemukan di database Mandiri.']);
    }
    exit;
}

// =======================================================================
// 2. LOGIC SUBMIT (CHECKBOX FIX)
// =======================================================================
if ($action == 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean(); // Bersihkan buffer lagi
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['status' => 'error', 'message' => 'Data input tidak valid']);
        exit;
    }

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

        if (isset($input['answers']) && is_array($input['answers'])) {
            foreach ($input['answers'] as $q_id => $val) {
                // Modifikasi Checkbox (Array -> String)
                $final_value = is_array($val) ? implode(', ', $val) : $val;

                $stmtAnswer->execute([
                    $respondent_id, 
                    $q_id, 
                    $final_value,
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