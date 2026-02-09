<?php
declare(strict_types=1);

/**
 * EcoRide - data.php
 * Endpoint used by conn.html to persist registrations into MySQL (phpMyAdmin).
 *
 * DB: ecoride
 * Tables:
 *  - id_chauff: nom, prenom, email, voiture, imma (plus optional columns if they exist)
 *  - id_user:   nom, prenom, email (plus optional columns if they exist)
 *
 * This file accepts POST either as application/x-www-form-urlencoded OR JSON body:
 *  - action=register_chauffeur
 *  - action=register_passager
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function read_input(): array {
  $data = $_POST;
  if (!empty($data)) return $data;

  $raw = file_get_contents('php://input');
  if (!$raw) return [];
  $decoded = json_decode($raw, true);
  return is_array($decoded) ? $decoded : [];
}

function env(string $key, string $default = ''): string {
  $v = getenv($key);
  if ($v === false || $v === null || $v === '') return $default;
  return $v;
}

function pdo(): PDO {
  // Configure these in your server env if possible:
  // ECORIDE_DB_HOST, ECORIDE_DB_NAME, ECORIDE_DB_USER, ECORIDE_DB_PASS
  $host = env('ECORIDE_DB_HOST', 'localhost');
  $db   = env('ECORIDE_DB_NAME', 'ecoride');
  $user = env('ECORIDE_DB_USER', 'root');
  $pass = env('ECORIDE_DB_PASS', '');

  $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
  $opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];
  return new PDO($dsn, $user, $pass, $opts);
}

function table_columns(PDO $pdo, string $table): array {
  // Returns array of column names for a table (MySQL).
  $stmt = $pdo->query("DESCRIBE `$table`");
  $cols = [];
  foreach ($stmt->fetchAll() as $row) {
    if (!empty($row['Field'])) $cols[] = (string)$row['Field'];
  }
  return $cols;
}

function pick_allowed(array $payload, array $allowedKeys): array {
  $out = [];
  foreach ($allowedKeys as $k) {
    if (array_key_exists($k, $payload) && $payload[$k] !== null && $payload[$k] !== '') {
      $out[$k] = $payload[$k];
    }
  }
  return $out;
}

function insert_row(PDO $pdo, string $table, array $values): void {
  if (empty($values)) {
    throw new RuntimeException('No values to insert.');
  }
  $cols = array_keys($values);
  $placeholders = array_map(fn($c) => ':' . $c, $cols);
  $sql = sprintf(
    "INSERT INTO `%s` (%s) VALUES (%s)",
    $table,
    implode(', ', array_map(fn($c) => "`$c`", $cols)),
    implode(', ', $placeholders)
  );
  $stmt = $pdo->prepare($sql);
  foreach ($values as $k => $v) {
    $stmt->bindValue(':' . $k, $v);
  }
  $stmt->execute();
}

// ---- Main ----
$input = read_input();
$action = (string)($input['action'] ?? '');
if ($action === '') {
  respond(400, ['ok' => false, 'error' => 'Missing action.']);
}

try {
  $pdo = pdo();
} catch (Throwable $e) {
  respond(500, ['ok' => false, 'error' => 'DB connection failed.', 'details' => $e->getMessage()]);
}

try {
  if ($action === 'register_chauffeur') {
    // Front-end keys -> DB columns mapping
    // vehiculeModele -> voiture
    // vehiculeImmat  -> imma
    $payload = [
      'nom' => trim((string)($input['nom'] ?? '')),
      'prenom' => trim((string)($input['prenom'] ?? '')),
      'email' => trim((string)($input['email'] ?? '')),
      'voiture' => trim((string)($input['vehiculeModele'] ?? $input['voiture'] ?? '')),
      'imma' => trim((string)($input['vehiculeImmat'] ?? $input['imma'] ?? '')),
      // Optional fields if your table has them:
      'vehiculeDateImmat' => $input['vehiculeDateImmat'] ?? null,
      'dateNaissance' => $input['dateNaissance'] ?? null,
      'cleConnexion' => $input['cleConnexion'] ?? null,
      'dateCreation' => $input['dateCreation'] ?? null,
    ];

    if ($payload['nom'] === '' || $payload['prenom'] === '' || $payload['email'] === '') {
      respond(422, ['ok' => false, 'error' => 'Missing required fields: nom, prenom, email.']);
    }
    if ($payload['voiture'] === '' || $payload['imma'] === '') {
      respond(422, ['ok' => false, 'error' => 'Missing required fields: voiture (vehiculeModele), imma (vehiculeImmat).']);
    }

    $cols = table_columns($pdo, 'id_chauff');
    $allowed = array_intersect(array_keys($payload), $cols);
    $values = pick_allowed($payload, $allowed);
    insert_row($pdo, 'id_chauff', $values);

    respond(200, ['ok' => true, 'table' => 'id_chauff']);
  }

  if ($action === 'register_passager') {
    $payload = [
      'nom' => trim((string)($input['nom'] ?? '')),
      'prenom' => trim((string)($input['prenom'] ?? '')),
      'email' => trim((string)($input['email'] ?? '')),
      // Optional:
      'cleConnexion' => $input['cleConnexion'] ?? null,
      'dateCreation' => $input['dateCreation'] ?? null,
    ];

    if ($payload['nom'] === '' || $payload['prenom'] === '' || $payload['email'] === '') {
      respond(422, ['ok' => false, 'error' => 'Missing required fields: nom, prenom, email.']);
    }

    $cols = table_columns($pdo, 'id_user');
    $allowed = array_intersect(array_keys($payload), $cols);
    $values = pick_allowed($payload, $allowed);
    insert_row($pdo, 'id_user', $values);

    respond(200, ['ok' => true, 'table' => 'id_user']);
  }

  respond(400, ['ok' => false, 'error' => 'Unknown action.']);
} catch (Throwable $e) {
  // Duplicate key, SQL errors, etc.
  respond(500, ['ok' => false, 'error' => 'DB operation failed.', 'details' => $e->getMessage()]);
}

