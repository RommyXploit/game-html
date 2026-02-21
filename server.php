<?php
header('Content-Type: application/json');
$databaseFile = 'database.txt';

if (!file_exists($databaseFile)) { file_put_contents($databaseFile, ""); }

$action = $_POST['action'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// --- AUTH: LOGIN & REGISTER ---
if ($action == 'auth') {
    $users = file($databaseFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $found = false;
    foreach ($users as $line) {
        $data = explode('|', $line);
        if ($data[0] === $username) {
            if ($data[1] === $password) {
                echo json_encode(['status' => 'success', 'message' => 'Login Berhasil!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Password Salah!']);
            }
            $found = true; break;
        }
    }
    if (!$found) {
        $newUser = "$username|$password|0|0|0|0";
        file_put_contents($databaseFile, $newUser . PHP_EOL, FILE_APPEND);
        echo json_encode(['status' => 'success', 'message' => 'Akun Berhasil Dibuat!']);
    }
    exit;
}

// --- UPDATE SKOR ---
if ($action == 'update_score') {
    $gameIdx = intval($_POST['game_index']); // index 2=snake, 3=2048, dst
    $newScore = intval($_POST['score']);
    $lines = file($databaseFile, FILE_IGNORE_NEW_LINES);
    foreach ($lines as &$line) {
        $parts = explode('|', $line);
        if ($parts[0] === $username) {
            if ($newScore > (int)$parts[$gameIdx]) {
                $parts[$gameIdx] = $newScore;
                $line = implode('|', $parts);
            }
            break;
        }
    }
    file_put_contents($databaseFile, implode(PHP_EOL, $lines) . PHP_EOL);
    echo json_encode(['status' => 'success']);
    exit;
}

// --- GET TOP 20 RANK ---
if ($action == 'get_rank') {
    $lines = file($databaseFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $allPlayers = [];
    foreach ($lines as $line) {
        $p = explode('|', $line);
        $total = (int)$p[2] + (int)$p[3] + (int)$p[4] + (int)$p[5];
        $allPlayers[] = ['user' => $p[0], 'snake' => $p[2], 'total' => $total];
    }
    // Urutkan berdasarkan total tertinggi
    usort($allPlayers, function($a, $b) { return $b['total'] - $a['total']; });
    echo json_encode(['status' => 'success', 'data' => array_slice($allPlayers, 0, 20)]);
    exit;
}
?>
