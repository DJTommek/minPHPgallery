<?php
/*
TODO
- ovládání klávesami
-- Posun po albu o 1: šipky "doleva" a "doprava"
-- Posun po albu o 10: "ctrl+doleva" a "ctrl+doprava"
-- Posun po albu začátek/konec: "home" a "end"
-- zoom: "+" a "-" (nechat co nejvíce věcí přímo na prohlížeči)
-- předchozí/další složka: "page up" a "page down"
- prezentace (automatický posun obrázků)
- plný náhled (aneb zoom)
- přehrání videa (HTML5 tag)
*/
/*///////////////////////////////////////////////////////////////
/*                          Nastavení
////////////////////////////////////////////////////////////////////////////////////////////////////*////////////////////////////////////////////////////////////////////////////////////////////////////*///////////////////////////////////////////////////////////////////////////////////////////////////
/* povolené přípony souborů (nezáleží na velikosti)
/*/ $imgEx = array('jpg', 'jpeg', 'png');
$text = array(
 'title'               => 'Fotogalerie - NSA210',
 'root'                => 'Domů',
 'back'                => 'Zpět',
 'doesnotExistsFolder' => 'Složka neexistuje',
 'doesnotExistsFile'   => 'Soubor neexistuje',
 'noAllowedFile'       => 'Jedná se o nepovolenou příponu, není možné obrázek zobrazit.<br> Povolené přípony jsou:',
 'imgMoveFirst'        => '<|',
 'imgMoveBefore10'     => '< -10',
 'imgMoveBefore1'      => '< -1',
 'imgMoveNext1'        => '+1 >',
 'imgMoveNext10'       => '+10 >',
 'imgMoveLast'         => '|>',
 '' => ''
 );
