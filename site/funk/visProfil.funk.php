<?php
require '../lib/medlem.class.php';

//Sender brukeren til login-siden om ikke innlogget
session_start();
if(!isset($_SESSION['bruker']['innlogget']) ||
    $_SESSION['bruker']['innlogget'] !== true) {
    header("Location: ./login.funk.php");
    exit();
}

//Henter obj fra session
$brukerObj = unserialize($_SESSION['bruker']['medlem']);
$brukerArr = $brukerObj->getArr();

$con = dbConnect();                             //Mysqli

if (isset($_POST['contact-send'])){             //Bildet er sendt

    $filNavn = $_FILES["profilbilde"]['name'];
    $filTmpNavn = $_FILES['profilbilde']['tmp_name'];
    $filType = $_FILES["profilbilde"]['type'];
    $filStr = $_FILES["profilbilde"]['size'];
    $filFeil = $_FILES["profilbilde"]['error'];

    $fileExt = explode('.', $filNavn);
    $fileActualExt = strtolower(end($fileExt)); //jpg eller png

    $tillat = array('jpg', 'png');

    $feilmeldinger = array();

    if (in_array($fileActualExt, $tillat)){  //Filtype
        if ($filFeil === 0){                 //Evt andre feil
            if ($filStr < 2000000){          //Filstr i bytes
                $riktigFormat = True;
            }
            else {$feilmeldinger[] = "Filen er for stor";}
        }
        else{$feilmeldinger[] = "En feil har oppstått";}
    }
    else {$feilmeldinger[] = "Ugyldig filtype";}


    if(empty($feilmeldinger)){
        $nyttNavn = $brukerArr['id'];
        $nyttNavn .= "." . $fileActualExt;

        $mappePath = "../img/" . $nyttNavn;

        $mappeRef = opendir('../img/');      //Mappe åpnes
        while($neste = readdir($mappeRef)){  //Sjekker filer i katalog
            
            //Fjerner gammelt bilde
            if (($brukerArr['id'] . ".jpg") == $neste)
            { unlink('../img/' . $neste);}
            if (($brukerArr['id'] . ".png") == $neste)
            { unlink('../img/' . $neste);}

        }

        //Flytter nytt til katalog
        move_uploaded_file($filTmpNavn, $mappePath);
        
        closedir($mappeRef);
        header("Refresh:0");  //Refresher side
        exit();

    }

}

?>


<html>
    <head>
    <h2>
        Min Profil 
    </h2>
    <h3>
        <?php echo $brukerArr['fornavn'] . " " . $brukerArr['etternavn']; ?><br>
    </h3>
    
    </head>

    <body>
        <p>
            <a href = "../../index.php">Tilbake til forsiden </a>
            <br>
        </p>

        <?php 
        $mappe = "../img/";     //Henviser til mappe

        $mappeRef = opendir($mappe);        //Mappe åpnes
        
        $funnet = FALSE; 
        while($neste = readdir($mappeRef)){ //Sjekker filer i katalog
            
            //Printer fil med riktig navn
            if (($brukerArr['id'] . ".jpg") == $neste)
            {echo '<img src="' . $mappe . $neste . 
                '" alt="Profilbilde" style="width:125px;height:125px;">';
                $funnet = TRUE;
            }
            if (($brukerArr['id'] . ".png") == $neste)
            {echo '<img src="' . $mappe . $neste . 
                '" alt="Profilbilde" style="width:125px;height:125px;">';
                $funnet = TRUE;
            }
        }

        if (!$funnet){
            echo '<img src="' . $mappe . '0.png" alt="Profilbilde" 
            style="width:125px;height:125px;">';
        }
        
        ?>

        <br>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>"
        method="post"
        enctype="multipart/form-data">

        <input type="file" name="profilbilde"><br>

        <button type="submit" name="contact-send"
        value="upload">Last opp nytt profilbilde</button>

        <?php if(!empty($feilmeldinger)){
            foreach ($feilmeldinger as $mld) {
                echo '<br>' . $mld . "<br>";
            }
        }
        ?>

        </form>


        <p>
            <?php
            $query  = "SELECT sted FROM postnummer WHERE nr = " . $brukerArr['postnummer'];
            $result = mysqli_query($con, $query);           
            $sted   = mysqli_fetch_all($result, MYSQLI_ASSOC);

            switch($brukerArr['kjonn']){
                case 0: $kjonn  = 'Kvinne'; break;
                case 1: $kjonn  = 'Mann';   break;
            }

            switch($brukerArr['kontigentstatus']){
                case 0: $kontigentstatus = 'Ikke Betalt'; break;
                case 1: $kontigentstatus = 'Betalt';      break;
            }

            echo '<b>Adresse:   </b>' . $brukerArr['adresse']     . 
            '<br><b>Postnummer: </b>' . $brukerArr['postnummer']  . 
            '<br><b>Poststed:   </b>' . $sted[0]['sted'] . '<br>' .
            '<br><b>E-post:     </b>' . $brukerArr['mail']        . 
            '<br><b>Telefon nr: </b>' . $brukerArr['tlf'] .'<br>' .

            '<br><b>Fødselsdato:</b>' . $brukerArr['fodselsdato'] .
            '<br><b>Kjønn:      </b>' . $kjonn           . '<br>' .
            '<br><b>Kontigentstatus:     </b>' . $kontigentstatus    .
            '<br><b>Medlem-siden dato: </b>' . $brukerArr['dato'];
            ?>



        </p>
    </body>
</html>