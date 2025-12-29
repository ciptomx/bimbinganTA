<?php
session_start();

// Jika pengguna sudah login, alihkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']); // Hapus pesan error setelah ditampilkan
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dasbor Bimbingan TA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc; /* bg-slate-50 */
        }
    </style>
</head>
<body class="text-slate-600 antialiased">

    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="w-full max-w-md">
            <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-200">
                <div class="flex items-center justify-center gap-3 mb-6">
                     <div class="bg-indigo-600 text-white p-3 rounded-xl">
                        <i data-lucide="book-marked"></i>
                     </div>
                     <h1 class="text-2xl font-bold text-slate-800">Dasbor Bimbingan TA</h1>
                </div>

                <h2 class="text-center text-xl font-semibold text-slate-700 mb-2">Selamat Datang Kembali</h2>
                <p class="text-center text-sm text-slate-500 mb-8">Silakan masuk untuk melanjutkan</p>
                
                <?php if ($error): ?>
                    <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 text-sm flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <form action="auth.php?action=login" method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="font-medium text-slate-700 block mb-1.5">Username</label>
                        <div class="relative">
                             <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i data-lucide="user" class="w-5 h-5 text-slate-400"></i>
                            </span>
                            <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required
                                   class="w-full bg-gray-50 p-3 pl-10 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>
                    </div>
                    <div>
                        <label for="password" class="font-medium text-slate-700 block mb-1.5">Password</label>
                         <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i data-lucide="lock" class="w-5 h-5 text-slate-400"></i>
                            </span>
                            <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required
                                   class="w-full bg-gray-50 p-3 pl-10 rounded-md border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>
                    </div>
                    <div>
                        <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                            <span>Masuk</span>
                        </button>
                    </div>
                </form>
            </div>
            <p class="text-center text-sm text-slate-500 mt-6">
                &copy; <?php echo date('Y'); ?> Dasbor Bimbingan TA. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>