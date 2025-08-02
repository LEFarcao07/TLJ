<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Proses ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_pass);
    $stmt->fetch();
    $stmt->close();

    if ($old_pass === $current_pass) {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_pass, $user_id);
        $stmt->execute();
        $_SESSION['message'] = ['text' => 'Password berhasil diubah.', 'type' => 'green'];
    } else {
        $_SESSION['message'] = ['text' => 'Password lama salah.', 'type' => 'red'];
    }

    header("Location: dashboard.php?notif=true");
    exit;
}

// Proses ganti username
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_username'])) {
    $new_username = $_POST['new_username'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->bind_param("si", $new_username, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['message'] = ['text' => 'Username sudah digunakan.', 'type' => 'red'];
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $new_username, $user_id);
        $stmt->execute();
        $_SESSION['message'] = ['text' => 'Username berhasil diubah.', 'type' => 'green'];
    }

    $stmt->close();
    header("Location: dashboard.php?notif=true");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    session_destroy();
    header("Location: login.php?deleted=true");
    exit;
}

$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center font-sans">

    <div class="text-center p-8">
        <h1 class="text-4xl font-bold mb-2">Selamat Datang, <span
                class="text-red-400"><?= htmlspecialchars($username) ?></span>!</h1>
        <p class="text-gray-400 mb-6">Kelola akunmu dengan fitur di bawah ini</p>

        <?php if (isset($_SESSION['message'])): ?>
            <div
                class="text-<?= $_SESSION['message']['type'] ?>-400 bg-<?= $_SESSION['message']['type'] ?>-900 border border-<?= $_SESSION['message']['type'] ?>-500 p-3 mb-6 rounded">
                <?= $_SESSION['message']['text'] ?>
            </div>
            <?php if (isset($_GET['notif']))
                unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="space-y-4">
            <button id="openPasswordModal"
                class="bg-red-500 hover:bg-red-600 px-6 py-2 rounded text-white font-semibold">Ganti Password</button>
            <button id="openUsernameModal"
                class="bg-blue-500 hover:bg-blue-600 px-6 py-2 rounded text-white font-semibold">Ganti Username</button>
            <button id="openDeleteModal"
                class="bg-red-700 hover:bg-red-800 px-6 py-2 rounded text-white font-semibold">Hapus Akun</button>
            <a href="logout.php"
                class="bg-gray-600 hover:bg-gray-500 px-6 py-2 rounded text-white font-semibold">Logout</a>
        </div>
    </div>

    <!-- Modal Ganti Password -->
    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white text-black rounded-lg shadow-lg w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">Ganti Password</h2>
            <form method="POST">
                <input type="hidden" name="change_password" value="1">
                <input type="password" name="old_password" placeholder="Password Lama" required
                    class="w-full mb-3 p-2 rounded border">
                <input type="password" name="new_password" placeholder="Password Baru" required
                    class="w-full mb-4 p-2 rounded border">
                <div class="flex justify-between">
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Simpan</button>
                    <button type="button" id="closePasswordModal" class="text-red-500">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ganti Username -->
    <div id="usernameModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white text-black rounded-lg shadow-lg w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">Ganti Username</h2>
            <form method="POST">
                <input type="hidden" name="change_username" value="1">
                <input type="text" name="new_username" placeholder="Username Baru" required
                    class="w-full mb-4 p-2 rounded border">
                <div class="flex justify-between">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Simpan</button>
                    <button type="button" id="closeUsernameModal" class="text-blue-500">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Akun -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white text-black rounded-lg shadow-lg w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4 text-red-600">Konfirmasi Hapus Akun</h2>
            <p class="mb-4">Apakah kamu yakin ingin menghapus akunmu secara permanen?</p>
            <form method="POST">
                <input type="hidden" name="delete_account" value="1">
                <div class="flex justify-between">
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">Hapus</button>
                    <button type="button" id="closeDeleteModal" class="text-red-500">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Password
        const openPasswordModal = document.getElementById('openPasswordModal');
        const closePasswordModal = document.getElementById('closePasswordModal');
        const passwordModal = document.getElementById('passwordModal');

        openPasswordModal.onclick = () => passwordModal.classList.remove('hidden');
        closePasswordModal.onclick = () => passwordModal.classList.add('hidden');

        // Modal Username
        const openUsernameModal = document.getElementById('openUsernameModal');
        const closeUsernameModal = document.getElementById('closeUsernameModal');
        const usernameModal = document.getElementById('usernameModal');

        openUsernameModal.onclick = () => usernameModal.classList.remove('hidden');
        closeUsernameModal.onclick = () => usernameModal.classList.add('hidden');

        // Modal Delete
        const openDeleteModal = document.getElementById('openDeleteModal');
        const closeDeleteModal = document.getElementById('closeDeleteModal');
        const deleteModal = document.getElementById('deleteModal');

        openDeleteModal.onclick = () => deleteModal.classList.remove('hidden');
        closeDeleteModal.onclick = () => deleteModal.classList.add('hidden');
    </script>

</body>

</html>