<?php 
require 'config.php'; 
// Ambil daftar perusahaan untuk Dropdown
$stmt = $pdo->query("SELECT * FROM companies ORDER BY name ASC");
$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        .animate-fade-up { animation: fadeUp 0.5s ease-out forwards; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .focus-pulse:focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); }
        
        /* Modal Animation */
        .modal-enter { animation: modalIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 bg-slate-100">
    <div x-show="showWelcomeModal" style="display: none;" 
     class="fixed inset-0 z-[100] flex items-center justify-center px-4 py-6"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
         @click="showWelcomeModal = false"></div>

    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 flex justify-between items-center shrink-0">
            <h2 class="text-lg font-bold text-white tracking-wide">Pengantar Survey</h2>
            <button @click="showWelcomeModal = false" class="text-white/70 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6 overflow-y-auto text-slate-600 space-y-4 text-sm leading-relaxed custom-scroll">
            
            <p><span class="font-bold text-slate-800">Yth. Bapak/Ibu,</span></p>
            
            <p class="text-justify">
                Divisi ITE senantiasa berupaya untuk bisa memberikan layanan TI yang terbaik bagi kelancaran operasional perusahaan. Oleh karena itu dalam rangka terus dapat memberikan yang terbaik, meningkatkan pelayanan serta memahami kebutuhan layanan dari bapak/ibu semua, kami mengharapkan bapak/ibu dapat meluangkan waktu untuk mengisi kuisioner ini.
            </p>

            <p class="text-justify">
                Kuisioner ini terdiri dari dua bagian, yaitu mengenai layanan TI dan assessment literasi digital bapak/ibu. Kuisioner ini kami susun dengan tujuan untuk bisa mendapat informasi langsung dari bapak/ibu. Hasil dari kuisioner akan sangat bermanfaat bagi kami sebagai masukan dan juga sebagai bahan analisis dan evaluasi untuk bisa terus meningkatkan performa layanan yang kami berikan. Selain itu dari assessment literasi digital akan membantu kami untuk menyusun program-program terkait peningkatan awareness dan literasi digital yang sesuai di Mandirigroup.
            </p>

            <div class="flex items-start gap-3 bg-slate-50 p-3 rounded-lg border border-slate-100">
                <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <p class="text-xs text-slate-500">Informasi data pribadi yang diberikan pada kuisioner ini akan kami <strong>jaga kerahasiaannya</strong>.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 relative group hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-amber-100 text-amber-600 p-1.5 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                        </span>
                        <span class="font-bold text-amber-800 text-sm">Hadiah Menarik!</span>
                    </div>
                    <p class="text-amber-900/80 text-xs">
                        Kami menyiapkan hadiah untuk <strong>10 responden yang beruntung</strong>. Mohon isi data identitas dengan lengkap.
                    </p>
                </div>

                <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 relative group hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-rose-100 text-rose-600 p-1.5 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </span>
                        <span class="font-bold text-rose-800 text-sm">Batas Waktu</span>
                    </div>
                    <p class="text-rose-900/80 text-xs">
                        Pengisian sampai dengan:<br>
                        <strong>28 Februari 2026, Pukul 17.00 WIB</strong>
                    </p>
                </div>
            </div>

            <p class="pt-2 italic text-xs border-t border-slate-100">
                "Akhir kata, kami sampaikan banyak terima kasih atas kontribusi bapak/ibu dalam pengisian kuisioner. Bantu kami untuk bisa lebih baik membantu anda."
                <br><span class="font-semibold not-italic">- Divisi ITE</span>
            </p>
        </div>

        <div class="bg-slate-50 px-6 py-4 flex justify-end shrink-0 border-t border-slate-200">
            <button @click="showWelcomeModal = false" 
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-6 rounded-xl shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center gap-2 text-sm">
                <span>Mulai Mengisi</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </div>
    </div>
</div>

    <div x-data="surveyApp()" class="w-full max-w-2xl bg-white rounded-3xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] overflow-hidden border border-slate-200 relative">
        
        <div class="bg-white px-8 py-6 border-b border-slate-100 relative">
            <button x-show="step > 1" 
                    @click="goBack()" 
                    class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 px-3 py-2 rounded-lg transition-all flex items-center gap-2 text-xs font-bold uppercase tracking-wider group"
                    style="display: none;">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </button>
            <div class="text-center">
                <img src="logo1.png" alt="Logo" class="h-16 mx-auto object-contain hover:scale-105 transition-transform duration-500">
            </div>
        </div>

        <div class="h-1 w-full bg-slate-50">
            <div class="h-full bg-blue-600 transition-all duration-500 ease-out" :style="'width: ' + ((step / 4) * 100) + '%'"></div>
        </div>

        <div class="p-8 sm:p-10 min-h-[350px]">
            
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight" x-text="getTitle()"></h1>
                <p class="text-slate-500 text-sm mt-1" x-text="getSubtitle()"></p>
            </div>

            <div x-show="step === 1" class="animate-fade-up">
                <div class="space-y-6">
                    <div class="relative group">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Unit Bisnis</label>
                        <select x-model="selectedCompanyId" @change="checkCompanyType()" 
                            class="w-full p-4 bg-slate-50 border-2 border-slate-200 rounded-xl appearance-none font-bold text-slate-700 focus:outline-none focus:border-blue-500 focus:bg-white transition-all cursor-pointer shadow-sm group-hover:border-blue-300">
                            <option value="">-- Pilih Perusahaan --</option>
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?php echo $comp['id']; ?>" data-name="<?php echo $comp['name']; ?>">
                                    <?php echo $comp['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute right-4 bottom-4 pointer-events-none text-slate-400 group-hover:text-blue-500 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>

                    <button @click="nextStep()" :disabled="!selectedCompanyId"
                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-slate-200 disabled:text-slate-400 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/30 disabled:shadow-none transition-all transform active:scale-[0.98] mt-4 flex justify-center items-center gap-2">
                        <span>Lanjut</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </button>
                </div>
            </div>

            <div x-show="step === 2" class="animate-fade-up" style="display:none;">
                <div class="space-y-6">
                    <div>
                        <input type="text" x-model="nikInput" @keydown.enter="searchNik()" placeholder="Contoh: 7366" 
                            class="w-full p-4 border-2 border-slate-200 rounded-xl bg-white focus:border-blue-600 outline-none font-bold text-center text-2xl tracking-widest text-slate-800 placeholder-slate-300 transition-colors focus-pulse">
                    </div>
                    
                    <button @click="searchNik()" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/30 transition-all transform active:scale-[0.98] flex justify-center items-center gap-3">
                        <span x-show="!isLoading">Cari Data</span>
                        <span x-show="isLoading">Memproses...</span>
                        <svg x-show="isLoading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>
                </div>
            </div>

            <div x-show="step === 3" class="animate-fade-up" style="display:none;">
                <div class="bg-orange-50 border border-orange-200 rounded-2xl p-8 text-center">
                    <div class="w-12 h-12 bg-white text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    
                    <div class="max-w-xs mx-auto">
                        <label class="block text-xs font-bold text-orange-800/60 uppercase tracking-wider mb-2 text-left">Tanggal Lahir</label>
                        <div class="flex gap-2">
                            <input type="date" x-model="userDobInput" class="flex-1 p-3 border-2 border-orange-200 rounded-xl font-bold text-slate-700 focus:border-orange-500 outline-none bg-white">
                            <button @click="verifyDob()" class="px-6 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-md transition-all">
                                OK
                            </button>
                        </div>
                        <p x-show="verifyError" class="text-red-600 text-xs mt-3 font-bold animate-pulse">
                            Tanggal lahir tidak cocok!
                        </p>
                    </div>
                </div>
            </div>

            <div x-show="step === 4" class="animate-fade-up" style="display:none;">
                <form id="biodataForm" action="form.php" method="POST" class="space-y-5">
                    <input type="hidden" name="company_id" :value="selectedCompanyId">
                    <input type="hidden" name="company_name" :value="selectedCompanyName">
                    <input type="hidden" name="nik" :value="formData.nik">

                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Perusahaan</p>
                            <p class="font-bold text-slate-700 text-sm truncate" x-text="selectedCompanyName"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">NIK</p>
                            <p class="font-bold text-slate-700 text-sm" x-text="formData.nik"></p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 tracking-widest mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="formData.name" :readonly="mode === 'api'"
                            class="w-full py-3 px-0 bg-transparent border-b-2 border-slate-200 font-bold text-slate-800 text-lg outline-none focus:border-blue-500 transition-colors placeholder-slate-300"
                            :class="{'cursor-not-allowed text-slate-500': mode === 'api'}">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 tracking-widest mb-1">Email</label>
                        <input type="email" name="email" x-model="formData.email" :readonly="mode === 'api' && !!formData.email"
                            class="w-full py-3 px-0 bg-transparent border-b-2 border-slate-200 font-bold text-slate-800 outline-none focus:border-blue-500 transition-colors placeholder-slate-300"
                            :class="{'cursor-not-allowed text-slate-500': mode === 'api' && !!formData.email}">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 tracking-widest mb-1">Divisi</label>
                        <input type="text" name="division" x-model="formData.division" :readonly="mode === 'api'"
                            class="w-full py-3 px-0 bg-transparent border-b-2 border-slate-200 font-bold text-slate-800 outline-none focus:border-blue-500 transition-colors placeholder-slate-300"
                            :class="{'cursor-not-allowed text-slate-500': mode === 'api'}">
                    </div>
                    
                    <div class="pt-4">
                        <button type="submit" :disabled="!isFormValid()"
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:from-slate-300 disabled:to-slate-400 disabled:cursor-not-allowed text-white text-lg font-bold py-4 rounded-xl shadow-lg shadow-blue-500/30 disabled:shadow-none transform hover:-translate-y-1 transition-all flex justify-center items-center gap-3">
                            <span>MULAI SURVEY</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showModal" style="display: none;" 
            class="absolute inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center transform transition-all modal-enter relative overflow-hidden">
                
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                     :class="modalType === 'error' ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-500'">
                    
                    <svg x-show="modalType === 'error'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    
                    <svg x-show="modalType === 'warning'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>

                <h3 class="text-lg font-bold text-slate-800 mb-2" x-text="modalTitle"></h3>
                <p class="text-sm text-slate-500 mb-6 leading-relaxed" x-text="modalMessage"></p>

                <button @click="showModal = false" 
                    class="w-full py-3 rounded-xl font-bold text-sm transition-colors"
                    :class="modalType === 'error' ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-slate-800 hover:bg-slate-900 text-white'">
                    OK, Saya Mengerti
                </button>
            </div>
        </div>

    </div>
    
    <div class="mt-6 text-center text-slate-400 text-xs font-medium">
        &copy; <?php echo date('Y'); ?> IT Operation Dept.
    </div>
        <script>
            function surveyApp() {
                return {
                    // --- [BARU] STATUS MODAL POPUP ---
                    showWelcomeModal: true, 
                    // ---------------------------------

                    step: 1, 
                    mode: 'api', 
                    selectedCompanyId: '',
                    selectedCompanyName: '',
                    nikInput: '',
                    userDobInput: '',
                    apiDobCheck: '',
                    isLoading: false,
                    errorMessage: '',
                    verifyError: false,
                    formData: { nik: '', name: '', email: '', division: '' },
                    
                    // Modal States (Error/Warning Alert) - INI YANG LAMA (JANGAN DIHAPUS)
                    showModal: false,
                    modalType: 'error', 
                    modalTitle: '',
                    modalMessage: '',

                    // Fungsi Helper untuk Trigger Modal Alert
                    triggerAlert(type, title, message) {
                        this.modalType = type;
                        this.modalTitle = title;
                        this.modalMessage = message;
                        this.showModal = true;
                    },

                    goBack() {
                        this.resetForm();
                        this.step = 1;
                    },

                    resetForm() {
                        this.nikInput = '';
                        this.userDobInput = '';
                        this.apiDobCheck = '';
                        this.errorMessage = '';
                        this.verifyError = false;
                        this.formData = { nik: '', name: '', email: '', division: '' };
                    },

                    getTitle() {
                        if(this.step === 1) return "IT Satisfaction Survey";
                        if(this.step === 2) return "Pencarian NIK";
                        if(this.step === 3) return "Verifikasi Keamanan";
                        if(this.step === 4) return "Konfirmasi Data";
                    },

                    getSubtitle() {
                        if(this.step === 1) return "Silakan pilih unit bisnis Anda untuk memulai.";
                        if(this.step === 2) return "Masukkan NIK karyawan untuk validasi data.";
                        if(this.step === 3) return "Mohon konfirmasi tanggal lahir Anda.";
                        if(this.step === 4) return "Pastikan data diri Anda sudah benar.";
                    },
                    
                    checkCompanyType() {
                        const select = document.querySelector('select');
                        const option = select.options[select.selectedIndex];
                        if(option.value === "") {
                            this.selectedCompanyId = "";
                            this.selectedCompanyName = "";
                            return;
                        }
                        this.selectedCompanyName = option.getAttribute('data-name');
                        if (this.selectedCompanyName && this.selectedCompanyName.toLowerCase().includes('mandiriland')) {
                            this.mode = 'manual';
                        } else {
                            this.mode = 'api';
                        }
                    },

                    nextStep() {
                        if (this.mode === 'manual') {
                            this.formData.nik = '-'; 
                            this.step = 4;
                        } else {
                            this.step = 2;
                        }
                    },

                    async searchNik() {
                        if (!this.nikInput) return;
                        this.isLoading = true;
                        this.errorMessage = '';

                        try {
                            const res = await fetch(`handler.php?action=search_nik&nik=${this.nikInput}`);
                            const json = await res.json();
                            
                            if (json.status === 'success') {
                                const d = json.data;
                                
                                // VALIDASI CROSS-CHECK COMPANY
                                let userComp = this.selectedCompanyName.toLowerCase().replace(/pt\.?\s*/g, '').trim();
                                let apiComp = (d.company_name || '').toLowerCase().replace(/pt\.?\s*/g, '').trim();
                                const isMatch = apiComp.includes(userComp) || userComp.includes(apiComp);

                                if (!isMatch) {
                                    this.triggerAlert(
                                        'error', 
                                        'Data Tidak Sesuai', 
                                        `NIK ${this.nikInput} terdaftar di "${d.company_name}", sedangkan Anda memilih "${this.selectedCompanyName}". Mohon periksa kembali pilihan Anda.`
                                    );
                                    
                                    this.isLoading = false;
                                    this.nikInput = ''; 
                                    return; 
                                }

                                this.formData = {
                                    nik: this.nikInput,
                                    name: d.name,
                                    email: d.email,
                                    division: d.division
                                };
                                this.apiDobCheck = d.dob_check;
                                
                                if (this.apiDobCheck) {
                                    this.step = 3; 
                                } else {
                                    this.triggerAlert(
                                        'warning', 
                                        'Data Belum Lengkap', 
                                        'Data keamanan karyawan ini belum lengkap di sistem SAP. Silakan lanjutkan pengisian data secara manual.'
                                    );
                                    
                                    this.mode = 'manual';
                                    this.formData.nik = this.nikInput; 
                                    this.step = 4;
                                }
                            } else {
                                this.triggerAlert('error', 'Tidak Ditemukan', 'NIK yang Anda masukkan tidak terdaftar dalam database kami.');
                            }
                        } catch (e) {
                            this.errorMessage = "Gagal koneksi server.";
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    verifyDob() {
                        let inputVal = String(this.userDobInput).trim();
                        let apiVal = String(this.apiDobCheck).trim();

                        if (inputVal === apiVal) {
                            this.step = 4; 
                            this.verifyError = false;
                        } else {
                            this.verifyError = true;
                        }
                    },

                    isFormValid() {
                        return this.formData.name && this.formData.email && this.selectedCompanyId;
                    }
                }
            }
        </script>
</body>
</html>