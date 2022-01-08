<?php
require '../lib/medlem.class.php';

session_start();

if(!isset($_SESSION['bruker']['innlogget']) ||          //Sjekker om innlogget
    ($_SESSION['bruker']['innlogget'] !== true)) {
    header("Location: ./login.funk.php");
    exit();
}
$brukerObj = unserialize($_SESSION['bruker']['medlem']);
    $brukerArr = $brukerObj->getArr();

if (!in_array('admin', $brukerArr['roller'])){          //Sjekker om admin
    header("Location: ../../index.php");
    exit();
}


function settVerdi($i){
    if (isset($_POST[$i])) {echo $_POST[$i];} 
}

if (isset($_POST['contact-send'])){
    $feilmeldinger = medlem::sjekkOmGyldig($_POST);     //Ser etter feilmeldinger

    if(empty($feilmeldinger)){
        $medlem = medlem::lagMedlem($_POST);            //Lager obj med verdiene
        
        $medlem->sendTilDB();

        foreach ($_POST as $k=>$v) {                    //Sletter data i $_post
            unset($_POST[$k]);
        }

        echo "<b>Databesen er oppdattert</b><br>";

    }
    else {    
        //Printer feilmeldinger
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
        <title>Nytt medlem</title>
    </head>

    <body>
        <p>
            <a href = "../../index.php">Tilbake til forsiden </a>
            <br>
        </p>
        
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        
        <p>             <!––Input sendes med $_POST -->
            <label for="fornavn">Fornavn</label>
            <input name="fornavn" type="text"       
                value="<?php settVerdi("fornavn"); ?>">
        
            <label for="etternavn">Etternavn</label>
            <input name="etternavn" type="text"     
                value="<?php settVerdi("etternavn"); ?>">
        
        <p>
            <label for="adresse">Gateadresse</label>
            <input name="adresse" type="text"       
                value="<?php settVerdi("adresse"); ?>">
        
            <label for="postnummer">Postnummer</label>
            <input name="postnummer" type="number"    
                value="<?php settVerdi("postnummer"); ?>">
        
        <p>
            <label for="tlf">Telefonnummer</label>
            <input name="tlf" type="number"           
                value="<?php settVerdi("tlf"); ?>">
        
            <label for="mail">E-post</label>
            <input name="mail" type="text"          
                value="<?php settVerdi("mail"); ?>">
        
        <p>
            <label for="fodselsdato">Fødselsdato</label>
            <input name="fodselsdato" type="date"   
                value="<?php settVerdi("fodselsdato"); ?>">


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
                in_array(1, $_POST["interesser"]))){
                echo "selected";}?>>Fotball</option>

                <option value="2" <?php if ((isset($_POST["interesser"]) && 
                in_array(2, $_POST["interesser"]))){
                echo "selected";}?>>Dart</option>

                <option value="3" <?php if ((isset($_POST["interesser"]) && 
                in_array(3, $_POST["interesser"]))){
                echo "selected";}?>>Biljard</option>

                <option value="4" <?php if ((isset($_POST["interesser"]) && 
                in_array(4, $_POST["interesser"]))){
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
                    echo '<option value=' . $verdi['id'] . " ";
                    
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
                in_array(1, $_POST["roller"]))){
                echo "selected";}?>>Admin</option>

                <option value="2" <?php if ((isset($_POST["roller"]) && 
                in_array(2, $_POST["roller"]))){
                echo "selected";}?>>Leder</option>

                <option value="3" <?php if ((isset($_POST["roller"]) && 
                in_array(3, $_POST["roller"]))){
                echo "selected";}?>>Medlem</option>
            </select>

        <p>
            <label for="dato">Medlem-siden dato</label><br>
            <input name="dato" type="date"          
                value="<?php settVerdi("dato"); ?>">
        
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
            </select>
        
        <p>               <!––"send" knapp -->
            <button type="submit" name="contact-send">Send</button>                       
        </p>
</form>
</body> 
</html>