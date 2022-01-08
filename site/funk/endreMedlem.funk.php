<?php
require '../lib/medlem.class.php';

session_start();

//Sender brukeren til login-siden  om ikke innlogget
if(!isset($_SESSION['bruker']['innlogget']) ||       
    ($_SESSION['bruker']['innlogget'] !== true)) {
    header("Location: ./login.funk.php");
    exit();
}
$brukerObj = unserialize($_SESSION['bruker']['medlem']);
    $brukerArr = $brukerObj->getArr();

//Sender brukeren til forsiden om ikke innlogget som admin
if (!in_array('admin', $brukerArr['roller'])){ 
    header("Location: ../../index.php");
    exit();
}

//Sjekker om cookie er laget
if (!isset($_COOKIE['mail'])){                      
    header("Location: velgEndring.funk.php");
    exit();
}



function hentVerdi($i){         //Sjekker om index fins
    if (isset($_POST[$i])) {echo $_POST[$i];} //Printer i <form>
}

//Deklarerer obj utenfor løkke
$medlemObj; 

//Henter medlem med gitt mail fra db
if (isset($_COOKIE['mail'])){  
    $medlemObj = medlem::medlemFraDB($_COOKIE['mail']);
    $medlemObj->verdiTilID();
}       


if (isset($_POST['contact-send'])) {

    //Sjekker om feilmeldinger
    $feilmeldinger = medlem::sjekkOmGyldig($_POST);
    $endringer = array();            //Array med endringer       

    if(empty($feilmeldinger)){      

        //Henter verdier fra obj
        $medlemArr = $medlemObj->getArr();
        
        //Looper og sjekker om atributter er endret
        $arr = array("interesser", "aktiviteter", "roller");
        foreach($arr as $kategori){
            if (!empty($medlemArr[$kategori]) && isset($_POST[$kategori])){
                sort($_POST[$kategori]); sort($medlemArr[$kategori]);

                if((array_count_values($medlemArr[$kategori])) != 
                   (array_count_values($_POST[$kategori]))){
                        $endringer[] = $kategori;
                }elseif (!empty(array_diff_assoc($medlemArr[$kategori], $_POST[$kategori]))){
                    $endringer[] = $kategori;

                }elseif($_POST[$kategori] != $_POST[$kategori]){
                     $endringer[] = $kategori;
                }
            }elseif(!empty($medlemArr[$kategori]) xor isset($_POST[$kategori])){
                $endringer[] = $kategori;
            }
        }

        //Sjekker om andre verdier er endret
        foreach($medlemArr as $k => $v){    
            if (($k != "id") && ($k != "interesser") && 
            ($k != "roller") && ($k != "aktiviteter")){
                if ($medlemArr[$k] != $_POST[$k]){ 
                    $endringer[] = $k;
                }   
            }
        }            

        //Oppdatterer $medlemObj
        $_POST['id'] = $medlemArr['id'];
        $medlemObj = medlem::lagMedlem($_POST);
        $medlemObj->verdiTilID();
        

        //Oppdatterer DB om det er gjort endringer
        if(!empty($endringer)){
            $medlemObj->endreDB($endringer);
        }
    }
    else {
        //Skriver ut evt. feilmeldinger
        foreach($feilmeldinger as $feilmelding){
            echo "<br>" . $feilmelding;
        }
    }   

    //Tilbakemelding om endret
    if((!empty($endringer)) && (empty($feilmeldinger))){                
        echo ("<br>Medlemmet er endret!<br>");
    }
    else{
        echo "<br>Medlemmet er ikke endret!<br>";
    }        
    $_POST = $medlemObj->getArr();
}

//Sendes til form med post
else{   

    $_POST = $medlemObj->getArr();
}             
?>






