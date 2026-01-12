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
        
        /* Scrollbar untuk Modal */
        .modal-scroll::-webkit-scrollbar { width: 6px; }
        .modal-scroll::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .modal-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body x-data="surveyLogin()" class="bg-slate-50 min-h-screen flex items-center justify-center p-4 relative">

    <div x-show="showWelcomeModal" 
         style="display: none;"
         class="fixed inset-0 z-[999] flex items-center justify-center px-4 py-6"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" @click="showWelcomeModal = false"></div>

        <div class="relative w-full max-w-3xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-5 flex justify-between items-center shrink-0">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg backdrop-blur-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-xl font-bold text-white tracking-wide">Pengantar Survey Layanan TI</h2>
                </div>
                <button @click="showWelcomeModal = false" class="text-white/70 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-8 overflow-y-auto modal-scroll text-slate-600 space-y-5 text-sm sm:text-base leading-relaxed">
                
                <div>
                    <p class="font-bold text-slate-800 text-lg mb-2">Yth. Bapak/Ibu,</p>
                    <p>Divisi ITE senantiasa berupaya memberikan layanan TI terbaik bagi kelancaran operasional perusahaan. Demi meningkatkan kualitas pelayanan dan memahami kebutuhan Anda, kami mengharapkan kesediaan Bapak/Ibu meluangkan waktu sejenak untuk mengisi kuesioner ini.</p>
                </div>

                <div class="bg-slate-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                    <p class="font-semibold text-slate-800 mb-1">Tujuan Kuesioner:</p>
                    <ul class="list-disc list-inside space-y-1 ml-1 text-slate-600">
                        <li>Evaluasi performa layanan TI & Literasi Digital.</li>
                        <li>Masukan langsung untuk analisis dan peningkatan layanan.</li>
                        <li>Penyusunan program peningkatan <i>awareness</i> digital di Mandirigroup.</li>
                    </ul>
                </div>

                <div class="flex items-start gap-3 text-slate-500 text-sm">
                    <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <p>Informasi data pribadi yang Anda berikan akan kami <strong>jaga kerahasiaannya</strong>.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 bg-amber-100 w-16 h-16 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative z-10 flex flex-col h-full justify-between">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-amber-100 text-amber-600 p-1.5 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                                </span>
                                <span class="font-bold text-amber-800">Doorprize Menarik!</span>
                            </div>
                            <p class="text-amber-900/80 text-sm">
                                Tersedia hadiah untuk <strong>10 responden beruntung</strong>. Mohon isi data identitas dengan lengkap dan benar agar dapat kami hubungi.
                            </p>
                        </div>
                    </div>

                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 bg-rose-100 w-16 h-16 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative z-10 flex flex-col h-full justify-between">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-rose-100 text-rose-600 p-1.5 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </span>
                                <span class="font-bold text-rose-800">Batas Waktu</span>
                            </div>
                            <p class="text-rose-900/80 text-sm">
                                Pengisian kuesioner ditutup pada:<br>
                                <strong>31 Januari 2024, Pukul 17.00 WIB</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <p class="text-slate-600 text-sm pt-2 italic border-t border-slate-100">
                    "Terima kasih atas kontribusi Bapak/Ibu. Bantu kami untuk bisa lebih baik membantu Anda." <br>
                    <span class="font-semibold not-italic">- Divisi ITE</span>
                </p>

            </div>

            <div class="bg-slate-50 px-8 py-5 flex justify-end shrink-0 border-t border-slate-200">
                <button @click="showWelcomeModal = false" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-8 rounded-xl shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center gap-2">
                    <span>Mulai Pengisian</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </div>
    </div>
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    </div>

    <div class="w-full max-w-md bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-white/50 relative z-10 animate-fade-up">
        
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-8 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            <img src="logo1.png" alt="Mandiri Coal Logo" class="h-12 mx-auto mb-4 drop-shadow-lg object-contain relative z-10 hover:scale-105 transition-transform duration-300">
            <h1 class="text-2xl font-bold text-white tracking-tight relative z-10">Survey Kepuasan IT</h1>
            <p class="text-blue-100 text-sm mt-1 relative z-10">Silakan masuk untuk memulai survey</p>
        </div>

        <div class="p-8">
            <div x-show="step === 1">
                <label class="block text-slate-700 text-sm font-bold mb-3">Pilih Unit Bisnis / Perusahaan</label>
                <div class="space-y-3">
                    <select x-model="selectedCompanyId" class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5 transition-all outline-none font-medium">
                        <option value="">-- Pilih Perusahaan --</option>
                        <?php foreach ($companies as $comp): ?>
                            <option value="<?php echo $comp['id']; ?>" data-name="<?php echo htmlspecialchars($comp['name']); ?>">
                                <?php echo htmlspecialchars($comp['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button @click="goToStep2" :disabled="!selectedCompanyId"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed mt-4 flex justify-center items-center gap-2">
                        <span>Lanjut</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

            <div x-show="step === 2" style="display: none;">
                <button @click="step = 1" class="text-slate-400 hover:text-indigo-600 text-sm flex items-center gap-1 mb-4 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali
                </button>
                
                <label class="block text-slate-700 text-sm font-bold mb-2">Masukkan NIK / NRP Anda</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.956 2.05-2.5 3.05C8.09 10.2 7 12 7 14v1h10v-1c0-2-1.09-3.8-3.5-5.95C11.956 8.05 11 6.884 11 6z"/></svg>
                    </div>
                    <input type="number" x-model="nikInput" @keydown.enter="checkNik" 
                        class="pl-10 w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5 outline-none transition-all focus-pulse" 
                        placeholder="Contoh: 12345678">
                </div>

                <div x-show="isLoading" class="mt-4 flex justify-center">
                    <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>

                <button @click="checkNik" :disabled="!nikInput || isLoading" x-show="!isLoading"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed mt-4">
                    Cek Data
                </button>
            </div>

            <div x-show="step === 3" style="display: none;">
                <button @click="step = 2" class="text-slate-400 hover:text-indigo-600 text-sm flex items-center gap-1 mb-4 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali
                </button>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">Halo <span x-text="formData.name" class="font-bold"></span>, demi keamanan data, silakan verifikasi tanggal lahir Anda.</p>
                        </div>
                    </div>
                </div>

                <label class="block text-slate-700 text-sm font-bold mb-2">Tanggal Lahir</label>
                <input type="date" x-model="userDobInput" 
                    class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5 outline-none transition-all">

                <p x-show="verifyError" class="text-red-500 text-xs mt-2 flex items-center gap-1 animate-pulse">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tanggal lahir tidak sesuai dengan data NIK.
                </p>

                <button @click="verifyDob" :disabled="!userDobInput"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed mt-4">
                    Verifikasi & Lanjut
                </button>
            </div>

            <div x-show="step === 4" style="display: none;">
                <button @click="mode === 'manual' ? step = 2 : step = 3" class="text-slate-400 hover:text-indigo-600 text-sm flex items-center gap-1 mb-4 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali
                </button>

                <div x-show="mode === 'manual'" class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6 rounded-r-lg">
                    <p class="text-sm text-amber-700 font-medium">Data NIK Anda belum terdaftar di sistem otomatis kami. Silakan lengkapi data di bawah ini secara manual.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">NIK</label>
                        <input type="text" x-model="formData.nik" readonly class="w-full bg-slate-100 border border-slate-200 text-slate-500 text-sm rounded-lg p-3 cursor-not-allowed font-mono">
                    </div>

                    <div>
                        <label class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Perusahaan</label>
                        <input type="text" x-model="selectedCompanyName" readonly class="w-full bg-slate-100 border border-slate-200 text-slate-500 text-sm rounded-lg p-3 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-slate-700 text-xs font-bold uppercase tracking-wider mb-1">Nama Lengkap</label>
                        <input type="text" x-model="formData.name" :readonly="mode === 'auto'" 
                            :class="mode === 'auto' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-indigo-500 focus:border-indigo-500'"
                            class="w-full border border-slate-300 text-sm rounded-lg p-3 outline-none">
                    </div>

                    <div>
                        <label class="block text-slate-700 text-xs font-bold uppercase tracking-wider mb-1">Email Kantor</label>
                        <input type="email" x-model="formData.email" :readonly="mode === 'auto'"
                            :class="mode === 'auto' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-indigo-500 focus:border-indigo-500'"
                            class="w-full border border-slate-300 text-sm rounded-lg p-3 outline-none">
                    </div>

                    <div>
                        <label class="block text-slate-700 text-xs font-bold uppercase tracking-wider mb-1">Divisi</label>
                        <input type="text" x-model="formData.division" :readonly="mode === 'auto'"
                            :class="mode === 'auto' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-indigo-500 focus:border-indigo-500'"
                            class="w-full border border-slate-300 text-sm rounded-lg p-3 outline-none">
                    </div>
                </div>

                <form action="form.php" method="POST" class="mt-8">
                    <input type="hidden" name="company_id" x-model="selectedCompanyId">
                    <input type="hidden" name="company_name" x-model="selectedCompanyName">
                    <input type="hidden" name="nik" x-model="formData.nik">
                    <input type="hidden" name="name" x-model="formData.name">
                    <input type="hidden" name="email" x-model="formData.email">
                    <input type="hidden" name="division" x-model="formData.division">

                    <button type="submit" :disabled="!isFormValid()"
                        class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-4 px-4 rounded-xl shadow-lg shadow-emerald-500/30 transition-all transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2">
                        <span>Mulai Isi Survey</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7"></path></svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-slate-50 p-4 text-center border-t border-slate-100">
            <p class="text-xs text-slate-400 font-medium">&copy; 2026 Mandirigroup IT Division. All rights reserved.</p>
        </div>
    </div>

    <div x-show="showAlert" style="display: none;" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl transform transition-all"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             @click.away="showAlert = false">
            
            <div class="flex items-center justify-center w-12 h-12 rounded-full mb-4 mx-auto"
                :class="alertType === 'success' ? 'bg-green-100 text-green-600' : (alertType === 'error' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600')">
                <svg x-show="alertType === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <svg x-show="alertType === 'error'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                <svg x-show="alertType === 'info'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            
            <h3 class="text-lg font-bold text-center text-slate-800 mb-2" x-text="alertTitle"></h3>
            <p class="text-sm text-center text-slate-500 mb-6 leading-relaxed" x-text="alertMessage"></p>
            
            <button @click="showAlert = false" 
                class="w-full py-2.5 rounded-xl font-bold transition-colors"
                :class="alertType === 'success' ? 'bg-green-600 hover:bg-green-700 text-white' : (alertType === 'error' ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white')">
                Tutup
            </button>
        </div>
    </div>

    <script>
    function surveyLogin() {
        return {
            step: 1,
            isLoading: false,
            mode: 'auto', 
            
            // --- STATE UNTUK MODAL SAMBUTAN ---
            showWelcomeModal: true, // Default true agar muncul saat load

            // Form Data
            selectedCompanyId: '',
            selectedCompanyName: '',
            nikInput: '',
            userDobInput: '', 
            apiDobCheck: '',  
            
            formData: {
                nik: '',
                name: '',
                email: '',
                division: ''
            },

            verifyError: false,

            // Alert State
            showAlert: false,
            alertType: 'info',
            alertTitle: '',
            alertMessage: '',

            triggerAlert(type, title, message) {
                this.alertType = type;
                this.alertTitle = title;
                this.alertMessage = message;
                this.showAlert = true;
            },

            goToStep2() {
                const select = document.querySelector('select');
                const option = select.options[select.selectedIndex];
                this.selectedCompanyName = option.getAttribute('data-name');
                this.step = 2;
            },

            async checkNik() {
                if (!this.nikInput) return;
                this.isLoading = true;

                try {
                    const response = await fetch(`handler.php?action=search_nik&nik=${this.nikInput}`);
                    const result = await response.json();

                    if (result.status === 'success') {
                        // Data ditemukan di API
                        const data = result.data;
                        
                        // Cek apakah company match? (Opsional, saat ini kita percaya user milih company di awal)
                        // Logika: Jika user pilih "MIP" tapi NIK-nya "MKP", apakah boleh?
                        // Untuk fleksibilitas, kita biarkan saja dulu, atau berikan warning.
                        // Disini kita set data form dari API
                        
                        this.formData.nik = data.nik;
                        this.formData.name = data.name;
                        this.formData.email = data.email;
                        this.formData.division = data.division;
                        
                        // Simpan Kunci Jawaban Tanggal Lahir
                        this.apiDobCheck = data.dob_check; 

                        this.mode = 'auto';
                        this.step = 3; // Masuk ke Step Verifikasi DOB
                    } else if (result.status === 'error') {
                        if (result.message.includes('tidak ditemukan')) {
                            // NIK TIDAK ADA DI API -> MODE MANUAL
                            // Kita gunakan Custom Alert Info
                            this.triggerAlert(
                                'info', 
                                'Data Belum Lengkap', 
                                'Data keamanan karyawan ini belum lengkap di sistem SAP. Silakan lanjutkan pengisian data secara manual.'
                            );
                            
                            this.mode = 'manual';
                            this.formData.nik = this.nikInput; 
                            this.step = 4;
                        }
                    } else {
                        // MENGGUNAKAN CUSTOM MODAL (ERROR - Not Found)
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