/*///////////////////////////////////////////////////////////////
/*                      Konec nastavení
/*///////////////////////////////////////////////////////////////
function makeUrl($path){
  return '?path='.stripDot($path);
}
function stripDot($string){
  return str_replace("./", "", $string);
}
define('RE_ALLOWED_EX', "/^(\.\/)?.*\.(".implode('|', $imgEx).")$/i");
$path = $_GET['path'];
$path = preg_replace("(//)", "/", $path);
//pokus dostat se o složku zpět nebo do rootu, tj. tam, kde nemá co dělat!
//možná přidat logování do souboru
if(preg_match("((\.\.)|(^\/))", $path)){
  header('HTTP/1.1 301 Moved Permanently');
  header('Location: '.dirname($_SERVER["PHP_SELF"]));
  header('Connection: close');
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <title><?php echo $text['title']; ?></title>
</head>
<body>
<style>
body{
  max-height: 100%;
  max-width: 100%;
  font-family: 'Courier New', 'Courier', 'Andale Mono', 'monospace';
}
.nobr{
  white-space: nowrap;
}
</style>
<?php
////////////////////////////////////////////
//       Výpis cesty k souboru/složce    //
////////////////////////////////////////////
  $pathArray = explode("/", $path);
  echo '<h3>';
  foreach($pathArray as $key=>$value) {
    if($key == 0){
      echo '<a href="?path=">'.$text['root'].'</a> / ';
    }
    if($key > 0){
      $pathToFolderArray = array_slice($pathArray, 0, $key);
      $pathToFolder = implode("/", $pathToFolderArray);
      echo '<a href="'.makeUrl($pathToFolder.'/').'">'.end($pathToFolderArray).'</a> / ';
    }
  }
  echo '</h3>';
  if($path != '' && !file_exists($path)){
    if(preg_match("(/$)", $path)){
      die($text['doesnotExistsFolder']);
    }
    else{
      die($text['doesnotExistsFile']);
    }
  }
////////////////////////////////////////////
//          prohlížení souboru            //
////////////////////////////////////////////
//if(!preg_match("((\/)$)", $path)){
if($path != '' && !preg_match("(\/$)", $path)){
?>
<style>
body{
  margin: 0 auto;
  text-align: center;
}
img{
  max-width: 100%;
  max-height: 80%;
}
</style>
<?php
  //další & předchozí
  $pathArray = explode("/", $path);
  if(count($pathArray) == 1){ //jedná-li se o root (tj. přímo název souboru)
    $allFiles = glob('*.*');
  }
  else{
    $pathToFolderArray = array_slice($pathArray, 0, (count($pathArray) - 1));
    $pathToFolder = implode("/", $pathToFolderArray);
    $allFiles = glob('./'.$pathToFolder.'/*.*');
  }
  //echo '<p>Zpět do složky <a href="'.makeUrl($pathToFolder.'/').'">'.$pathToFolder.'</a></p>';
  $files = array();
  foreach($allFiles as $key=>$value) {
    if(preg_match(RE_ALLOWED_EX, $value)){
      $files[] = $value;
    }
  }
  if(is_array($files)){
    $navigation = '';
    $filesCount = count($files);
    foreach($files as $key=>$value){
      if($path == stripDot($value)){
        $navigation .= '<div class="navigation">';
        //první
        if($key != 0){
          $imgBefore0 = $files[0];
          $navigation .= '<a class="move" href="'.makeUrl($imgBefore0).'">'.$text['imgMoveFirst'].'</a> | ';
        }
        else{
          $navigation .= preg_replace("(.)", '&nbsp;', $text['imgMoveFirst']).'&nbsp;&nbsp;&nbsp;';
        }
        //o 10 zpět
        $imgBefore10 = $files[$key-10];
        if($imgBefore10 != ''){
          $navigation .= '<a class="move" href="'.makeUrl($imgBefore10).'">'.$text['imgMoveBefore10'].'</a> | ';
        }
        else{
          $navigation .= preg_replace("(.)", '&nbsp;', $text['imgMoveBefore10']).'&nbsp;&nbsp;&nbsp;';;
        }
        //předchozí
        $imgBefore = $files[$key-1];
        if($imgBefore != ''){
          $navigation .= '<a class="move" href="'.makeUrl($imgBefore).'">'.$text['imgMoveBefore1'].'</a>';
        }
        else{
          $navigation .= preg_replace("(.)", '&nbsp;', $text['imgMoveBefore1']);
        }
        //aktuální
        $navigation .= ' <br> ';
        $navigation .= '<u>'.end($pathArray).'</u>';
        $navigation .= ' ('.($key+1).'/'.$filesCount.')';
        $navigation .= ' <br> ';
        //další
        $imgNext = $files[$key+1];
        if($imgNext != ''){
          $navigation .= '<a class="move" href="'.makeUrl($imgNext).'">'.$text['imgMoveNext1'].'</a>';
        }
        else{
          $navigation .= preg_replace("(.)", '&nbsp;', $text['imgMoveNext1']);
        }
        //o 10 vpřed
        $imgNext10 = $files[$key+10];
        if($imgNext10 != ''){
          $navigation .= ' | <a class="move" href="'.makeUrl($imgNext10).'">'.$text['imgMoveNext10'].'</a>';
        }
        else{
          $navigation .= '&nbsp;&nbsp;&nbsp;'.preg_replace("(.)", '&nbsp;', $text['imgMoveNext10']);
        }
        //poslední
        if($key != count($files) - 1){
          $imgNext0 = $files[(count($files) - 1)];
          if($imgNext0 != ''){
            $navigation .= ' | <a class="move" href="'.makeUrl($imgNext0).'">'.$text['imgMoveLast'].'</a>';
          }
          else{
            $navigation .= preg_replace("(.)", '&nbsp;', $text['imgMoveLast']);
          }
        }
        $navigation .= '</p>';
      }
    }
  }
  $navigation .= '<div>';
  echo $navigation;
  if(preg_match(RE_ALLOWED_EX, $path)){
    echo '<img src="'.$path.'"><br>';
  }
  else{
    echo $text['noAllowedFile'].' '.implode(", ", $imgEx);
  }
  echo $navigation;
}
else{
////////////////////////////////////////////
//          prohlížení složky             //
////////////////////////////////////////////
  //folders
  $folders = glob('./'.$path.'*', GLOB_ONLYDIR);
  if($path != ''){
    echo '&nbsp;↳ &nbsp;&nbsp;/ <a href="?path=">'.$text['root'].'</a><br>';
    $pathToFolderArray = array_slice($pathArray, 0, $key-1);
    $pathToFolder = implode("/", $pathToFolderArray);
    echo '&nbsp;↳ ../ <a href="'.makeUrl($pathToFolder.'/').'">'.$text['back'].'</a><br>';
  }
  else{
    //echo 'Vítej v rodinné fotogalerii.<br /><br />';
    echo '↑ Aktuální složka <br>';
    echo '↓ Procházení adresářů (složek)<br />';
  }
  echo '<hr>';
  if(is_array($folders)){
    foreach($folders as $key=>$value){
      $pathArray = explode("/", $value);
      echo '&nbsp;↳ <a href="'.makeUrl($value.'/').'">'.end($pathArray).'/</a><br>';
    }
  }
  else{
    //echo 'NoFolders...<br>';
  }
  //files
  $allFiles = glob('./'.$path.'*.*');
  $files = array();
  foreach ($allFiles as $key=>$value) {
    if(preg_match(RE_ALLOWED_EX, $value)){
      $files[] = $value;
    }
  }
  if(is_array($files)){
    foreach($files as $key=>$value){
      $pathArray = explode("/", $value);
      echo '<a href="'.makeUrl($value).'">'.end($pathArray).'</a><br>';
    }
  }
  else{
    //echo 'NoFiles...<br>';
  }
}
?>
</body>
</html>
