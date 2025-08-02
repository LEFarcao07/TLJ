<?php
require 'config.php';
session_start();

if (isset($_SESSION['message_shown']) && $_SESSION['message_shown'] === true) {
    unset($_SESSION['message']);
    unset($_SESSION['message_shown']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password_input = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $stored_password);
        $stmt->fetch();

        if ($password_input === $stored_password) {
            $_SESSION['user_id'] = $id;
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['message'] = [
                'text' => "Password salah.",
                'type' => "text-red-500"
            ];
        }
    } else {
        $_SESSION['message'] = [
            'text' => "Akun tidak ditemukan.",
            'type' => "text-red-500"
        ];
    }

    $stmt->close();

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex justify-center items-center h-screen bg-gray-100">
    <form method="POST" class="bg-white p-6 rounded shadow-md w-96">
        <h2 class="text-2xl mb-4 font-bold text-center">Login</h2>
        <?php
        if (isset($_SESSION['message'])) {
            echo "<p class='{$_SESSION['message']['type']} mb-3'>{$_SESSION['message']['text']}</p>";
            $_SESSION['message_shown'] = true;
        }
        ?>
        <input name="username" type="text" placeholder="Username" autocomplete="username"
            class="w-full p-2 border rounded mb-3" required>
        <input name="password" type="password" placeholder="Password" autocomplete="current-password"
            class="w-full p-2 border rounded mb-3" required>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full">Login</button>
        <p class="mt-3 text-center">Belum punya akun? <a href="register.php" class="text-blue-500">Daftar</a></p>
    </form>
</body>

</html>