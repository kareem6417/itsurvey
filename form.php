<?php
require 'config.php'; 

// 1. Cek akses POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// 2. AMBIL INPUT
$input_company = $_POST['company_id'] ?? null; 

// 3. LOGIC PINTAR: CEK KE TABEL COMPANIES
$final_company_id = null;
$final_company_name = $_POST['company_name'] ?? '';

if ($input_company) {
    $stmt = $pdo->prepare("SELECT id, name, code FROM companies WHERE id = ? OR code = ? LIMIT 1");
    $stmt->execute([$input_company, $input_company]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $final_company_id = $data['id']; 
        if (empty($final_company_name)) {
            $final_company_name = $data['name'];
        }
    }
}

// 4. SUSUN DATA USER
$user = [
    'nik' => $_POST['nik'] ?? null,
    'name' => $_POST['name'] ?? 'User',
    'email' => $_POST['email'] ?? '',
    'division' => $_POST['division'] ?? '',
    'company_id' => $final_company_id,
    'company_name' => $final_company_name
];

// 5. AMBIL PERTANYAAN
$stmtQ = $pdo->prepare("SELECT * FROM questions WHERE company_id = ? OR company_id IS NULL ORDER BY id ASC");
$stmtQ->execute([$final_company_id]);
$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

$totalQuestions = count($questions);
?>

