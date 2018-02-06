<?php
ini_set('memory_limit', '128M');

$locale = setlocale(LC_CTYPE, "");
//$locale = wxSetlocale(LC_CTYPE, "");
//echo $locale;exit;

require 'src/autoload.php';

wxInitAllImageHandlers();

//$main = new wxScrolledWindowDemo();
$main = new ImageDownFrame();
$main->SetMinClientSize($main->GetMinSize());
$main->Show();
$main->Maximize();

wxEntry();