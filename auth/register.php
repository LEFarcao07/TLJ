<?php
session_start();
require 'config.php';

if (isset($_SESSION['message_shown']) && $_SESSION['message_shown'] === true) {
    unset($_SESSION['message']);
    unset($_SESSION['message_shown']);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['message'] = [
            'text' => "Username sudah digunakan.",
            'type' => "text-red-500"
        ];
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $_SESSION['message'] = [
                'text' => "Registrasi berhasil. <a href='login.php' class='underline'>Login di sini</a>",
                'type' => "text-green-500"
            ];
        } else {
            $_SESSION['message'] = [
                'text' => "Terjadi kesalahan saat menyimpan data.",
                'type' => "text-red-500"
            ];
        }

        $stmt->close();
    }

    $check->close();
    header("Location: register.php");
    exit;
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex justify-center items-center min-h-screen bg-gray-100">
    <form method="POST" class="bg-white p-6 rounded shadow-md w-96">
        <h2 class="text-2xl mb-4 font-bold text-center">Register</h2>
        <?php
        if (isset($_SESSION['message'])) {
            echo "<p class='{$_SESSION['message']['type']} mb-3'>{$_SESSION['message']['text']}</p>";
            $_SESSION['message_shown'] = true;
        }
        ?>
        <input name="username" type="text" placeholder="Username" autocomplete="username"
            class="w-full p-2 border rounded mb-3" required>
        <input name="password" type="password" placeholder="Password" autocomplete="new-password"
            class="w-full p-2 border rounded mb-3" required>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full">Register</button>
        <p class="mt-3 text-center">Sudah punya akun? <a href="login.php" class="text-blue-500">Login</a></p>
    </form>
</body>

</html>