<!DOCTYPE html>
<html lang="id" x-data="{ isDark: false }" :class="{ 'dark': isDark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Kepuasan Layanan ITE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    animation: { 'fade-in-up': 'fadeInUp 0.5s ease-out forwards' }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .pro-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        body { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 min-h-screen transition-colors duration-300">

    <div x-data="formApp()" x-cloak class="relative">
        
        <div class="fixed top-0 left-0 w-full h-2 bg-slate-200 dark:bg-slate-800 z-50">
            <div class="h-full bg-indigo-600 transition-all duration-500 ease-out" :style="'width: ' + progress + '%'"></div>
        </div>

        <header class="sticky top-2 z-40 px-4 mt-4">
            <div class="max-w-4xl mx-auto bg-white/80 dark:bg-slate-800/80 backdrop-blur-md rounded-2xl shadow-lg border border-white/20 p-4 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-200">IT</div>
                    <div>
                        <h2 class="font-bold text-slate-800 dark:text-white leading-tight">Survey ITE 2026</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="userData.company_name"></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="isDark = !isDark" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-indigo-50 transition-colors">
                        <svg x-show="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <svg x-show="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </button>
                </div>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 py-8 pb-32">
            <div class="mb-10 p-8 bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[2rem] text-white shadow-2xl relative overflow-hidden group">
                <div class="relative z-10">
                    <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-medium backdrop-blur-md mb-4 inline-block">Survey Digital</span>
                    <h1 class="text-3xl font-bold mb-2">Halo, <span x-text="userData.name"></span>!</h1>
                    <p class="text-indigo-100 max-w-md">Bantu kami meningkatkan kualitas layanan IT dengan memberikan feedback jujur Anda.</p>
                </div>
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700"></div>
            </div>

            <div class="space-y-8">
                <?php $delay = 0.1; foreach ($questions as $index => $q): ?>
                    <?php 
                        $id = $q['id']; 
                        $delay += 0.05;
                    ?>
                    
                    <div id="q-card-<?php echo $id; ?>" 
                        class="pro-card p-6 sm:p-8 rounded-3xl border transition-all duration-300 relative overflow-hidden" 
                        :class="isDark ? 'bg-slate-800 border-slate-700 shadow-none' : 'bg-white border-slate-100 shadow-xl shadow-slate-200/50'"
                        style="animation: fadeInUp 0.5s ease-out <?php echo $delay; ?>s forwards; opacity: 0;"
                        
                        /* LOGIKA DEPENDENSI REVISI */
                        x-show="isVisible(<?php echo $id; ?>, <?php echo json_encode($q['dependency_id']); ?>, <?php echo json_encode($q['dependency_value']); ?>)"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform -translate-y-4"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                    >
                        
                        <div class="flex items-start gap-4 mb-6">
                            <div class="flex-shrink-0 w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                                <?php echo $index + 1; ?>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-tight pt-1">
                                <?php echo $q['question_text']; ?>
                            </h3>
                        </div>

                        <div class="pl-0 sm:pl-14">
                            <?php if ($q['input_type'] == 'rating_10'): ?>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-5 sm:grid-cols-10 gap-2">
                                        <?php for($i=1; $i<=10; $i++): ?>
                                            <label class="cursor-pointer group">
                                                <input type="radio" name="q_<?php echo $id; ?>" value="<?php echo $i; ?>" x-model="$store.answersStore.answers[<?php echo $id; ?>]" @change="recalc()" class="sr-only">
                                                <div class="h-12 rounded-xl border-2 flex items-center justify-center font-bold transition-all duration-200"
                                                    :class="$store.answersStore.answers[<?php echo $id; ?>] == <?php echo $i; ?> 
                                                        ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-200' 
                                                        : (isDark ? 'border-slate-700 text-slate-400 hover:border-indigo-500' : 'border-slate-100 text-slate-400 hover:border-indigo-500 bg-slate-50')">
                                                    <?php echo $i; ?>
                                                </div>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="flex justify-between text-xs font-bold uppercase tracking-wider text-slate-400 px-1">
                                        <span><?php echo $q['min_label'] ?? 'Sangat Buruk'; ?></span>
                                        <span><?php echo $q['max_label'] ?? 'Sangat Baik'; ?></span>
                                    </div>
                                </div>

                            <?php elseif ($q['input_type'] == 'yes_no'): ?>
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <?php foreach(['Ya', 'Tidak'] as $val): ?>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="q_<?php echo $id; ?>" value="<?php echo $val; ?>" x-model="$store.answersStore.answers[<?php echo $id; ?>]" @change="recalc()" class="sr-only">
                                            <div class="py-4 px-6 rounded-2xl border-2 text-center font-bold transition-all duration-200"
                                                :class="$store.answersStore.answers[<?php echo $id; ?>] == '<?php echo $val; ?>'
                                                    ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg'
                                                    : (isDark ? 'border-slate-700 text-slate-500 hover:bg-slate-700' : 'border-slate-100 text-slate-500 bg-slate-50 hover:bg-slate-100')">
                                                <?php echo $val; ?>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                            <?php elseif ($q['input_type'] == 'checkbox'): ?>
                                <?php 
                                    $options = array_map('trim', explode(',', $q['options'])); 
                                ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-init="if(!Array.isArray($store.answersStore.answers[<?php echo $id; ?>])) $store.answersStore.answers[<?php echo $id; ?>] = []">
                                    <?php foreach ($options as $opt): ?>
                                        <?php 
                                            $desc = '';
                                            if (strpos($opt, 'FICO') !== false) $desc = 'Keuangan & Akuntansi';
                                            elseif (strpos($opt, 'HR') !== false) $desc = 'Data Karyawan & Payroll';
                                            elseif (strpos($opt, 'MM') !== false) $desc = 'Logistik & Inventory';
                                            elseif (strpos($opt, 'PM') !== false) $desc = 'Pemeliharaan Aset';
                                            elseif (strpos($opt, 'Lainnya') !== false) $desc = 'Modul lainnya';
                                        ?>
                                        <label class="cursor-pointer group">
                                            <input type="checkbox" value="<?php echo htmlspecialchars($opt); ?>" x-model="$store.answersStore.answers[<?php echo $id; ?>]" @change="recalc()" class="sr-only">
                                            <div class="w-full p-4 rounded-2xl border-2 flex items-center gap-4 transition-all duration-200"
                                                :class="$store.answersStore.answers[<?php echo $id; ?>].includes('<?php echo htmlspecialchars($opt); ?>')
                                                    ? 'bg-indigo-600 border-indigo-600 text-white'
                                                    : (isDark ? 'border-slate-700 bg-slate-800 text-slate-400' : 'border-slate-100 bg-slate-50 text-slate-500 hover:bg-slate-100')">
                                                <div class="w-6 h-6 rounded-lg border-2 flex items-center justify-center flex-shrink-0"
                                                     :class="$store.answersStore.answers[<?php echo $id; ?>].includes('<?php echo htmlspecialchars($opt); ?>') ? 'bg-white border-white' : 'border-slate-300'">
                                                    <svg x-show="$store.answersStore.answers[<?php echo $id; ?>].includes('<?php echo htmlspecialchars($opt); ?>')" class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-sm"><?php echo $opt; ?></span>
                                                    <span class="text-[10px] opacity-70 font-medium"><?php echo $desc; ?></span>
                                                </div>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                            <?php else: ?>
                                <textarea 
                                    x-model="$store.answersStore.answers[<?php echo $id; ?>]" 
                                    @input="recalc()"
                                    rows="4" 
                                    class="w-full p-5 rounded-2xl border-2 outline-none transition-all duration-200"
                                    :class="isDark ? 'bg-slate-900 border-slate-700 text-white focus:border-indigo-500' : 'bg-slate-50 border-slate-100 focus:bg-white focus:border-indigo-500'"
                                    placeholder="Ketik jawaban Anda di sini..."></textarea>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-16 text-center">
                <button 
                    @click="submit()" 
                    :disabled="isSubmitting"
                    class="w-full sm:w-auto px-12 py-5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-xl shadow-indigo-200 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3 mx-auto"
                >
                    <template x-if="!isSubmitting">
                        <div class="flex items-center gap-3">
                            <span>Kirim Survey</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </div>
                    </template>
                    <template x-if="isSubmitting">
                        <span>Sedang Mengirim...</span>
                    </template>
                </button>
            </div>
        </main>

        <footer class="py-10 text-center border-t border-slate-100 dark:border-slate-800">
            <p class="text-slate-400 text-sm font-medium">© 2026 ITE Support Departement. All rights reserved.</p>
        </footer>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('answersStore', {
                answers: {}
            });
        });

        function formApp() {
            return {
                userData: <?php echo json_encode($user); ?>,
                totalQuestions: <?php echo $totalQuestions; ?>,
                progress: 0,
                isSubmitting: false,

                init() {
                    // Pastikan progress terhitung di awal
                    this.recalc();
                },

                isVisible(id, parentId, trigger) {
                    if (!parentId) return true;
                    
                    let ans = Alpine.store('answersStore').answers[parentId];
                    if (!ans) return false;

                    // Support Checkbox (Array) dan Radio (String)
                    let visible = Array.isArray(ans) ? ans.includes(trigger) : ans == trigger;
                    
                    // Bersihkan jawaban jika pertanyaan dihidden
                    if (!visible && Alpine.store('answersStore').answers[id]) {
                        delete Alpine.store('answersStore').answers[id];
                    }
                    return visible;
                },

                recalc() {
                    let filled = 0;
                    let visibleCount = 0;
                    
                    // Kita scan DOM untuk menghitung hanya yang visible
                    document.querySelectorAll('.pro-card').forEach((card) => {
                        if (card.style.display !== 'none') {
                            visibleCount++;
                            let qId = card.id.split('-')[2];
                            let val = Alpine.store('answersStore').answers[qId];
                            if (val && val.length > 0) filled++;
                        }
                    });

                    this.progress = visibleCount > 0 ? Math.round((filled / visibleCount) * 100) : 0;
                },

                async submit() {
                    // Validasi minimal
                    let unanswered = [];
                    document.querySelectorAll('.pro-card').forEach((card) => {
                        if (card.style.display !== 'none') {
                            let qId = card.id.split('-')[2];
                            let val = Alpine.store('answersStore').answers[qId];
                            if (!val || val.length === 0) unanswered.push(qId);
                        }
                    });

                    if (unanswered.length > 0) {
                        alert("Mohon lengkapi semua pertanyaan yang tampil.");
                        document.getElementById('q-card-' + unanswered[0]).scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }

                    this.isSubmitting = true;
                    const payload = { ...this.userData, answers: Alpine.store('answersStore').answers };

                    try {
                        const res = await fetch('handler.php?action=submit', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify(payload)
                        });

                        const text = await res.text();
                        let json = JSON.parse(text);

                        if (json.status === 'success') {
                            window.location.href = 'thankyou.php';
                        } else {
                            alert("Gagal: " + json.message);
                        }
                    } catch(e) {
                        console.error(e);
                        alert("Koneksi bermasalah.");
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</body>
</html>