<?php
require __DIR__ . '/session_check.php';
$userRole = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Bimbingan TA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
    }

    .chart-container {
        position: relative;
        height: 40vh;
        width: 100%;
    }

    .sortable {
        cursor: pointer;
        user-select: none;
    }

    .sort-indicator {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1em;
        height: 1em;
        margin-left: 0.5rem;
        color: #64748b;
    }

    .dropdown:hover .dropdown-menu {
        display: block;
    }

    .dropdown-menu {
        display: none;
    }
    </style>
</head>

<body class="text-slate-600 antialiased">

    <div id="loading-overlay"
        class="fixed inset-0 bg-white bg-opacity-90 flex flex-col items-center justify-center z-[100] transition-opacity duration-300">
        <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <p class="mt-4 text-lg font-semibold text-slate-700">Memuat data...</p>
    </div>

    <div class="flex min-h-screen bg-gray-100">
        <aside id="sidebar"
            class="bg-white w-64 min-h-screen flex-col fixed inset-y-0 left-0 z-50 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out border-r border-gray-200">
            <div class="p-4 flex flex-col h-full">
                <div class="flex items-center gap-3 mb-8">
                    <div class="bg-indigo-600 text-white p-2 rounded-lg"><i data-lucide="book-marked"></i></div>
                    <h1 class="text-xl font-bold text-slate-800">Dasbor Bimbingan TA</h1>
                </div>
                <nav class="space-y-2 flex-grow">
                    <a href="#overview"
                        class="flex items-center gap-3 px-4 py-2 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition-colors"><i
                            data-lucide="layout-dashboard" class="w-5 h-5"></i><span>Ringkasan</span></a>
                    <?php if ($userRole === 'dosen'): ?>
                    <a href="#dosen-view"
                        class="flex items-center gap-3 px-4 py-2 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition-colors"><i
                            data-lucide="users" class="w-5 h-5"></i><span>Mhs Bimbingan</span></a>
                    <?php endif; ?>
                    <?php if ($userRole === 'admin' || $userRole === 'kaprodi'): ?>
                    <a href="#insights"
                        class="flex items-center gap-3 px-4 py-2 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition-colors"><i
                            data-lucide="siren" class="w-5 h-5"></i><span>Insight & Distribusi</span></a>
                    <a href="#manajemen"
                        class="flex items-center gap-3 px-4 py-2 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition-colors"><i
                            data-lucide="file-cog" class="w-5 h-5"></i><span>Manajemen TA</span></a>
                    <?php endif; ?>
                    <a href="#topik"
                        class="flex items-center gap-3 px-4 py-2 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition-colors"><i
                            data-lucide="lightbulb" class="w-5 h-5"></i><span>Analisis Topik</span></a>
                    <div class="mt-auto">
                        <div class="p-3 bg-gray-100 rounded-lg mb-4">
                            <p class="text-sm font-semibold text-slate-800">
                                <?php echo htmlspecialchars($_SESSION['display_name']); ?></p>
                            <p class="text-xs text-indigo-600 font-bold uppercase">
                                <?php echo htmlspecialchars($userRole); ?></p>
                        </div>
                        <a href="auth.php?action=logout"
                            class="flex items-center justify-center w-full gap-3 px-4 py-2 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"><i
                                data-lucide="log-out" class="w-5 h-5"></i><span>Keluar</span></a>
                    </div>
                </nav>
            </div>
        </aside>

        <div class="flex-1 flex flex-col">
            <header
                class="md:hidden bg-white p-4 flex justify-between items-center border-b border-gray-200 sticky top-0 z-40">
                <h1 class="text-lg font-bold text-slate-800">Dasbor Bimbingan TA</h1><button id="menu-toggle"
                    class="p-2"><i data-lucide="menu"></i></button>
            </header>
            <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-8">
                <section id="overview"></section>
                <?php if ($userRole === 'dosen'): ?><section id="dosen-view"></section><?php endif; ?>
                <?php if ($userRole === 'admin' || $userRole === 'kaprodi'): ?><section id="insights"></section>
                <section id="manajemen"></section><?php endif; ?>
                <section id="topik"></section>
            </main>
        </div>
    </div>

    <!-- Modal hanya ada untuk admin/kaprodi -->
    <?php if ($userRole === 'admin' || $userRole === 'kaprodi'): ?>
    <div id="skripsi-modal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4"
        style="background-color: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);">
        <div id="modal-content"
            class="bg-white rounded-lg shadow-2xl w-full max-w-2xl flex flex-col transform transition-all scale-95 opacity-0 max-h-[90vh]">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 id="modal-title" class="text-xl font-bold text-slate-800"></h3><button id="btn-cancel"
                        class="p-1 rounded-full hover:bg-gray-200 text-slate-500"><i data-lucide="x"
                            class="w-5 h-5"></i></button>
                </div>
            </div>
            <div class="p-6 flex-grow overflow-y-auto">
                <form id="skripsi-form" autocomplete="off" class="space-y-4">
                    <input type="hidden" id="form-nim-original">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><label for="form-nim" class="font-medium text-slate-700 block mb-1">NIM</label><input
                                id="form-nim" type="text" placeholder="Nomor Induk Mahasiswa" required
                                class="w-full bg-gray-50 p-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div><label for="form-nama" class="font-medium text-slate-700 block mb-1">Nama
                                Mahasiswa</label><input id="form-nama" type="text" placeholder="Nama Lengkap" required
                                class="w-full bg-gray-50 p-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div class="sm:col-span-2"><label for="form-judul"
                                class="font-medium text-slate-700 block mb-1">Judul Tugas Akhir</label><textarea
                                id="form-judul" placeholder="Judul Lengkap Tugas Akhir" required rows="3"
                                class="w-full bg-gray-50 p-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                        </div>
                        <div><label for="form-p1" class="font-medium text-slate-700 block mb-1">Pembimbing
                                1</label><select id="form-p1"
                                class="w-full bg-gray-50 p-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                            </select></div>
                        <div><label for="form-p2" class="font-medium text-slate-700 block mb-1">Pembimbing
                                2</label><select id="form-p2"
                                class="w-full bg-gray-50 p-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                            </select></div>
                        <div><label for="form-u1" class="font-medium text-slate-700 block mb-1">Penguji 1</label><select
                                id="form-u1"
                                class="w-full bg-gray-50 p-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                            </select></div>
                        <div><label for="form-u2" class="font-medium text-slate-700 block mb-1">Penguji 2</label><select
                                id="form-u2"
                                class="w-full bg-gray-50 p-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                            </select></div>
                        <div><label for="form-tgl-sk" class="font-medium text-slate-700 block mb-1">Tgl SK</label><input
                                id="form-tgl-sk" type="date"
                                class="w-full bg-gray-50 p-2.5 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div><label for="form-tgl-lulus" class="font-medium text-slate-700 block mb-1">Tgl
                                Lulus</label><input id="form-tgl-lulus" type="date"
                                class="w-full bg-gray-50 p-2.5 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div><label for="form-status"
                                class="font-medium text-slate-700 block mb-1">Status</label><select id="form-status"
                                required
                                class="w-full bg-gray-50 p-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <option value="Belum Lulus">Belum Lulus</option>
                                <option value="Lulus">Lulus</option>
                                <option value="Data Tidak Lengkap">Data Tidak Lengkap</option>
                            </select></div>
                        <div><label for="form-jumlah-pengambilan"
                                class="font-medium text-slate-700 block mb-1">Pengambilan ke-</label><input
                                id="form-jumlah-pengambilan" type="number" min="1" value="1" placeholder="Jumlah"
                                required
                                class="w-full bg-gray-50 p-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-4 rounded-b-lg border-t border-gray-200"><button
                    type="button" id="btn-cancel-footer"
                    class="bg-white hover:bg-gray-100 text-slate-700 font-bold py-2 px-4 rounded-lg border border-gray-300 transition-colors">Batal</button><button
                    type="submit" form="skripsi-form"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">Simpan
                    Perubahan</button></div>
        </div>
    </div>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        Chart.defaults.color = 'rgba(71, 85, 105, 0.8)';
        Chart.defaults.borderColor = 'rgba(226, 232, 240, 1)';
        Chart.defaults.font.family = "'Inter', sans-serif";

        const state = {
            charts: {},
            isEditMode: false,
            allLecturers: [],
            allStudents: [],
            currentPage: 1,
            rowsPerPage: 10,
            sortColumn: 'nim',
            sortDirection: 'asc'
        };
        const userRole = "<?php echo htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8'); ?>";

        const injectTemplates = () => {
            document.getElementById('overview').innerHTML =
                `<h2 class="text-2xl font-bold text-slate-800 mb-6">Ringkasan</h2><div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"><div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm"><p class="text-slate-500 text-sm font-medium">Total Mahasiswa</p><p id="total-mahasiswa" class="text-3xl font-bold text-slate-800 mt-1">0</p></div><div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm"><p class="text-slate-500 text-sm font-medium">Sudah Lulus</p><p id="sudah-lulus" class="text-3xl font-bold text-emerald-600 mt-1">0</p></div><div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm"><p class="text-slate-500 text-sm font-medium">Belum Lulus</p><p id="belum-lulus" class="text-3xl font-bold text-amber-600 mt-1">0</p></div><div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm"><p class="text-slate-500 text-sm font-medium">Total Dosen</p><p id="total-dosen" class="text-3xl font-bold text-slate-800 mt-1">0</p></div></div>`;
            if (userRole === 'dosen') {
                document.querySelector('#overview h2').textContent = 'Ringkasan Bimbingan Anda';
                document.querySelector('#overview .grid div:first-child p:first-child').textContent =
                    'Total Mahasiswa Bimbingan';
                document.querySelector('#total-dosen').parentElement.classList.add('hidden');
                document.getElementById('dosen-view').innerHTML =
                    `<div class="flex flex-col md:flex-row justify-between items-center mb-6 pt-6"><h2 class="text-2xl font-bold text-slate-800">Detail Mahasiswa Bimbingan</h2></div><div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm"><div id="dosen-filters" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4"><input type="text" id="dosenSearchInput" placeholder="Cari Nama atau NIM..." class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"><select id="dosenStatusFilter" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"></select></div><div class="overflow-x-auto"><table class="w-full text-sm text-left text-slate-500"><thead class="text-xs text-slate-700 uppercase bg-gray-50"><tr><th class="px-6 py-3 sortable" data-sort="nim">NIM <span class="sort-indicator"></span></th><th class="px-6 py-3 sortable" data-sort="name">Mahasiswa <span class="sort-indicator"></span></th><th class="px-6 py-3">Judul</th><th class="px-6 py-3 sortable" data-sort="status">Status <span class="sort-indicator"></span></th><th class="px-6 py-3 sortable" data-sort="jumlah_pengambilan">Pengambilan Ke <span class="sort-indicator"></span></th></tr></thead><tbody id="dosen-student-table-body" class="divide-y divide-gray-200"></tbody></table></div><div id="dosen-pagination-controls" class="flex flex-col md:flex-row justify-between items-center mt-4 text-sm text-slate-600"><div class="flex items-center gap-2 mb-2 md:mb-0"><span>Tampilkan</span><select id="dosen-rows-per-page" class="bg-gray-50 border border-gray-300 rounded-md px-2 py-1"><option value="10">10</option><option value="20">20</option><option value="all">Semua</option></select></div><div class="flex items-center gap-2"><button id="dosen-prev-page" class="bg-white hover:bg-gray-100 border border-gray-300 px-3 py-1 rounded disabled:opacity-50 disabled:cursor-not-allowed">Sebelumnya</button><span id="dosen-page-info"></span><button id="dosen-next-page" class="bg-white hover:bg-gray-100 border border-gray-300 px-3 py-1 rounded disabled:opacity-50 disabled:cursor-not-allowed">Berikutnya</button></div></div></div>`;
            }
            if (userRole === 'admin' || userRole === 'kaprodi') {
                document.getElementById('insights').innerHTML =
                    `<h2 class="text-2xl font-bold text-slate-800 mb-6 pt-6">Insight & Distribusi</h2><div class="grid grid-cols-1 lg:grid-cols-3 gap-6"><div class="lg:col-span-2 bg-white p-6 rounded-lg border border-gray-200 shadow-sm"><h3 class="text-lg font-semibold text-slate-800 mb-4">Distribusi Bimbingan per Dosen</h3><div class="chart-container"><canvas id="distribusiChart"></canvas></div></div><div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm space-y-4"><h3 class="text-lg font-semibold text-slate-800">Insight</h3><div id="insight-cards"></div></div></div>`;
                document.getElementById('manajemen').innerHTML =
                    `<div class="flex flex-col md:flex-row justify-between items-center mb-6 pt-6"><h2 class="text-2xl font-bold text-slate-800">Manajemen Tugas Akhir</h2><div class="flex gap-2 mt-4 md:mt-0"><div class="relative dropdown"><button class="bg-white border border-gray-300 hover:bg-gray-100 text-slate-700 font-bold py-2 px-4 rounded-lg flex items-center gap-2"><i data-lucide="download" class="w-5 h-5"></i><span>Ekspor Data</span><i data-lucide="chevron-down" class="w-4 h-4"></i></button><div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200"><a href="#" class="export-btn block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100" data-format="pdf">Unduh sebagai PDF</a><a href="#" class="export-btn block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100" data-format="xls">Unduh sebagai XLS</a><a href="#" class="export-btn block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100" data-format="csv">Unduh sebagai CSV</a></div></div><button id="btn-add-new" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2"><i data-lucide="plus" class="w-5 h-5"></i><span>Tambah Data</span></button></div></div><div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm"><div id="manajemen-filters" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4"><input type="text" id="searchInput" placeholder="Cari Nama atau NIM..." class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"><select id="lecturerFilter" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"></select><select id="statusFilter" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"></select></div><div class="overflow-x-auto"><table class="w-full text-sm text-left text-slate-500"><thead class="text-xs text-slate-700 uppercase bg-gray-50"><tr><th class="px-6 py-3 sortable" data-sort="nim">NIM <span class="sort-indicator"></span></th><th class="px-6 py-3 sortable" data-sort="name">Mahasiswa <span class="sort-indicator"></span></th><th class="px-6 py-3">Pembimbing</th><th class="px-6 py-3 sortable" data-sort="status">Status <span class="sort-indicator"></span></th><th class="px-6 py-3 text-center">Aksi</th></tr></thead><tbody id="management-table-body" class="divide-y divide-gray-200"></tbody></table></div><div id="pagination-controls" class="flex flex-col md:flex-row justify-between items-center mt-4 text-sm text-slate-600"><div class="flex items-center gap-2 mb-2 md:mb-0"><span>Tampilkan</span><select id="rows-per-page" class="bg-gray-50 border border-gray-300 rounded-md px-2 py-1"><option value="10">10</option><option value="20">20</option><option value="30">30</option><option value="40">40</option><option value="all">Semua</option></select></div><div class="flex items-center gap-2"><button id="prev-page" class="bg-white hover:bg-gray-100 border border-gray-300 px-3 py-1 rounded disabled:opacity-50 disabled:cursor-not-allowed">Sebelumnya</button><span id="page-info"></span><button id="next-page" class="bg-white hover:bg-gray-100 border border-gray-300 px-3 py-1 rounded disabled:opacity-50 disabled:cursor-not-allowed">Berikutnya</button></div></div></div>`;
            }
            document.getElementById('topik').innerHTML =
                `<h2 class="text-2xl font-bold text-slate-800 mb-6 pt-6">Analisis Topik Populer</h2><div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm"><h3 class="text-lg font-semibold text-slate-800 mb-4">Frekuensi Kata Kunci Judul</h3><div class="chart-container" style="height: 60vh;"><canvas id="topicChart"></canvas></div></div>`;
        };

        const fetchData = async () => {
            document.getElementById('loading-overlay').style.display = 'flex';
            const searchInput = document.getElementById(userRole === 'dosen' ? 'dosenSearchInput' :
                'searchInput');
            const lecturerFilter = document.getElementById('lecturerFilter');
            const statusFilter = document.getElementById(userRole === 'dosen' ? 'dosenStatusFilter' :
                'statusFilter');

            const params = new URLSearchParams({
                sort: state.sortColumn,
                direction: state.sortDirection,
                search: searchInput ? searchInput.value : '',
                status: statusFilter ? statusFilter.value : '',
                lecturer: (userRole === 'admin' || userRole === 'kaprodi') && lecturerFilter ?
                    lecturerFilter.value : ''
            });
            try {
                const response = await fetch(`api.php?${params.toString()}`);
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal memuat data dari server.');
                }

                state.allLecturers = data.lecturers;
                state.allStudents = data.students;

                // Panggil fungsi untuk mengisi filter di sini, setelah elemen DOM dijamin ada
                populateAllFilters(data.lecturers);

                renderAll(data);
            } catch (error) {
                console.error("Kesalahan Fetch:", error);
                alert("Terjadi kesalahan saat memuat data: " + error.message);
            } finally {
                document.getElementById('loading-overlay').style.display = 'none';
            }
        };

        // Fungsi terpusat untuk mengisi semua jenis filter
        const populateAllFilters = (lecturers) => {
            if (userRole === 'admin' || userRole === 'kaprodi') {
                const lecturerFilter = document.getElementById('lecturerFilter');
                const statusFilter = document.getElementById('statusFilter');
                const formSelects = document.querySelectorAll('#form-p1, #form-p2, #form-u1, #form-u2');

                // Isi filter Dosen Utama
                if (lecturerFilter && lecturerFilter.options.length <= 1) {
                    lecturerFilter.innerHTML = '<option value="">Semua Dosen</option>';
                    lecturers.forEach(l => {
                        const opt = document.createElement('option');
                        opt.value = l.id;
                        opt.textContent = `${l.inisial} - ${l.nama_lengkap}`;
                        lecturerFilter.appendChild(opt);
                    });
                }

                // Isi filter Status Utama
                if (statusFilter && statusFilter.options.length <= 1) {
                    statusFilter.innerHTML =
                        '<option value="">Semua Status</option><option value="Lulus">Lulus</option><option value="Belum Lulus">Belum Lulus</option><option value="Data Tidak Lengkap">Data Tidak Lengkap</option>';
                }

                // Isi filter Dosen di Modal
                formSelects.forEach(sel => {
                    if (sel && sel.options.length <= 1) {
                        sel.innerHTML = '<option value="">-- Pilih --</option>';
                        lecturers.forEach(l => {
                            const opt = document.createElement('option');
                            opt.value = l.id;
                            opt.textContent = `${l.inisial} - ${l.nama_lengkap}`;
                            sel.appendChild(opt);
                        });
                    }
                });
            } else if (userRole === 'dosen') {
                const statusFilter = document.getElementById('dosenStatusFilter');
                if (statusFilter && statusFilter.options.length <= 1) {
                    statusFilter.innerHTML =
                        '<option value="">Semua Status</option><option value="Lulus">Lulus</option><option value="Belum Lulus">Belum Lulus</option><option value="Data Tidak Lengkap">Data Tidak Lengkap</option>';
                }
            }
        };

        // Sisa fungsi tetap sama seperti sebelumnya
        const renderAll = (data) => {
            renderKPIs(data.stats);
            if (userRole === 'admin' || userRole === 'kaprodi') {
                renderInsights(data.insights);
                renderManagementTable();
            }
            if (userRole === 'dosen') {
                renderDosenStudentTable();
            }
            renderTopicChart(data.students);
        };
        const renderInsights = (insights) => {
            const container = document.getElementById('insight-cards');
            if (!container) return;
            const topLecturer = insights.distribusi[0] || {
                nama: '-',
                jumlah: 0
            };
            const bottomLecturer = insights.distribusi[insights.distribusi.length - 1] || {
                nama: '-',
                jumlah: 0
            };
            container.innerHTML =
                `<div class="bg-indigo-50 p-4 rounded-lg"><p class="text-sm text-slate-600">Bimbingan Terbanyak</p><p class="font-bold text-indigo-800">${topLecturer.nama} (${topLecturer.jumlah} mhs)</p></div><div class="bg-rose-50 p-4 rounded-lg"><p class="text-sm text-slate-600">Bimbingan Paling Sedikit</p><p class="font-bold text-rose-800">${bottomLecturer.nama} (${bottomLecturer.jumlah} mhs)</p></div><div class="bg-emerald-50 p-4 rounded-lg"><p class="text-sm text-slate-600">Rata-rata Waktu Lulus</p><p class="font-bold text-emerald-800">${parseFloat(insights.avg_lulus_tahun || 0).toFixed(2)} Tahun</p></div><div class="bg-amber-50 p-4 rounded-lg"><p class="text-sm text-slate-600">Mhs Terlama Belum Lulus</p><p class="font-bold text-amber-800">${insights.terlama ? insights.terlama.nama : '-'}</p></div>`;
            renderChart('distribusiChart', 'doughnut', {
                labels: insights.distribusi.map(d => d.nama),
                datasets: [{
                    data: insights.distribusi.map(d => d.jumlah),
                    backgroundColor: [
                        '#e54646', // merah
                        '#f1d763', // kuning 
                        '#99f881', // hijau muda
                        '#32ffff', // cyan
                        '#476cff', // biru
                        '#9747ff', // ungu
                        '#ff47b6', // pink
                        '#ff8532', // oranye
                        '#47ff86', // hijau mint
                        '#d147ff' // magenta
                    ]
                }]
            }, {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            });
        };
        const renderDosenStudentTable = () => {
            const tbody = document.getElementById('dosen-student-table-body');
            if (!tbody) return;
            const dataToRender = state.allStudents;
            const totalRows = dataToRender.length;
            const rowsPerPageEl = document.getElementById('dosen-rows-per-page');
            const rowsPerPage = rowsPerPageEl ? rowsPerPageEl.value : state.rowsPerPage;
            const start = (state.currentPage - 1) * (rowsPerPage === 'all' ? totalRows : parseInt(
                rowsPerPage));
            const end = rowsPerPage === 'all' ? totalRows : start + parseInt(rowsPerPage);
            const paginatedData = dataToRender.slice(start, end);
            tbody.innerHTML = '';
            if (paginatedData.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="5" class="px-6 py-4 text-center">Tidak ada mahasiswa bimbingan yang cocok.</td></tr>`;
            } else {
                paginatedData.forEach(s => {
                    const statusColor = s.status === 'Lulus' ? 'text-emerald-600' : (s.status ===
                        'Belum Lulus' ? 'text-amber-600' : 'text-slate-500');
                    const row =
                        `<tr class="hover:bg-gray-50"><td class="px-6 py-4 font-medium text-slate-800">${s.nim}</td><td class="px-6 py-4 font-medium text-slate-800">${s.name}</td><td class="px-6 py-4 text-xs max-w-xs warpping-text overflow-hidden" title="${s.title}">${s.title}</td><td class="px-6 py-4"><span class="font-semibold ${statusColor}">${s.status}</span></td><td class="px-6 py-4 text-center">${s.jumlah_pengambilan}</td></tr>`;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            }
            updatePaginationControls(totalRows, 'dosen-');
            updateSortIndicators('dosen-view');
            lucide.createIcons();
        };
        const renderKPIs = (stats) => {
            document.getElementById('total-mahasiswa').textContent = stats.totalMahasiswa || 0;
            document.getElementById('sudah-lulus').textContent = stats.sudahLulus || 0;
            document.getElementById('belum-lulus').textContent = stats.belumLulus || 0;
            if (document.getElementById('total-dosen')) {
                document.getElementById('total-dosen').textContent = stats.totalDosen || 0;
            }
        };
        const renderChart = (canvasId, type, data, options) => {
            if (state.charts[canvasId]) state.charts[canvasId].destroy();
            const ctx = document.getElementById(canvasId)?.getContext('2d');
            if (ctx) state.charts[canvasId] = new Chart(ctx, {
                type,
                data,
                options
            });
        };
        const renderTopicChart = (students) => {
            const wordCount = {};
            const stopWords = new Set(['di', 'dan', 'dengan', 'pada', 'untuk', 'menggunakan', 'berbasis',
                'studi', 'kasus', 'sistem', 'metode', 'analisis', 'perancangan', 'implementasi',
                'aplikasi', 'rancang', 'bangun', 'dalam', 'sebagai', 'pontianak', 'yang', 'atau',
                'dari', 'ini', 'terhadap', 'teknik', 'jaringan', 'data', 'komputer', 'jaringan',
                'informasi', 'basis', 'web', 'mobile', 'cloud', 'keamanan', 'sosial', 'media',
                'digital', 'internet', 'iot', 'artificial', 'intelligence', 'machine', 'learning',
                'deep', 'learning', 'big', 'data', 'kabupaten', 'berdasarkan', 'reality', 'cyber',
                'physical', 'virtual', 'smart', 'city', 'network', 'design', 'development',
                'evaluation', 'performance', 'modeling', 'simulation', 'automation', 'control',
                'robotics', 'embedded', 'system', 'sensor', 'actuator', 'tingkat', 'protocol',
                'architecture', 'framework', 'platform', 'technology', 'application', 'penjualan',
                'kota', 'pelayanan', 'kesehatan', 'pendidikan', 'keuangan', 'pertanian',
                'perdagangan', 'industri', 'manufaktur', 'transportasi', 'logistik', 'energi',
                'lingkungan', 'algoritma', 'desa', 'universitas', 'fakultas', 'jurusan', 'program',
                'studi', 'teknologi', 'informasi', 'komunikasi', 'sains', 'teknik', 'ilmu', 'data',
                'komputer', 'sistem', 'informasi', 'multimedia', 'grafika', 'animasi', 'game',
                'cloud', 'computing', 'security', 'cryptography', 'blockchain', 'virtualization',
                'kabupaten', 'provinsi', 'berdasarkan', 'microservices', 'algoritma', 'kota',
                'desa',
                'universitas', 'fakultas', 'jurusan', 'program', 'studi', 'teknologi', 'informasi',
                'komunikasi', 'sains', 'teknik', 'ilmu', 'data', 'komputer', 'sistem', 'rumah',
                'penerapan', 'website', 'e-commerce', 'e-learning', 'e-government', 'e-health',
                'social', 'networking', 'lokasi', 'pemilihan', 'perbandingan', 'studi', 'kasus',
                'terhadap', 'penerapan', 'jenis'
            ]);
            students.forEach(s => {
                if (s.title) s.title.toLowerCase().replace(/[^a-z\s]/g, '').split(/\s+/).forEach(
                    word => {
                        if (word.length > 3 && !stopWords.has(word)) wordCount[word] = (
                            wordCount[word] || 0) + 1;
                    });
            });
            const sorted = Object.entries(wordCount).sort(([, a], [, b]) => b - a).slice(0, 35);
            renderChart('topicChart', 'bar', {
                labels: sorted.map(item => item[0]),
                datasets: [{
                    label: 'Frekuensi Kata',
                    data: sorted.map(item => item[1]),
                    backgroundColor: [
                        '#e54646', // merah
                        '#f1d763', // kuning 
                        '#99f881', // hijau muda
                        '#32ffff', // cyan
                        '#476cff', // biru
                        '#9747ff', // ungu
                        '#ff47b6', // pink
                        '#ff8532', // oranye
                        '#47ff86', // hijau mint
                        '#d147ff' // magenta
                    ]
                }]
            }, {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            });
        };
        const renderManagementTable = () => {
            const tbody = document.getElementById('management-table-body');
            if (!tbody) return;
            const dataToRender = state.allStudents;
            const totalRows = dataToRender.length;
            const start = (state.currentPage - 1) * (state.rowsPerPage === 'all' ? totalRows : parseInt(
                state.rowsPerPage));
            const end = state.rowsPerPage === 'all' ? totalRows : start + parseInt(state.rowsPerPage);
            const paginatedData = dataToRender.slice(start, end);
            tbody.innerHTML = '';
            if (paginatedData.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="5" class="px-6 py-4 text-center">Tidak ada data yang cocok.</td></tr>`;
            } else {
                paginatedData.forEach(s => {
                    const isAtRisk = s.status === 'Belum Lulus' && s.jumlah_pengambilan >= 2;
                    const statusColor = s.status === 'Lulus' ? 'text-emerald-600' : (s.status ===
                        'Belum Lulus' ? 'text-amber-600' : 'text-slate-500');
                    const row =
                        `<tr class="hover:bg-gray-50"><td class="px-6 py-4 font-medium text-slate-800">${s.nim}</td><td class="px-6 py-4 font-medium text-slate-800"><div class="flex items-center gap-2">${isAtRisk ? '<i data-lucide="flag" class="w-4 h-4 text-red-500 flex-shrink-0"></i>' : ''}<span>${s.name}</span></div></td><td class="px-6 py-4 text-xs">${s.p1 || '-'}<br>${s.p2 || '-'}</td><td class="px-6 py-4"><span class="font-semibold ${statusColor}">${s.status}</span></td><td class="px-6 py-4 text-center"><div class="flex justify-center gap-2"><button data-nim="${s.nim}" class="btn-edit text-indigo-600 hover:text-indigo-800 p-1"><i data-lucide="file-pen-line" class="w-4 h-4"></i></button><button data-nim="${s.nim}" class="btn-delete text-red-600 hover:text-red-800 p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button></div></td></tr>`;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            }
            updatePaginationControls(totalRows);
            updateSortIndicators('manajemen');
            lucide.createIcons();
        };
        const updatePaginationControls = (totalRows, prefix = '') => {
            const pageInfo = document.getElementById(`${prefix}page-info`);
            const prevPage = document.getElementById(`${prefix}prev-page`);
            const nextPage = document.getElementById(`${prefix}next-page`);
            if (!pageInfo || !prevPage || !nextPage) return;
            const rowsPerPageEl = document.getElementById(`${prefix}rows-per-page`);
            const rowsPerPage = rowsPerPageEl ? rowsPerPageEl.value : state.rowsPerPage;
            const finalRowsPerPage = rowsPerPage === 'all' ? totalRows : parseInt(rowsPerPage);
            const totalPages = finalRowsPerPage > 0 ? Math.ceil(totalRows / finalRowsPerPage) : 1;
            pageInfo.textContent = `Halaman ${state.currentPage} dari ${totalPages}`;
            prevPage.disabled = state.currentPage === 1;
            nextPage.disabled = state.currentPage >= totalPages;
        };
        const updateSortIndicators = (sectionId) => {
            const section = document.getElementById(sectionId);
            if (!section) return;
            section.querySelectorAll('.sortable .sort-indicator').forEach(el => el.innerHTML = '');
            const activeIndicator = section.querySelector(
                `.sortable[data-sort="${state.sortColumn}"] .sort-indicator`);
            if (activeIndicator) activeIndicator.innerHTML = state.sortDirection === 'asc' ?
                '<i data-lucide="chevron-up" class="w-4 h-4"></i>' :
                '<i data-lucide="chevron-down" class="w-4 h-4"></i>';
            lucide.createIcons();
        };
        const openModal = (mode, nim = null) => {
            state.isEditMode = (mode === 'edit');
            const modal = document.getElementById('skripsi-modal');
            const modalContent = document.getElementById('modal-content');
            const form = document.getElementById('skripsi-form');
            form.reset();
            document.getElementById('modal-title').textContent = state.isEditMode ?
                'Edit Data Tugas Akhir' : 'Tambah Data Tugas Akhir';
            document.getElementById('form-nim').readOnly = state.isEditMode;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
            }, 10);
            if (state.isEditMode && nim) {
                document.getElementById('form-nim-original').value = nim;
                fetch(`manage_data.php?action=get_skripsi&nim=${nim}`).then(res => res.json()).then(res => {
                    if (res.success) {
                        const d = res.data;
                        document.getElementById('form-nim').value = d.mahasiswa_nim;
                        document.getElementById('form-nama').value = d.nama;
                        document.getElementById('form-judul').value = d.judul;
                        document.getElementById('form-p1').value = d.pembimbing_1_id || '';
                        document.getElementById('form-p2').value = d.pembimbing_2_id || '';
                        document.getElementById('form-u1').value = d.penguji_1_id || '';
                        document.getElementById('form-u2').value = d.penguji_2_id || '';
                        document.getElementById('form-tgl-sk').value = d.tgl_sk_pembimbing;
                        document.getElementById('form-tgl-lulus').value = d.tgl_lulus;
                        document.getElementById('form-status').value = d.status;
                        document.getElementById('form-jumlah-pengambilan').value = d
                            .jumlah_pengambilan;
                    } else {
                        alert(res.message);
                        closeModal();
                    }
                });
            }
        };
        const closeModal = () => {
            const modal = document.getElementById('skripsi-modal');
            const modalContent = document.getElementById('modal-content');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        };
        const handleFormSubmit = async (e) => {
            e.preventDefault();
            const formData = {
                nim: document.getElementById('form-nim').value,
                nama: document.getElementById('form-nama').value,
                judul: document.getElementById('form-judul').value,
                pembimbing_1_id: document.getElementById('form-p1').value || null,
                pembimbing_2_id: document.getElementById('form-p2').value || null,
                penguji_1_id: document.getElementById('form-u1').value || null,
                penguji_2_id: document.getElementById('form-u2').value || null,
                tgl_sk_pembimbing: document.getElementById('form-tgl-sk').value || null,
                tgl_lulus: document.getElementById('form-tgl-lulus').value || null,
                status: document.getElementById('form-status').value,
                jumlah_pengambilan: document.getElementById('form-jumlah-pengambilan').value
            };
            const url = state.isEditMode ? `manage_data.php?action=update_skripsi` :
                `manage_data.php?action=add_skripsi`;
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                const result = await response.json();
                if (result.success) {
                    closeModal();
                    await fetchData();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Submit error:', error);
                alert('Terjadi kesalahan koneksi.');
            }
        };
        const deleteData = async (nim) => {
            if (!confirm(`Apakah Anda yakin ingin menghapus data untuk NIM ${nim}?`)) return;
            try {
                const response = await fetch(`manage_data.php?action=delete_skripsi`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nim: nim
                    })
                });
                const result = await response.json();
                if (result.success) {
                    await fetchData();
                } else {
                    alert('Gagal menghapus: ' + result.message);
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('Terjadi kesalahan koneksi.');
            }
        };
        const handleExport = (format) => {
            const search = document.getElementById('searchInput')?.value || '';
            const status = document.getElementById('statusFilter')?.value || '';
            const lecturerFilter = document.getElementById('lecturerFilter');
            const selectedLecturerId = lecturerFilter ? lecturerFilter.value : '';
            let lecturerInitial = '';
            if (selectedLecturerId) {
                const selectedLecturer = state.allLecturers.find(l => l.id == selectedLecturerId);
                if (selectedLecturer) lecturerInitial = selectedLecturer.inisial;
            }
            const params = new URLSearchParams({
                format,
                search,
                status,
                lecturer: lecturerInitial
            });
            window.open(`export_data.php?${params.toString()}`, '_blank');
        };

        const setupEventListeners = () => {
            document.getElementById('menu-toggle').addEventListener('click', () => {
                document.getElementById('sidebar').classList.toggle('-translate-x-full');
            });

            const handleSort = (e) => {
                const header = e.target.closest('.sortable');
                if (!header) return;
                const column = header.dataset.sort;
                if (state.sortColumn === column) {
                    state.sortDirection = state.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    state.sortColumn = column;
                    state.sortDirection = 'asc';
                }
                fetchData();
            };

            if (userRole === 'dosen') {
                document.querySelector('#dosen-view thead')?.addEventListener('click', handleSort);
                let debounceTimer;
                ['dosenSearchInput', 'dosenStatusFilter'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        const eventType = el.tagName === 'SELECT' ? 'change' : 'input';
                        el.addEventListener(eventType, () => {
                            clearTimeout(debounceTimer);
                            debounceTimer = setTimeout(fetchData, 300);
                        });
                    }
                });
                const rowsPerPageSelect = document.getElementById('dosen-rows-per-page');
                const prevPageBtn = document.getElementById('dosen-prev-page');
                const nextPageBtn = document.getElementById('dosen-next-page');
                if (rowsPerPageSelect) rowsPerPageSelect.addEventListener('change', (e) => {
                    state.rowsPerPage = e.target.value;
                    state.currentPage = 1;
                    renderDosenStudentTable();
                });
                if (prevPageBtn) prevPageBtn.addEventListener('click', () => {
                    if (state.currentPage > 1) {
                        state.currentPage--;
                        renderDosenStudentTable();
                    }
                });
                if (nextPageBtn) nextPageBtn.addEventListener('click', () => {
                    const totalRows = state.allStudents.length;
                    const rowsPerPage = document.getElementById('dosen-rows-per-page').value;
                    const finalRowsPerPage = rowsPerPage === 'all' ? totalRows : parseInt(
                        rowsPerPage);
                    const totalPages = finalRowsPerPage > 0 ? Math.ceil(totalRows /
                        finalRowsPerPage) : 1;
                    if (state.currentPage < totalPages) {
                        state.currentPage++;
                        renderDosenStudentTable();
                    }
                });
            }

            if (userRole === 'admin' || userRole === 'kaprodi') {
                const managementSection = document.getElementById('manajemen');
                const btnAddNew = document.getElementById('btn-add-new');
                const tableBody = document.getElementById('management-table-body');
                const rowsPerPageSelect = document.getElementById('rows-per-page');
                const prevPageBtn = document.getElementById('prev-page');
                const nextPageBtn = document.getElementById('next-page');
                document.getElementById('btn-cancel').addEventListener('click', closeModal);
                document.getElementById('btn-cancel-footer').addEventListener('click', closeModal);
                document.getElementById('skripsi-form').addEventListener('submit', handleFormSubmit);
                if (btnAddNew) btnAddNew.addEventListener('click', () => openModal('add'));
                if (tableBody) tableBody.addEventListener('click', (e) => {
                    const editBtn = e.target.closest('.btn-edit');
                    const deleteBtn = e.target.closest('.btn-delete');
                    if (editBtn) openModal('edit', editBtn.dataset.nim);
                    else if (deleteBtn) deleteData(deleteBtn.dataset.nim);
                });
                let debounceTimer;
                ['searchInput', 'lecturerFilter', 'statusFilter'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        const eventType = el.tagName === 'SELECT' ? 'change' : 'input';
                        el.addEventListener(eventType, () => {
                            clearTimeout(debounceTimer);
                            debounceTimer = setTimeout(fetchData, 300);
                        });
                    }
                });
                if (rowsPerPageSelect) rowsPerPageSelect.addEventListener('change', (e) => {
                    state.rowsPerPage = e.target.value;
                    state.currentPage = 1;
                    renderManagementTable();
                });
                if (prevPageBtn) prevPageBtn.addEventListener('click', () => {
                    if (state.currentPage > 1) {
                        state.currentPage--;
                        renderManagementTable();
                    }
                });
                if (nextPageBtn) nextPageBtn.addEventListener('click', () => {
                    const totalRows = state.allStudents.length;
                    const rowsPerPage = state.rowsPerPage === 'all' ? totalRows : parseInt(state
                        .rowsPerPage);
                    const totalPages = rowsPerPage > 0 ? Math.ceil(totalRows / rowsPerPage) : 1;
                    if (state.currentPage < totalPages) {
                        state.currentPage++;
                        renderManagementTable();
                    }
                });
                if (managementSection) {
                    managementSection.querySelector('thead').addEventListener('click', handleSort);
                    managementSection.addEventListener('click', (e) => {
                        const exportBtn = e.target.closest('.export-btn');
                        if (exportBtn) {
                            e.preventDefault();
                            handleExport(exportBtn.dataset.format);
                        }
                    });
                }
            }
        };
        injectTemplates();
        fetchData().then(() => {
            setupEventListeners();
            lucide.createIcons();
        });
    });
    </script>
</body>

</html>