<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Endre medlem</title>
    </head>
    <body>

    <p>
        <a href = "../../index.php">Tilbake til forsiden </a>
    </p>
    
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">

        <p>                             <!––Henter fra/sender med $_POST -->
            <label for="fornavn">Fornavn</label>
            <input name="fornavn" type="text"           
                value="<?php hentVerdi("fornavn"); ?>">
            
            <label for="etternavn">Etternavn</label>
            <input name="etternavn" type="text"         
                value="<?php hentVerdi("etternavn"); ?>">
            
        <p>
            <label for="adresse">Gateadresse</label>
            <input name="adresse" type="text"           
                value="<?php hentVerdi("adresse"); ?>">
            
            <label for="postnummer">Postnummer</label>
            <input name="postnummer" type="number"      
                value="<?php hentVerdi("postnummer"); ?>">
            
        <p>
            <label for="tlf">Telefonnummer</label>
            <input name="tlf" type="number"             
                value="<?php hentVerdi("tlf"); ?>">
            
            <label for="mail">E-post</label>
            <input name="mail" type="text"              
                value="<?php hentVerdi("mail"); ?>">
            
        <p>
            <label for="fodselsdato">Fødselsdato</label>
            <input name="fodselsdato" type="date"   
                value="<?php hentVerdi("fodselsdato"); ?>">


                        <!––Bruker velger en av to verdier -->    
            <label for="kjonn">Kjønn</label>
            <select name="kjonn">       
                <option value="" disabled selected>Velg Kjønn</option>
                <option value="1"<?php if ((isset($_POST["kjonn"]) && 
                        $_POST["kjonn"] == "1")){
                        echo "selected";}?>>Mann</option>
                <option value="0"<?php if ((isset($_POST["kjonn"]) && 
                        $_POST["kjonn"] == "0")){
                        echo "selected";}?>>Kvinne</option>
            </select>

        <p>             <!––Sender valgte alternativer i array -->
            <label for="interesser[]">Interesser</label><br>
            <select multiple name="interesser[]">  

                <option value="1" <?php if ((isset($_POST["interesser"]) && 
                in_array("1", $_POST["interesser"]))){
                echo "selected";}?>>Fotball</option>

                <option value="2" <?php if ((isset($_POST["interesser"]) && 
                in_array("2", $_POST["interesser"]))){
                echo "selected";}?>>Dart</option>

                <option value="3" <?php if ((isset($_POST["interesser"]) && 
                in_array("3", $_POST["interesser"]))){
                echo "selected";}?>>Biljard</option>

                <option value="4" <?php if ((isset($_POST["interesser"]) && 
                in_array("4", $_POST["interesser"]))){
                echo "selected";}?>>Dans</option>
            </select>

        <p>
            <label for="aktiviteter[]">Kursaktiviteter</label><br>
            <select multiple name="aktiviteter[]" > 
                
                <?php //Henter alternativer fra DB
                $con = dbConnect();
                $query = "SELECT id, navn FROM aktiviteter";

                $result = mysqli_query($con, $query);           
                $rader = mysqli_fetch_all($result, MYSQLI_ASSOC);

                mysqli_free_result($result);                                 //frigir minne
                mysqli_close($con);  
                
                foreach($rader as $index => $verdi){
                    echo '<option value=' . $verdi['id'] . ' ';
                    
                    if ((isset($_POST["aktiviteter"]) && 
                    in_array($verdi['id'], $_POST["aktiviteter"]))){
                        echo "selected";
                    }

                    echo '>' . $verdi['navn'] . '</option>';
                
                }
                ?>

            </select>
        <p>

            <label for="roller[]">Roller</label><br>
            <select multiple name="roller[]">

                <option value="1" <?php if ((isset($_POST["roller"]) && 
                in_array("1", $_POST["roller"]))){
                echo "selected";}?>>Admin</option>

                <option value="2" <?php if ((isset($_POST["roller"]) && 
                in_array("2", $_POST["roller"]))){
                echo "selected";}?>>Leder</option>

                <option value="3" <?php if ((isset($_POST["roller"]) && 
                in_array("3", $_POST["roller"]))){
                echo "selected";}?>>Medlem</option>
            </select>

        <p>
            <label for="dato">Medlem-siden dato</label><br>
            <input name="dato" type="date"          
                value="<?php hentVerdi("dato"); ?>">
        
        <p>
        
            <label for="kontigentstatus">Kontigentstatus</label><br>
            <select name="kontigentstatus">
                <option value="" disabled >Velg Kontigentstatus</option>
                <option value="1" <?php if ((isset($_POST["kontigentstatus"]) &&                         
                        ($_POST["kontigentstatus"] == "1"))){
                        echo "selected";}?>>Betalt</option>
                <option value="0" <?php if ((isset($_POST["kontigentstatus"]) &&                         
                        ($_POST["kontigentstatus"] == "0"))){
                        echo "selected";}?>>Ikke betalt</option>
            </select><br>

        <p>

                        <!––"send" knapp -->
                <button type="submit" name="contact-send">Send endringer</button> 
        </p>
    </form>
    </body> 
</html>