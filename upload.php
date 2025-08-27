<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/db.php';
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/auth_mock.php';
license_check();
header('Content-Type: application/json');

function fail($code, $msg){
  http_response_code($code);
  echo json_encode(['error'=>$msg]);
  exit;
}

if($_SERVER['REQUEST_METHOD']!=='POST') { fail(405,'Method not allowed'); }

$userId = current_user_id();
$slug = current_user_folder_slug();

if(empty($_FILES['pdf']['name'])){ fail(400,'No file'); }
if($_FILES['pdf']['error']!==UPLOAD_ERR_OK){ fail(400,'Upload error'); }
if($_FILES['pdf']['size'] > 50*1024*1024){ fail(413,'File too large (>50MB)'); }

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $_FILES['pdf']['tmp_name']);
if($mime!=='application/pdf'){ fail(400,'Only PDF allowed'); }

$orig = $_FILES['pdf']['name'];
$store = time().'_'.bin2hex(random_bytes(4)).'.pdf';

$dest = STORAGE_DIR.'/'.$slug.'/'.$store;
@mkdir(STORAGE_DIR.'/'.$slug, 0775, true);
move_uploaded_file($_FILES['pdf']['tmp_name'], $dest);

$sha = hash_file('sha256', $dest);
db_exec("INSERT INTO documents(user_id,filename,original_name,size_bytes,mime,sha256) VALUES(?,?,?,?,?,?)",
  [$userId,$store,$orig,$_FILES['pdf']['size'],$mime,$sha]);

echo json_encode(['ok'=>true]);
