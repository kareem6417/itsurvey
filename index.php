<?php 
require 'config.php'; 
// Ambil daftar perusahaan
$stmt = $pdo->query("SELECT * FROM companies ORDER BY name ASC");
$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Layanan TI - Mandirigroup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        
        /* Modal Animation */
        .modal-enter { opacity: 0; transform: scale(0.95); }
        .modal-enter-active { opacity: 1; transform: scale(1); transition: all 0.3s ease-out; }
        .modal-leave { opacity: 1; transform: scale(1); }
        .modal-leave-active { opacity: 0; transform: scale(0.95); transition: all 0.2s ease-in; }
        
        /* Custom Scrollbar for Modal Text */
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body x-data="surveyLogin()" class="min-h-screen flex items-center justify-center p-4">

    <div x-show="showWelcomeModal" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center px-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showWelcomeModal = false"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden"
             x-transition:enter="modal-enter-active"
             x-transition:enter-start="modal-enter"
             x-transition:enter-end="modal-enter-active"
             x-transition:leave="modal-leave-active"
             x-transition:leave-start="modal-leave"
             x-transition:leave-end="modal-leave-active">
            
            <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center shrink-0">
                <h3 class="text-white font-bold text-lg">Pengantar Survey</h3>
                <button @click="showWelcomeModal = false" class="text-white/80 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto custom-scroll text-slate-700 text-sm leading-relaxed space-y-4">
                <p class="font-bold">Yth Bapak/Ibu</p>
                
                <p class="text-justify">
                    Divisi ITE senantiasa berupaya untuk bisa memberikan layanan TI yang terbaik bagi kelancaran operasional perusahaan. Oleh karena itu dalam rangka terus dapat memberikan yang terbaik, meningkatkan pelayanan serta memahami kebutuhan layanan dari bapak/ibu semua, kami mengharapkan bapak/ibu dapat meluangkan waktu untuk mengisi kuisioner ini.
                </p>

                <p class="text-justify">
                    Kuisioner ini terdiri dari dua bagian, yaitu mengenai layanan TI dan assessment literasi digital bapak/ibu. Kuisioner ini kami susun dengan tujuan untuk bisa mendapat informasi langsung dari bapak/ibu. Hasil dari kuisioner akan sangat bermanfaat bagi kami sebagai masukan dan juga sebagai bahan analisis dan evaluasi untuk bisa terus meningkatkan performa layanan yang kami berikan. Selain itu dari assessment literasi digital akan membantu kami untuk menyusun program-program terkait peningkatan awareness dan literasi digital yang sesuai di Mandirigroup.
                </p>

                <p class="bg-blue-50 text-blue-800 p-3 rounded-lg border border-blue-100 font-medium">
                    Informasi data pribadi yang diberikan pada kuisioner ini akan kami jaga kerahasiaannya.
                </p>

                <p class="text-justify">
                    Kami menyiapkan hadiah untuk <strong>10 orang responden yang beruntung</strong>. Oleh karena itu, mohon untuk dapat mengisikan informasi data identitas secara lengkap dan benar, sehingga kami bisa menghubungi bapak/ibu kembali.
                </p>

                <p class="text-red-600 font-semibold">
                    Waktu pengisian kuisioner sampai dengan tanggal 28 Februari 2025 pukul 17.00 WIB.
                </p>

                <p>
                    Akhir kata, kami sampaikan banyak terima kasih atas kontribusi bapak/ibu dalam pengisian kuisioner. Bantu kami untuk bisa lebih baik membantu anda.
                </p>

                <div class="pt-2">
                    <p class="font-bold">Terima kasih</p>
                    <p class="font-semibold text-indigo-600">Divisi ITE</p>
                </div>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end shrink-0">
                <button @click="showWelcomeModal = false" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-indigo-200">
                    Mulai Mengisi
                </button>
            </div>
        </div>
    </div>


    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
        
        <div class="bg-white p-8 pb-4 text-center">
            <img src="logo1.png" alt="Logo" class="h-12 mx-auto mb-4 object-contain">
            <h1 class="text-2xl font-bold text-slate-800">Survey Kepuasan IT</h1>
            <p class="text-slate-500 text-sm mt-1">Silakan lengkapi data diri Anda</p>
        </div>

        <div class="p-8 pt-4">
            
            <div x-show="errorMessage" style="display: none;" class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-text="errorMessage"></span>
            </div>

            <div x-show="step === 1">
                <label class="block text-slate-700 text-sm font-bold mb-2">Unit Bisnis / Perusahaan</label>
                <select x-model="selectedCompanyId" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none">
                    <option value="">-- Pilih --</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?php echo $comp['id']; ?>" data-name="<?php echo htmlspecialchars($comp['name']); ?>">
                            <?php echo htmlspecialchars($comp['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button @click="goToStep2" :disabled="!selectedCompanyId"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl mt-6 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Lanjut
                </button>
            </div>

            <div x-show="step === 2" style="display: none;">
                <button @click="step = 1" class="text-slate-400 hover:text-indigo-600 text-sm mb-4 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg> Kembali
                </button>
                
                <label class="block text-slate-700 text-sm font-bold mb-2">NIK / NRP</label>
                <input type="number" x-model="nikInput" @keydown.enter="checkNik"
                    class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none" 
                    placeholder="Contoh: 12345678">

                <button @click="checkNik" :disabled="!nikInput || isLoading"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl mt-6 transition disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2">
                    <span x-show="!isLoading">Cek Data</span>
                    <span x-show="isLoading">Memproses...</span>
                </button>
            </div>

            <div x-show="step === 3" style="display: none;">
                <button @click="step = 2" class="text-slate-400 hover:text-indigo-600 text-sm mb-4 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg> Kembali
                </button>

                <div class="bg-blue-50 p-3 rounded-lg text-sm text-blue-800 mb-4">
                    Halo <strong><span x-text="formData.name"></span></strong>, demi keamanan, masukkan tanggal lahir Anda.
                </div>

                <label class="block text-slate-700 text-sm font-bold mb-2">Tanggal Lahir</label>
                <input type="date" x-model="userDobInput" 
                    class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none">

                <p x-show="verifyError" class="text-red-500 text-xs mt-2">Tanggal lahir tidak sesuai.</p>

                <button @click="verifyDob" :disabled="!userDobInput"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl mt-6 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Verifikasi
                </button>
            </div>

            <div x-show="step === 4" style="display: none;">
                <button @click="mode === 'manual' ? step = 2 : step = 3" class="text-slate-400 hover:text-indigo-600 text-sm mb-4 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg> Kembali
                </button>

                <div x-show="mode === 'manual'" class="bg-amber-50 text-amber-800 text-sm p-3 rounded-lg mb-4">
                    Data NIK belum tersedia otomatis. Silakan lengkapi manual.
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Perusahaan</label>
                        <input type="text" x-model="selectedCompanyName" readonly class="w-full bg-slate-100 border border-slate-200 rounded p-2 text-sm text-slate-600">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">NIK</label>
                        <input type="text" x-model="formData.nik" readonly class="w-full bg-slate-100 border border-slate-200 rounded p-2 text-sm text-slate-600 font-mono">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Nama</label>
                        <input type="text" x-model="formData.name" :readonly="mode === 'auto'" class="w-full border border-slate-300 rounded p-2 text-sm text-slate-800 focus:border-indigo-500 outline-none" :class="mode === 'auto' ? 'bg-slate-100' : 'bg-white'">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Email</label>
                        <input type="email" x-model="formData.email" :readonly="mode === 'auto'" class="w-full border border-slate-300 rounded p-2 text-sm text-slate-800 focus:border-indigo-500 outline-none" :class="mode === 'auto' ? 'bg-slate-100' : 'bg-white'">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase">Divisi</label>
                        <input type="text" x-model="formData.division" :readonly="mode === 'auto'" class="w-full border border-slate-300 rounded p-2 text-sm text-slate-800 focus:border-indigo-500 outline-none" :class="mode === 'auto' ? 'bg-slate-100' : 'bg-white'">
                    </div>
                </div>

                <form action="form.php" method="POST" class="mt-6">
                    <input type="hidden" name="company_id" x-model="selectedCompanyId">
                    <input type="hidden" name="company_name" x-model="selectedCompanyName">
                    <input type="hidden" name="nik" x-model="formData.nik">
                    <input type="hidden" name="name" x-model="formData.name">
                    <input type="hidden" name="email" x-model="formData.email">
                    <input type="hidden" name="division" x-model="formData.division">

                    <button type="submit" :disabled="!formData.name || !formData.email"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-green-200 disabled:opacity-50">
                        Mulai Isi Survey
                    </button>
                </form>
            </div>

        </div>
        <div class="bg-slate-50 p-4 text-center border-t border-slate-100 text-xs text-slate-400">
            &copy; 2026 Mandirigroup IT Division
        </div>
    </div>

    <script>
    function surveyLogin() {
        return {
            step: 1,
            isLoading: false,
            mode: 'auto', 
            showWelcomeModal: true, // POPUP AKTIF DISINI
            errorMessage: '',

            selectedCompanyId: '',
            selectedCompanyName: '',
            nikInput: '',
            userDobInput: '', 
            apiDobCheck: '',  
            
            formData: { nik: '', name: '', email: '', division: '' },
            verifyError: false,

            goToStep2() {
                const select = document.querySelector('select');
                this.selectedCompanyName = select.options[select.selectedIndex].getAttribute('data-name');
                this.step = 2;
                this.errorMessage = '';
            },

            async checkNik() {
                if (!this.nikInput) return;
                this.isLoading = true;
                this.errorMessage = '';

                try {
                    const response = await fetch(`handler.php?action=search_nik&nik=${this.nikInput}`);
                    const result = await response.json();

                    if (result.status === 'success') {
                        const data = result.data;
                        this.formData.nik = data.nik;
                        this.formData.name = data.name;
                        this.formData.email = data.email;
                        this.formData.division = data.division;
                        this.apiDobCheck = data.dob_check; 
                        this.mode = 'auto';
                        this.step = 3;
                    } else if (result.status === 'error') {
                        if (result.message.includes('tidak ditemukan')) {
                            this.mode = 'manual';
                            this.formData.nik = this.nikInput;
                            this.step = 4;
                        } else {
                            this.errorMessage = result.message;
                        }
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
            }
        }
    }
    </script>
</body>
</html>