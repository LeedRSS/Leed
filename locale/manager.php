<?php

/*
 @nom: manager
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Outil de gestion des fichiers langues
 */
header('Content-Type: text/html; charset=utf-8');
require_once('../Functions.class.php');

require_once("../common.php");
if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));

if(isset($_['1234567890saveButton'])){
    unset($_['1234567890saveButton']);
    $currentLangage = $_['1234567890currentLangage'];
    unset($_['1234567890currentLangage']);
    file_put_contents($currentLangage.'.json', pretty_json_encode($_));
}


$_ = array_map('Functions::secure',array_merge($_GET, $_POST));
$files = glob('*.json');
$currentLangage = (isset($_['1234567890currentLangage'])?json_decode(file_get_contents($_['1234567890currentLangage'].'.json'),true):array());
ksort($currentLangage);

$missingTags = array();



$missingTags = scanTags('../');



function scanTags($dir){
    $return = array();
    $extensions = array('html','php','js');
    $leedFiles = scandir($dir);
    foreach($leedFiles as $file){
        if($file!='.' && $file!='..' && $file!='.git'){
            if(is_dir($dir.$file)){
                $return = array_merge($return,scanTags($dir.$file.'/'));
            }else{
                $ext = str_replace('.rtpl.php','.wrongphp',$file);
                $ext = strtolower(substr($ext,strrpos($ext,'.')+1));
                if(in_array($ext, $extensions)){
                    $content = file_get_contents($dir.$file);
                    if(preg_match_all("#_t\(([\'\\\"])([a-zA-Z0-9\s_\-]+)([\'\\\"])(?:,.+)?\)#", $content, $match)){
                        //var_dump($dir.$file.'-->',$match[2]);
                        $return = array_merge($return,$match[2]);
                    }
                }
            }
        }
    }
    $return = array_unique($return);
    return $return;
}
?>


<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Gestionnaire de langues</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="../templates/marigolds/css/style.css">
    <style>
        code {
            color:#000;
            font-size: 1em;
        }
        .tradTab{
            width:100%;
            font-size: 10px;
        }
        .tradTab .value{
            width:70%;
        }
        .tradTab .value input,.tradTab .value textarea{
            width:98%;
        }
        .diffTab{
            width:100%;
        }
        .diffTab th{
            background-color: #222222;
            color:#ffffff;
            font-weight: bold;
        }
        .diffTab th,.diffTab td{
            padding:5px;
            border:1px solid #cecece;
        }
    </style>
    <script src="../templates/marigolds/js/libs/jqueryAndModernizr.min.js"></script>
</head>
<body>
<div class="global-wrapper">
    <div id="header-container">
        <header class="wrapper clearfix">
            <h1 class="logo" id="title"><a href="./index.php">L<i>eed</i></a></h1>
            <nav>
            </nav>
        </header>
    </div>


    <div id="main-container">

<div id="main" class="wrapper clearfix">






<div id="menuBar">
            <aside>
                <h3 class="left">Vérifications</h3>
                <ul class="clear" style="margin:0">

                        <?php
                        $test = array();
                        if(!is_writable('./')){
                            $test['Erreur'][]='Écriture impossible dans le répertoire Leed, veuillez ajouter les permissions en écriture sur tout le dossier, pensez à blinder les permissions par la suite)';
                        }

                        if (!@function_exists('file_get_contents')){
                             $test['Erreur'][] = 'La fonction requise "file_get_contents" est inaccessible sur votre serveur, vérifiez votre version de PHP.';
                        }
                        if (!@function_exists('file_put_contents')){
                             $test['Erreur'][] = 'La fonction requise "file_put_contents" est inaccessible sur votre serveur, vérifiez votre version de PHP.';
                        }
                        if (@version_compare(PHP_VERSION, '5.1.0') <= 0){
                         $test['Erreur'][] = 'Votre version de PHP ('.PHP_VERSION.') est trop ancienne, il est possible que certaines fonctionnalités du script comportent des dysfonctionnements.';
                        }


                        if(ini_get('safe_mode') && ini_get('max_execution_time')!=0){
                            $test['Erreur'][] = 'Le script ne peux pas gérer le timeout tout seul car votre safe mode est activé,<br/> dans votre fichier de configuration PHP, mettez la variable max_execution_time à 0 ou désactivez le safemode.';
                        }

                        foreach($test as $type=>$messages){
                        ?>
                        <li style="font-size:10px;color:#ffffff;background-color:<?php echo ($type=='Erreur'?'#F16529':'#008000'); ?>"><?php echo $type; ?> :<ul><?php foreach($messages as $message){?><li style="border:1px solid #212121"><?php echo $message; ?></li><?php } ?></ul></li><li>&nbsp;</li>
                        <?php }  ?>


                        <form action="#" method="POST">
                        <h2>Ouvrir une langue</h2>
                        <select name="1234567890currentLangage">
                            <?php

                            foreach($files as $file){
                            $file = str_replace('.json', '', $file);
                                ?>
                            <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
                            <?php } ?>
                        </select>
                            <input type="submit" value="Ouvrir" class="button">
                        </form>

                </ul>
            </aside>
