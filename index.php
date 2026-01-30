<?php 
require 'config.php'; 

// 1. Ambil daftar perusahaan
$stmt = $pdo->query("SELECT * FROM companies ORDER BY name ASC");
$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Ambil daftar pertanyaan (PENTING: Agar form survey tidak kosong)
$stmtQ = $pdo->query("SELECT * FROM questions ORDER BY id ASC");
$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Satisfaction Survey - Mandirigroup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .animate-fade-up { animation: fadeUp 0.5s ease-out forwards; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        /* Transisi warna input halus */
        input { transition: all 0.2s ease-in-out; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div x-data="surveyApp" class="w-full max-w-3xl bg-white rounded-2xl shadow-xl overflow-hidden relative min-h-[600px] flex flex-col">
        
        <div class="h-32 bg-blue-900 relative overflow-hidden flex-shrink-0">
            <div class="absolute inset-0 bg-blue-800/50"></div>
            <div class="absolute bottom-0 left-0 p-6 z-10">
                <h1 class="text-2xl font-bold text-white" x-text="getTitle()">IT Satisfaction Survey</h1>
                <p class="text-blue-200 text-sm mt-1">Mandirigroup</p>
            </div>
            <div class="absolute bottom-0 left-0 h-1.5 bg-blue-800 w-full z-20">
                <div class="h-full bg-yellow-400 transition-all duration-500 ease-out" :style="'width: ' + ((step / 5) * 100) + '%'"></div>
            </div>
        </div>

        <div class="p-6 md:p-8 flex-1 overflow-y-auto">
            
            <div x-show="step === 1" class="space-y-6 animate-fade-up">
                <div class="text-center mb-8">
                    <h2 class="text-xl font-bold text-slate-800">Pilih Unit Bisnis</h2>
                    <p class="text-slate-500">Dimana Anda bekerja saat ini?</p>
                </div>
                <select x-model="selectedCompanyId" @change="checkCompanyType()" class="w-full p-4 border rounded-xl bg-slate-50 outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>-- Pilih Perusahaan --</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?= $comp['id'] ?>" data-name="<?= htmlspecialchars($comp['name']) ?>"><?= htmlspecialchars($comp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button @click="nextStep()" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl mt-4 hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">Lanjutkan</button>
            </div>

            <div x-show="step === 2" class="space-y-6 animate-fade-up" style="display: none;">
                <div class="text-center mb-8">
                    <h2 class="text-xl font-bold text-slate-800">Validasi Data</h2>
                    <p class="text-slate-500">Masukkan NIK untuk pencarian otomatis</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">NIK</label>
                    <input type="text" x-model="nikInput" @keydown.enter="searchNik()" placeholder="Contoh: 123456" class="w-full p-4 border rounded-xl bg-slate-50 outline-none focus:ring-2 focus:ring-blue-500 font-mono text-lg">
                </div>
                <button @click="searchNik()" :disabled="isLoading" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl mt-4 hover:bg-blue-700 transition flex justify-center shadow-lg shadow-blue-500/30">
                    <span x-show="!isLoading">Cari Data</span>
                    <span x-show="isLoading" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memproses...
                    </span>
                </button>
                <button @click="goBack()" class="w-full text-slate-400 font-bold py-2 mt-2 hover:text-slate-600">Kembali</button>
            </div>

            <div x-show="step === 3" class="space-y-6 animate-fade-up" style="display: none;">
                <div class="text-center mb-8">
                    <h2 class="text-xl font-bold text-slate-800">Verifikasi Keamanan</h2>
                    <p class="text-slate-500">Halo <span class="font-bold text-slate-800" x-text="formData.name"></span>, konfirmasi tanggal lahir Anda.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" x-model="userDobInput" class="w-full p-4 border rounded-xl bg-slate-50 outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button @click="verifyDob()" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl mt-4 hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">Verifikasi</button>
            </div>

            <div x-show="step === 4" class="space-y-6 animate-fade-up" style="display: none;">
                
                <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Data Responden
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">NIK</label>
                            <input type="text" x-model="formData.nik" readonly class="w-full p-3 bg-slate-200 border border-slate-300 rounded-lg text-sm text-slate-500 cursor-not-allowed font-mono">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Nama Lengkap *</label>
                            <input type="text" x-model="formData.name" 
                                   :readonly="locked.name"
                                   class="w-full p-3 rounded-lg text-sm border outline-none focus:ring-1 focus:ring-blue-500"
                                   :class="locked.name ? 'bg-slate-200 text-slate-500 cursor-not-allowed border-slate-300' : 'bg-white text-slate-800 border-blue-300 placeholder-slate-400'"
                                   placeholder="Isi nama manual...">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Email *</label>
                            <input type="email" x-model="formData.email" 
                                   :readonly="locked.email"
                                   class="w-full p-3 rounded-lg text-sm border outline-none focus:ring-1 focus:ring-blue-500"
                                   :class="locked.email ? 'bg-slate-200 text-slate-500 cursor-not-allowed border-slate-300' : 'bg-white text-slate-800 border-blue-300 placeholder-slate-400'"
                                   placeholder="Isi email manual...">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase">Divisi *</label>
                            <input type="text" x-model="formData.division" 
                                   :readonly="locked.division"
                                   class="w-full p-3 rounded-lg text-sm border outline-none focus:ring-1 focus:ring-blue-500"
                                   :class="locked.division ? 'bg-slate-200 text-slate-500 cursor-not-allowed border-slate-300' : 'bg-white text-slate-800 border-blue-300 placeholder-slate-400'"
                                   placeholder="Isi divisi manual...">
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="font-bold text-lg text-slate-800 border-b pb-2">Kuesioner Survey</h3>
                    
                    <p class="text-sm text-slate-600 bg-blue-50 p-3 rounded-lg border border-blue-100">
                        Halo <strong x-text="formData.name || 'Responden'"></strong>, mohon isi penilaian di bawah ini.
                    </p>

                    <template x-for="(q, index) in questions" :key="q.id">
                        <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-blue-400 transition shadow-sm">
                            <p class="font-medium text-slate-800 mb-3 text-sm md:text-base">
                                <span class="text-blue-600 font-bold mr-1" x-text="index + 1 + '.'"></span>
                                <span x-html="q.question_text"></span>
                            </p>
                            <div class="grid grid-cols-5 gap-1">
                                <template x-for="val in 5">
                                    <label class="cursor-pointer relative group">
                                        <input type="radio" :name="'q_' + q.id" :value="val" x-model="answers[q.id]" class="peer sr-only">
                                        <div class="h-10 w-full rounded border flex items-center justify-center text-sm font-bold text-slate-400 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition hover:bg-slate-50 group-hover:border-blue-300">
                                            <span x-text="val"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                            <div class="flex justify-between text-xs text-slate-400 mt-1 px-1">
                                <span>Sangat Buruk</span>
                                <span>Sangat Baik</span>
                            </div>
                        </div>
                    </template>
                </div>

                <button @click="submitSurvey()" :disabled="isLoading" class="w-full bg-green-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-500/30 hover:bg-green-700 transition flex justify-center mt-6">
                    <span x-show="!isLoading">Kirim Survey</span>
                    <span x-show="isLoading">Mengirim Data...</span>
                </button>
            </div>

            <div x-show="step === 5" class="text-center py-10 animate-fade-up" style="display: none;">
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto text-green-600 mb-6 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Terima Kasih!</h2>
                <p class="text-slate-600 mb-8">Masukan Anda sangat berharga bagi kami.</p>
                <a href="index.php" class="inline-block px-8 py-3 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 transition shadow-lg">Kembali ke Awal</a>
            </div>

        </div>
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('surveyApp', () => ({
            // State
            step: 1,
            isLoading: false,
            
            // Inputan
            selectedCompanyId: '',
            selectedCompanyName: '',
            nikInput: '',
            userDobInput: '',
            
            // Data Form & Status Kunci
            formData: { nik: '', name: '', email: '', division: '', department: '', position: '' },
            locked: { name: false, email: false, division: false }, // Default: Terbuka
            
            // API Helper
            apiDobCheck: '',
            
            // Data dari PHP
            questions: <?php echo json_encode($questions); ?>,
            answers: {},

            getTitle() {
                const titles = ["", "Survey", "Cari Data", "Verifikasi", "Isi Data", "Selesai"];
                return titles[this.step] || "Survey";
            },

            checkCompanyType() {
                const select = document.querySelector('select');
                const option = select.options[select.selectedIndex];
                if(option.value) {
                    this.selectedCompanyName = option.getAttribute('data-name');
                    this.nikInput = '';
                }
            },

            nextStep() {
                if (!this.selectedCompanyId) { alert("Mohon pilih perusahaan dulu."); return; }
                this.step = 2;
            },

            goBack() {
                if (this.step > 1) {
                    if (this.step === 4) {
                        this.step = 2; // Dari form balik ke input NIK
                    } else {
                        this.step--;
                    }
                }
            },

            async searchNik() {
                if (!this.nikInput || this.nikInput.length < 3) { alert("NIK minimal 3 digit"); return; }
                
                this.isLoading = true;
                try {
                    const res = await fetch(`handler.php?action=search_nik&nik=${this.nikInput}`);
                    const json = await res.json();

                    if (json.status === 'success') {
                        const d = json.data;
                        
                        // 1. MAPPING DATA (Menggunakan || '' agar tidak undefined)
                        // Kita tampung semua kemungkinan nama variabel
                        const apiName = d.name || d.employee_name || d.full_name || '';
                        const apiEmail = d.email || d.respondent_email || '';
                        const apiDivision = d.division || '';

                        this.formData = {
                            nik: this.nikInput,
                            name: apiName,
                            email: apiEmail,
                            division: apiDivision,
                            department: d.department || '',
                            position: d.position || ''
                        };

                        // 2. LOGIKA KUNCI PINTAR (INI PERBAIKANNYA)
                        // Jika data dari API panjangnya > 0, maka Kunci (TRUE).
                        // Jika kosong, maka Buka (FALSE).
                        this.locked.name = apiName.length > 0;
                        this.locked.email = apiEmail.length > 0;
                        this.locked.division = apiDivision.length > 0;

                        // 3. Cek DOB
                        let rawDob = d.dob_check || d.date_of_birth || '';
                        if (rawDob.length === 8 && !rawDob.includes('-')) {
                            this.apiDobCheck = rawDob.substring(0, 4) + '-' + rawDob.substring(4, 6) + '-' + rawDob.substring(6, 8);
                        } else {
                            this.apiDobCheck = rawDob;
                        }

                        if (this.apiDobCheck) {
                            // Cek jika data parsial (misal nama ada, email kosong)
                            if (!this.locked.email || !this.locked.division) {
                                alert(`Halo ${this.formData.name}. Data email/divisi kosong, silakan lengkapi manual setelah verifikasi.`);
                            }
                            this.step = 3; // Masuk Verifikasi DOB
                        } else {
                            // Tidak ada DOB -> Anggap data keamanan kurang -> Manual Full
                            alert("Data keamanan belum lengkap. Silakan isi data diri secara manual.");
                            this.locked = { name: false, email: false, division: false }; // Buka Semua
                            this.step = 4;
                        }

                    } else {
                        // NIK Tidak Ditemukan -> Manual Full
                        alert("NIK tidak ditemukan. Silakan isi manual.");
                        this.formData = { nik: this.nikInput, name: '', email: '', division: '', department: '', position: '' };
                        this.locked = { name: false, email: false, division: false }; // Buka Semua
                        this.step = 4;
                    }
                } catch (e) {
                    console.error(e);
                    alert("Gagal koneksi server.");
                } finally {
                    this.isLoading = false;
                }
            },

            verifyDob() {
                let inputVal = String(this.userDobInput).trim();
                let apiVal = String(this.apiDobCheck).trim();
                
                // Debugging sederhana jika tanggal tidak cocok
                if(!inputVal) return alert("Pilih tanggal lahir.");

                if (inputVal === apiVal) {
                    this.step = 4;
                } else {
                    alert("Tanggal lahir tidak cocok dengan data sistem.");
                }
            },

            async submitSurvey() {
                // Validasi Manual
                if (!this.formData.name) return alert("Nama wajib diisi.");
                if (!this.formData.email) return alert("Email wajib diisi.");
                if (!this.formData.division) return alert("Divisi wajib diisi.");
                
                // Validasi Jawaban Survey
                for (let q of this.questions) {
                    if (!this.answers[q.id]) {
                        alert("Mohon lengkapi semua pertanyaan survey.");
                        return;
                    }
                }

                this.isLoading = true;
                
                // Gabungkan data
                const payload = {
                    ...this.formData,
                    company_id: this.selectedCompanyId,
                    company_name: this.selectedCompanyName,
                    answers: this.answers
                };

                try {
                    const res = await fetch('handler.php?action=submit_survey', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();

                    if (json.status === 'success') {
                        this.step = 5;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        alert("Gagal menyimpan: " + (json.message || "Unknown error"));
                    }
                } catch (e) {
                    console.error(e);
                    alert("Terjadi kesalahan sistem.");
                } finally {
                    this.isLoading = false;
                }
            }
        }))
    })
    </script>
</body>
</html>