<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/db.php';
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/auth_mock.php';
license_check();

// Endpoint used via AJAX to create shareable or embeddable links

header('Content-Type: application/json');

function fail($code,$msg){
  http_response_code($code);
  echo json_encode(['error'=>$msg]);
  exit;
}

if($_SERVER['REQUEST_METHOD']!=='POST') { fail(405,'Method not allowed'); }
$docId = (int)($_POST['doc_id']??0);
$kind  = $_POST['kind']??'view';
$allow_view = isset($_POST['allow_view']) ? 1 : 0;
$allow_download = isset($_POST['allow_download']) ? 1 : 0;
$allow_search = isset($_POST['allow_search']) ? 1 : 0;

if($docId<=0){ fail(422,'Invalid document'); }
if(!in_array($kind,['view','embed'],true)){ fail(422,'Invalid kind'); }

$doc = db_row("SELECT d.*, u.folder_slug FROM documents d JOIN users u ON u.id=d.user_id WHERE d.id=?", [$docId]);
if(!$doc){ fail(404,'Doc not found'); }

$slug = slug(10);
db_exec("INSERT INTO links(doc_id,kind,slug,allow_view,allow_download,allow_search) VALUES(?,?,?,?,?,?)",
  [$docId,$kind,$slug,$allow_view,$allow_download,$allow_search]);

$url = ($kind==='view') ? APP_BASE_URL.'/view.php?s='.$slug : APP_BASE_URL.'/embed.php?s='.$slug;
$embed = null;
if($kind==='embed'){
  $embed = '<iframe src="'.$url.'" width="100%" height="800" style="border:0" allowfullscreen></iframe>';
}

echo json_encode(['url'=>$url,'embed'=>$embed]);
