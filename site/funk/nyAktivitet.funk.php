<?php
require '../lib/aktivitet.class.php';

function settVerdi($i){
    if (isset($_POST[$i])) {echo $_POST[$i];} 
}

session_start();

if(!isset($_SESSION['bruker']['innlogget']) ||          //Sjekker om innlogget
    ($_SESSION['bruker']['innlogget'] !== true)) {
    header("Location: ./login.funk.php");
    exit();
}
$brukerObj = unserialize($_SESSION['bruker']['medlem']);
    $brukerArr = $brukerObj->getArr();

if ((!in_array('admin', $brukerArr['roller'])) && 
    (!in_array('leder', $brukerArr['roller']))){     //Sjekker om admin
    header("Location: ../../index.php");
    exit();
}

if (isset($_POST['contact-send'])){
    $feilmeldinger = aktivitet::sjekkOmGyldig($_POST);

    if(empty($feilmeldinger)){
        $aktivitet = aktivitet::lagAktivitet($_POST);
        $aktivitet->sendTilDB();

        foreach ($_POST as $k=>$v) {        //Sletter data i $_post
            unset($_POST[$k]);
        }

        echo "<b>Databesen er oppdattert</b><br>";

    }
    else {    
        echo "<b>Venligst fyll inn alle feltene riktig:</b><br>";
        foreach($feilmeldinger as $feilmelding){
            echo $feilmelding . "<br>";
        }
    }
}


?>


<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Ny aktivitet</title>
    </head>

    <body>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <p>
            <a href = "../../index.php">Tilbake til forsiden </a>
            <br>

        <p>             <!––Input sendes med $_POST -->
            <label for="aktivitet">Navn på aktivitet</label><br>
            <input name="aktivitet" type="text"       
                value="<?php settVerdi("fornavn"); ?>">
                
        <p>
            <label for="leder">Leder</label><br>
            <select  name="leder" > 
                
                <?php 
                $con = dbConnect();
                $query = "SELECT id, fornavn, etternavn FROM `medlemmer` 
                JOIN rolleregister ON rolleregister.mid = medlemmer.id
                WHERE rolleregister.rid = 2";

                $result = mysqli_query($con, $query);           
                $rader = mysqli_fetch_all($result, MYSQLI_ASSOC);
                mysqli_free_result($result);  
                mysqli_close($con);      
                echo '<option value="" disabled selected>Velg ansvarlig</option>';
                
                foreach($rader as $leder){
                    echo '<option value="' . $leder['id'] . '">' . 
                    $leder['fornavn'] . "  " . $leder['etternavn'] . '</option>';
                }
                ?>

            </select>

        <p>
            <label for="dato">Dato</label><br>
            <input name="dato" type="date"          
                value="<?php settVerdi("dato"); ?>">
        
        <p>              
            <button type="submit" name="contact-send">Send</button>                       
        </p>
        </form>
    </body> 
</html>