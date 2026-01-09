<?php
require 'config.php'; 

// 1. Cek akses POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// 2. AMBIL INPUT (Bisa berupa 'MIP', 'MKP', atau angka '1')
$input_company = $_POST['company_id'] ?? null; 

// 3. LOGIC PINTAR: CEK KE TABEL COMPANIES (BY ID atau BY CODE)
$final_company_id = null;
$final_company_name = $_POST['company_name'] ?? '';

if ($input_company) {
    // Query ini artinya: Cari perusahaan yang ID-nya X ... ATAU ... Code-nya X
    $stmt = $pdo->prepare("SELECT id, name, code FROM companies WHERE id = ? OR code = ? LIMIT 1");
    $stmt->execute([$input_company, $input_company]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // KETEMU! Kita ambil ID Aslinya (Angka)
        $final_company_id = $data['id']; 
        
        // Jika nama kosong, ambil dari database biar rapi
        if (empty($final_company_name)) {
            $final_company_name = $data['name'];
        }
    }
}

// 4. SUSUN DATA USER (PENTING: company_id DIISI ANGKA ID HASIL PENCARIAN DI ATAS)
$user = [
    'nik' => $_POST['nik'] ?? null,
    'name' => $_POST['name'] ?? 'User',
    'email' => $_POST['email'] ?? '',
    'division' => $_POST['division'] ?? '',
    'company_id' => $final_company_id, // <--- Disini kuncinya! Isinya angka (misal: 1), bukan 'MIP' lagi
    'company_name' => $final_company_name
];

// 5. AMBIL PERTANYAAN (Menggunakan ID yang sudah valid)
$stmt = $pdo->prepare("SELECT * FROM questions WHERE company_id IS NULL OR company_id = ? ORDER BY id ASC");
$stmt->execute([$final_company_id]);
$questionsDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. FORMAT PERTANYAAN
$questions = [];
$mainCounter = 0; 
$subCounter = 0;  
$lastParentNum = 0; 

