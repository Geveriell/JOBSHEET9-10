<?php
require __DIR__.'/includes/config.php';
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
header('Content-Type: application/json; charset=utf-8');

function out($d, $c = 200) { http_response_code($c); echo json_encode($d); exit; }
function need_login() { if (empty($_SESSION['uid'])) out(['error' => 'Silakan masuk dulu'], 401); return (int)$_SESSION['uid']; }

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$a = $_GET['a'] ?? '';

try {
  $pdo = db();
  switch ($a) {
    case 'register':
      $nama = trim($in['nama'] ?? ''); $email = strtolower(trim($in['email'] ?? '')); $pw = $in['password'] ?? '';
      if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6)
        out(['error' => 'Isi nama, email valid, dan kata sandi minimal 6 karakter'], 422);
      $s = $pdo->prepare('SELECT id FROM users WHERE email=?'); $s->execute([$email]);
      if ($s->fetch()) out(['error' => 'Email sudah terdaftar'], 409);
      $pdo->prepare('INSERT INTO users (nama,email,password_hash) VALUES (?,?,?)')
          ->execute([$nama, $email, password_hash($pw, PASSWORD_DEFAULT)]);
      session_regenerate_id(true);
      $_SESSION['uid'] = (int)$pdo->lastInsertId(); $_SESSION['nama'] = $nama;
      out(['nama' => $nama]);

    case 'login':
      $s = $pdo->prepare('SELECT * FROM users WHERE email=?'); $s->execute([strtolower(trim($in['email'] ?? ''))]);
      $u = $s->fetch();
      if (!$u || !password_verify($in['password'] ?? '', $u['password_hash'])) out(['error' => 'Email atau kata sandi salah'], 401);
      session_regenerate_id(true);
      $_SESSION['uid'] = (int)$u['id']; $_SESSION['nama'] = $u['nama'];
      out(['nama' => $u['nama']]);

    case 'logout':
      $_SESSION = []; session_destroy(); out(['ok' => true]);

    case 'me':
      need_login(); out(['nama' => $_SESSION['nama']]);

    case 'list':
      $s = $pdo->prepare('SELECT id,tanggal,tipe,kategori,deskripsi,jumlah FROM transaksi WHERE user_id=? ORDER BY tanggal DESC, id DESC');
      $s->execute([need_login()]); out($s->fetchAll());

    case 'save':
      $uid = need_login();
      $tipe = $in['tipe'] ?? ''; $tgl = $in['tanggal'] ?? ''; $kat = trim($in['kategori'] ?? '');
      $des = trim($in['deskripsi'] ?? ''); $jum = (float)($in['jumlah'] ?? 0);
      $d = DateTime::createFromFormat('Y-m-d', $tgl);
      if (!in_array($tipe, ['pemasukan', 'pengeluaran'], true) || !$d || $d->format('Y-m-d') !== $tgl || $kat === '' || $jum <= 0)
        out(['error' => 'Data belum lengkap atau tidak valid'], 422);
      $kat = mb_substr($kat, 0, 40); $des = mb_substr($des, 0, 120);
      if (!empty($in['id'])) {
        $pdo->prepare('UPDATE transaksi SET tanggal=?,tipe=?,kategori=?,deskripsi=?,jumlah=? WHERE id=? AND user_id=?')
            ->execute([$tgl, $tipe, $kat, $des, $jum, (int)$in['id'], $uid]);
      } else {
        $pdo->prepare('INSERT INTO transaksi (user_id,tanggal,tipe,kategori,deskripsi,jumlah) VALUES (?,?,?,?,?,?)')
            ->execute([$uid, $tgl, $tipe, $kat, $des, $jum]);
      }
      out(['ok' => true]);

    case 'delete':
      $pdo->prepare('DELETE FROM transaksi WHERE id=? AND user_id=?')->execute([(int)($in['id'] ?? 0), need_login()]);
      out(['ok' => true]);

    default: out(['error' => 'Aksi tidak dikenal'], 404);
  }
} catch (PDOException $e) {
  out(['error' => 'DB: ' . $e->getMessage()], 500);
}