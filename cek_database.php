<?php
// Load koneksi database
if (!file_exists('config.php')) {
    die("Error: File config.php tidak ditemukan. Pastikan file ini ada di folder yang sama.");
}
require 'config.php';

echo "<h1>Diagnosa Database Survey IT</h1>";

// 1. Cek Perusahaan MKP (ID 2) sebagai sampel
$company_id = 2; 
echo "<h3>1. Pengecekan untuk Company ID: $company_id (MKP)</h3>";

try {
    // Cari Pertanyaan Induk (Checkbox)
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE company_id = ? AND question_text LIKE '%Modul apa yang anda gunakan%'");
    $stmt->execute([$company_id]);
    $parent = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$parent) {
        echo "<div style='color:red'>[GAGAL] Pertanyaan Induk (Checkbox) TIDAK DITEMUKAN.</div>";
    } else {
        echo "<div style='color:green'>[OK] Pertanyaan Induk Ditemukan. ID: <b>" . $parent['id'] . "</b></div>";
        echo "Opsi di Database: <code>" . htmlspecialchars($parent['options']) . "</code><br><br>";

        // Cari Pertanyaan Anak (Textbox)
        $stmtChild = $pdo->prepare("SELECT * FROM questions WHERE dependency_id = ?");
        $stmtChild->execute([$parent['id']]);
        $child = $stmtChild->fetch(PDO::FETCH_ASSOC);

        if (!$child) {
            echo "<div style='color:red; font-weight:bold; border:1px solid red; padding:10px;'>
                    [MASALAH DITEMUKAN]<br>
                    Pertanyaan Textbox (Anak) BELUM ADA di database.<br>
                    Inilah sebabnya kenapa tidak muncul apa-apa saat Anda pilih 'Others'.
                  </div>";
            echo "<p><b>Solusi:</b> Jalankan Query SQL INSERT di bawah ini ke phpMyAdmin.</p>";
        } else {
            echo "<div style='color:green'>[OK] Pertanyaan Textbox Ditemukan. ID: <b>" . $child['id'] . "</b></div>";
            
            // Cek Kesamaan Trigger
            $triggerDB = $child['dependency_value'];
            echo "Syarat Muncul (Trigger): <code>'$triggerDB'</code><br>";
            
            if (strpos($parent['options'], $triggerDB) !== false) {
                echo "<div style='color:green'>[OK] Trigger cocok dengan Opsi.</div>";
            } else {
                echo "<div style='color:red'>[GAGAL] Trigger ('$triggerDB') TIDAK ADA di dalam Opsi Induk. Textbox tidak akan pernah muncul.</div>";
            }
        }
    }

} catch (PDOException $e) {
    echo "Error Database: " . $e->getMessage();
}

echo "<hr>";
echo "<h3>Solusi Perbaikan (SQL)</h3>";
echo "<p>Jika hasil diagnosa di atas merah, silakan jalankan SQL ini di phpMyAdmin:</p>";
?>

<textarea style="width:100%; height:200px; font-family:monospace;">
-- 1. INSERT PERTANYAAN TEXTBOX (Jika Belum Ada)
INSERT INTO `questions` 
(`company_id`, `question_text`, `input_type`, `dependency_id`, `dependency_value`)
SELECT 
    q.company_id,
    'Sebutkan modul SAP lainnya yang anda gunakan:',
    'text',
    q.id,
    'Lainnya (Others)'
FROM `questions` q
WHERE q.question_text LIKE 'Modul apa yang anda gunakan%'
AND q.company_id IN (1, 2, 3, 5)
AND NOT EXISTS (
    SELECT 1 FROM `questions` child 
    WHERE child.dependency_id = q.id
);
</textarea>