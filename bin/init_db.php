<?php

declare(strict_types=1);

$databasePath = $argv[1] ?? __DIR__ . '/../storage/db/panel.sqlite';
if (!is_dir(dirname($databasePath))) {
    mkdir(dirname($databasePath), 0755, true);
}

$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK (role IN (\'admin\',\'user\')),
  created_at TEXT NOT NULL
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS servers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  game_type TEXT NOT NULL,
  port INTEGER NOT NULL,
  directory TEXT NOT NULL,
  status TEXT NOT NULL,
  pid INTEGER,
  created_at TEXT NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id)
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS ftp_accounts (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  server_id INTEGER NOT NULL,
  username TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  home_dir TEXT NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id),
  FOREIGN KEY(server_id) REFERENCES servers(id)
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS game_templates (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT UNIQUE NOT NULL,
  start_command TEXT NOT NULL,
  default_port_range TEXT NOT NULL,
  description TEXT NOT NULL
)');

$users = [
    ['admin@admin.cz', 'pass123', 'admin'],
    ['test@test.cz', 'test123', 'user'],
];

foreach ($users as [$email, $password, $role]) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ((int) $stmt->fetchColumn() === 0) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare('INSERT INTO users (email, password_hash, role, created_at) VALUES (:email, :password_hash, :role, :created_at)');
        $insert->execute([
            'email' => $email,
            'password_hash' => $hash,
            'role' => $role,
            'created_at' => (new DateTimeImmutable())->format('c'),
        ]);
    }
}

$templates = [
    ['Minecraft', 'java -Xmx2G -Xms1G -jar server.jar nogui', '25565-25575', 'Vanilla Minecraft template'],
    ['CS2', './cs2_server -port 27015', '27015-27025', 'Counter-Strike 2 dedicated server template'],
    ['Valheim', './valheim_server.x86_64 -name "Valheim" -port 2456', '2456-2466', 'Valheim server template'],
    ['Terraria', './TerrariaServer.bin.x86_64 -port 7777', '7777-7787', 'Terraria server template'],
    ['Rust', './RustDedicated -port 28015', '28015-28025', 'Rust server template'],
];

foreach ($templates as [$name, $cmd, $ports, $desc]) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM game_templates WHERE name = :name');
    $stmt->execute(['name' => $name]);
    if ((int) $stmt->fetchColumn() === 0) {
        $insert = $pdo->prepare('INSERT INTO game_templates (name, start_command, default_port_range, description) VALUES (:name, :start_command, :default_port_range, :description)');
        $insert->execute([
            'name' => $name,
            'start_command' => $cmd,
            'default_port_range' => $ports,
            'description' => $desc,
        ]);
    }
}

fwrite(STDOUT, "Database initialized at {$databasePath}\n");
