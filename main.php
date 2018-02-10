<?php
ini_set('memory_limit', '128M');

$locale = setlocale(LC_CTYPE, "");
//$locale = wxSetlocale(LC_CTYPE, "");
//echo $locale;exit;

require 'src/autoload.php';

wxInitAllImageHandlers();

$icon = __DIR__ . '/assets/logo.ico';
//$main = new wxScrolledWindowDemo();
$main = new ImageDownFrame();
$main->SetIcon(new wxIcon($icon, wxBITMAP_TYPE_ICO));
$main->SetMinClientSize($main->GetMinSize());
$main->Show();
$main->Maximize();

wxEntry();