<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$email = isset($data['email']) ? trim($data['email']) : '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid email']);
  exit;
}

$entry = [
  'email' => $email,
  'notifyAtLaunch' => (bool)($data['notifyAtLaunch'] ?? false),
  'timestamp' => gmdate('c')
];

$file = __DIR__ . '/data/waitlist.jsonl';
$line = json_encode($entry) . "\n";

try {
  file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
  echo json_encode(['success' => true, 'message' => 'Thanks! You\'re on the list.']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Failed to save.']);
}