</div>

    <form action="#" method="POST">
            <article>
                <header>
                    <h1>Fichier langue de Leed <input type="text" value="<?php echo @$_['1234567890currentLangage'] ; ?>" name="1234567890currentLangage"><input type="submit" name="1234567890saveButton" value="Enregistrer" class="button"></h1>

                </header>

                <section>
                    <h2>Clée présentes</h2>
                    <table class="tradTab">
                        <?php foreach($currentLangage as $key=>$value){ ?>
                        <tr>
                            <td><?php echo $key; ?></td>
                            <td class="value">
                                <?php $value = htmlentities($value,ENT_COMPAT,'UTF-8');
                                if(strlen($value)>100){
                                    ?>
                                    <textarea  name="<?php echo $key; ?>"><?php echo $value; ?></textarea>
                                    <?php
                                }else{
                                    ?>
                                    <input type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                </section>

                <section>
                    <h2>Clés absentes/différences</h2>
                    <p>Différences entre les traductions du fichier langue et les
                        traductions trouvées dans les fichiers de Leed</p>
                        <strong>Nb: Ce différentiel est la à titre informatif, il peut se tromper, merci de vérifier la véracité des différences.</strong>

                        <table class="diffTab">
                            <tr>
                                <th>Fichier Langue ( <?php echo count($currentLangage) ?> Tags)</th>
                                <th>Leed ( <?php echo count($missingTags) ?> Tags)</th>
                            </tr>

                    <?php
                    foreach ($currentLangage as $key => $value) {
                        if(!in_array($key, $missingTags)){
                            echo '<tr><td>'.$key.'</td><td>-</td></tr>';
                        }
                    }

                    foreach ($missingTags as $key => $value) {
                        if(!isset($currentLangage[$value])){
                            echo '<tr><td>'.$value.'</td><td>-</td></tr>';
                        }
                    }
                    ?>
                </section>


            </article>
    </form>


        </div> <!-- #main -->


    </div> <!-- #main-container -->

    <div id="footer-container">
        <footer class="wrapper">
            <p>Leed "Light Feed" by <a target="_blank" href="http://blog.idleman.fr">Idleman</a></p>
        </footer>
    </div>
</div>

<script>window.jQuery || document.write('<script src="../js/libs/jquery-1.7.2.min.js"><\/script>'); </script>

<script src="../templates/marigolds/js/script.js"></script>
</body>
</html>

<?php

    function pretty_json_encode($json) {
    array_walk_recursive($json, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
    $json = mb_decode_numericentity(json_encode($json), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = ' ';
    $newLine = "\n";
    $prevChar = '';
    $outOfQuotes = true;
    for ($i=0; $i<=$strLen; $i++) {
    $char = substr($json, $i, 1);
    if ($char == '"' && $prevChar != '\\') {
    $outOfQuotes = !$outOfQuotes;
    } else if(($char == '}' || $char == ']') && $outOfQuotes) {
    $result .= $newLine;
    $pos --;
    for ($j=0; $j<$pos; $j++) {
    $result .= $indentStr;
    }
    }
    $result .= $char;
    if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
    $result .= $newLine;
    if ($char == '{' || $char == '[') {
    $pos ++;
    }
    for ($j = 0; $j < $pos; $j++) {
    $result .= $indentStr;
    }
    }
    $prevChar = $char;
    }
    return $result;
    }
?>