foreach ($questionsDB as $q) {
    $isChild = !empty($q['dependency_id']);
    
    if (!$isChild) {
        $mainCounter++;
        $subCounter = 0;
        $displayNumber = "$mainCounter";
        $lastParentNum = $mainCounter; 
    } else {
        $subCounter++;
        $displayNumber = "$lastParentNum.$subCounter";
    }

    $questions[$q['id']] = [
        'id' => $q['id'],
        'text' => $q['question_text'],
        'type' => $q['input_type'], 
        'number' => $displayNumber,
        'is_child' => $isChild,
        'dependency_id' => !empty($q['dependency_id']) ? $q['dependency_id'] : null,
        'dependency_value' => !empty($q['dependency_value']) ? $q['dependency_value'] : null,
        'label_min' => !empty($q['label_min']) ? $q['label_min'] : 'Sangat Buruk',
        'label_max' => !empty($q['label_max']) ? $q['label_max'] : 'Sangat Baik'
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Satisfaction Survey - <?php echo htmlspecialchars($user['company_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .bg-mesh {
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            background-attachment: fixed;
        }

        .pro-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .pro-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }

        .rating-circle {
            transition: all 0.2s ease;
            width: 100%; aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
            border-radius: 9999px; font-weight: 700; border: 2px solid #e2e8f0; background-color: #f8fafc; color: #64748b;
        }
        .rating-circle:hover { border-color: #3b82f6; color: #3b82f6; background-color: white; transform: scale(1.1); }
        .rating-circle.active { background-color: #2563eb; border-color: #2563eb; color: white; transform: scale(1.1); }

        .toggle-card { transition: all 0.2s ease; border: 1px solid #e2e8f0; background-color: white; }
        .toggle-card:hover { border-color: #94a3b8; background-color: #f8fafc; }
        .toggle-card.active-yes { background-color: #ecfdf5; border-color: #10b981; color: #047857; }
        .toggle-card.active-no { background-color: #fef2f2; border-color: #ef4444; color: #b91c1c; }

        .fade-in-up { animation: fadeInUp 0.5s ease-out forwards; opacity: 0; transform: translateY(15px); }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
        
        .skip-logic-transition { transition: all 0.5s ease-in-out; }
    </style>
</head>
<body class="bg-mesh min-h-screen text-slate-800 antialiased">

    <header class="fixed top-0 inset-x-0 z-50 transition-all duration-500 ease-in-out" 
        :class="{'bg-slate-900/80 backdrop-blur-md shadow-lg py-3 border-b border-white/5': scrolled, 'bg-transparent py-6': !scrolled}"
        x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">
        
        <div class="max-w-5xl mx-auto px-6 flex justify-between items-center">
            <div class="flex items-center gap-5">
                <img src="logo1.png" alt="Logo" class="h-12 w-auto object-contain drop-shadow-lg hover:scale-105 transition-transform duration-300">
                <div class="hidden sm:block">
                    <h1 class="font-bold text-lg leading-tight text-white tracking-tight">IT Satisfaction Survey</h1>
                    <p class="text-xs font-medium uppercase tracking-wider opacity-70 text-blue-100">
                        <?php echo htmlspecialchars($user['company_name']); ?>
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-3 pl-4 pr-1.5 py-1.5 rounded-full border border-white/10 bg-white/5 backdrop-blur-md shadow-inner transition-all hover:bg-white/10">
                <div class="text-right">
                    <p class="text-xs font-bold text-white"><?php echo explode(' ', $user['name'])[0]; ?></p>
                    <p class="text-[10px] text-blue-200 opacity-80"><?php echo htmlspecialchars($user['division']); ?></p>
                </div>
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-100 to-white flex items-center justify-center text-sm font-extrabold text-indigo-700 shadow-sm">
                    <?php echo substr($user['name'], 0, 1); ?>
                </div>
            </div>
        </div>
    </header>

    <main class="pt-32 pb-24 px-4" x-data="surveyForm()">
        <div class="max-w-3xl mx-auto space-y-6">

            <div class="pro-card p-8 sm:p-10 text-center relative overflow-hidden fade-in-up shadow-2xl" style="animation-delay: 0.05s;">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
                <div class="relative z-10">
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-3 tracking-tight">
                        Halo, <span class="text-indigo-600"><?php echo htmlspecialchars($user['name']); ?></span> 👋
                    </h2>
                    <p class="text-slate-500 leading-relaxed max-w-xl mx-auto text-base">
                        Pendapat Anda sangat berharga bagi kami. Mohon isi survey ini dengan objektif.
                    </p>
                </div>
            </div>

            <?php if (empty($questions)): ?>
                <div class="pro-card p-12 text-center fade-in-up">
                    <div class="w-20 h-20 bg-indigo-50 text-indigo-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-indigo-100">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="font-bold text-2xl text-slate-800 mb-3">Survey Belum Tersedia</h3>
                    <p class="text-slate-500 leading-relaxed max-w-lg mx-auto mb-8">
                        Mohon maaf, formulir survei layanan IT untuk unit bisnis 
                        <span class="font-bold text-indigo-600"><?php echo htmlspecialchars($user['company_name']); ?></span> 
                        saat ini sedang dalam tahap persiapan atau pembaruan sistem.
                    </p>
                    <a href="index.php" class="inline-flex items-center gap-2 bg-slate-800 text-white font-bold py-3 px-8 rounded-xl hover:bg-slate-900 transition shadow-lg shadow-slate-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kembali ke Halaman Utama
                    </a>
                </div>
            
            <?php else: ?>

                <form @submit.prevent="submitAll()" class="space-y-6">
                    <?php $delay = 0.15; ?>
                    <?php foreach ($questions as $id => $q): ?>
                    
                    <div id="q-card-<?php echo $id; ?>" 
                        class="pro-card p-6 sm:p-8 fade-in-up relative group skip-logic-transition" 
                        style="animation-delay: <?php echo $delay; ?>s;"
                        
                        x-data="{
                            showQuestion: true, 
                            parentId: <?php echo json_encode($q['dependency_id']); ?>,
                            triggerVal: '<?php echo $q['dependency_value']; ?>',

                            init() {
                                if (this.parentId) {
                                    this.showQuestion = false; 
                                    this.$watch(`$store.answersStore.answers[${this.parentId}]`, (val) => {
                                        this.showQuestion = (val == this.triggerVal);
                                        if (!this.showQuestion) delete $store.answersStore.answers[<?php echo $id; ?>];
                                    });
                                }
                            }
                        }"
                        x-show="showQuestion" 
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                    >
                        <div class="absolute left-0 top-6 bottom-6 w-1 bg-indigo-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <div class="flex gap-4 sm:gap-6 <?php echo $q['is_child'] ? 'ml-0 sm:ml-8 border-l-2 border-indigo-100 pl-4' : ''; ?>">
                            <div class="flex-shrink-0">
                                <div class="flex flex-col items-center justify-center w-14 h-14 rounded-2xl 
                                    <?php echo $q['is_child'] ? 'bg-slate-100 text-slate-500' : 'bg-indigo-600 text-white shadow-lg shadow-indigo-200'; ?> 
                                    font-bold text-lg border border-transparent">
                                    <?php echo $q['number']; ?>
                                </div>
                            </div>

                            <div class="flex-grow pt-1">
                                <h3 class="text-lg font-bold text-slate-800 leading-snug mb-6">
                                    <?php echo $q['text']; ?>
                                </h3>

                                <div class="w-full">
                                    <?php if ($q['type'] == 'yes_no'): ?>
                                        <div class="grid grid-cols-2 gap-4 max-w-sm">
                                            <label class="cursor-pointer group">
                                                <input type="radio" name="q_<?php echo $id; ?>" value="Ya" x-model="$store.answersStore.answers[<?php echo $id; ?>]" class="sr-only">
                                                <div class="toggle-card w-full py-3 px-4 rounded-xl flex items-center justify-center gap-3" :class="{'active-yes': $store.answersStore.answers[<?php echo $id; ?>] == 'Ya'}">
                                                    <span class="font-bold text-base">Ya</span>
                                                </div>
                                            </label>
                                            <label class="cursor-pointer group">
                                                <input type="radio" name="q_<?php echo $id; ?>" value="Tidak" x-model="$store.answersStore.answers[<?php echo $id; ?>]" class="sr-only">
                                                <div class="toggle-card w-full py-3 px-4 rounded-xl flex items-center justify-center gap-3" :class="{'active-no': $store.answersStore.answers[<?php echo $id; ?>] == 'Tidak'}">
                                                    <span class="font-bold text-base">Tidak</span>
                                                </div>
                                            </label>
                                        </div>

                                    <?php elseif ($q['type'] == 'rating_10'): ?>
                                        <div class="grid grid-cols-5 sm:grid-cols-10 gap-2 sm:gap-3">
                                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                                <label class="cursor-pointer group relative">
                                                    <input type="radio" name="q_<?php echo $id; ?>" value="<?php echo $i; ?>" x-model="$store.answersStore.answers[<?php echo $id; ?>]" class="sr-only">
                                                    <div class="rating-circle" :class="{'active': $store.answersStore.answers[<?php echo $id; ?>] == <?php echo $i; ?>}">
                                                        <?php echo $i; ?>
                                                    </div>
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="flex justify-between mt-2 text-xs text-slate-400 font-bold uppercase tracking-wider px-1">
                                            <span class="text-red-400/80"><?php echo $q['label_min']; ?></span>
                                            <span class="text-indigo-400/80"><?php echo $q['label_max']; ?></span>
                                        </div>

                                    <?php elseif ($q['type'] == 'text'): ?>
                                        <textarea x-model="$store.answersStore.answers[<?php echo $id; ?>]" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm resize-none focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="Tulis masukan Anda..."></textarea>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $delay += 0.08; ?>
                    <?php endforeach; ?>

                    <div class="pt-8 flex justify-center">
                        <button type="submit" :disabled="isSubmitting" 
                            class="group relative w-full sm:w-auto min-w-[280px] bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-base font-bold py-4 px-10 rounded-2xl shadow-xl shadow-indigo-500/30 transform hover:-translate-y-1 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden">
                            
                            <div class="flex items-center justify-center gap-3 relative z-10">
                                <span x-show="!isSubmitting">KIRIM SURVEY SEKARANG</span>
                                <span x-show="isSubmitting">Mengirim...</span>
                            </div>
                        </button>
                    </div>
                </form>

            <?php endif; ?>

        </div>

        <div x-show="showSuccess" style="display: none;" 
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100">
            
            <div class="bg-white rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl relative overflow-hidden border border-white/20">
                <div class="w-16 h-16 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-sm border border-green-100">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Terima Kasih!</h3>
                <p class="text-slate-500 text-sm mb-6 leading-relaxed">
                    Data survey Anda telah berhasil kami terima.
                </p>
                <a href="index.php" class="block w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl hover:bg-slate-800 transition shadow-lg shadow-slate-500/20">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('answersStore', { answers: {} });
        });

        function surveyForm() {
            return {
                isSubmitting: false,
                showSuccess: false,
                userData: <?php echo json_encode($user); ?>,
                
                async submitAll() {
                    const submittedAnswers = Alpine.store('answersStore').answers;
                    
                    // Validasi: Pastikan ada jawaban
                    if (Object.keys(submittedAnswers).length === 0) {
                        alert("Mohon isi setidaknya satu pertanyaan.");
                        return;
                    }

                    this.isSubmitting = true;
                    
                    // Gabungkan Data User + Data Jawaban
                    const payload = { ...this.userData, answers: submittedAnswers };

                    try {
                        // --- INI BAGIAN PENTINGNYA ---
                        // Mengirim data ke handler.php dengan action=submit
                        const res = await fetch('handler.php?action=submit', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify(payload)
                        });

                        // Cek respon dari handler.php
                        // Karena handler.php Anda mungkin mengembalikan JSON atau Error text
                        const text = await res.text(); 
                        let json;
                        
                        try {
                            json = JSON.parse(text);
                        } catch (e) {
                            console.error("Server Error:", text);
                            throw new Error("Respon server tidak valid.");
                        }

                        if (json.status === 'success') {
                            // SUKSES!
                            // Opsi 1: Redirect ke halaman Terima Kasih (Disarankan)
                            window.location.href = 'thankyou.php';
                            
                            // Opsi 2: Tampilkan Modal di halaman ini (Jika tidak punya thankyou.php)
                            // this.showSuccess = true;
                        } else {
                            alert("Gagal menyimpan: " + (json.message || "Unknown Error"));
                        }

                    } catch(e) { 
                        console.error(e);
                        alert("Terjadi kesalahan saat mengirim data. Cek Console untuk detail.");
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</body>
</html>