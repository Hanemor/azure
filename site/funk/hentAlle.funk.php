<?php
require '../inc/mysqli.inc.php';

session_start();

//Sender brukeren til login-siden  om ikke innlogget
if(!isset($_SESSION['bruker']['innlogget']) ||
    $_SESSION['bruker']['innlogget'] !== true) {
    header("Location: ./login.funk.php");
    exit();
}

//Spørring - Medlemmer
$sql = 'SELECT id, fornavn, etternavn, 
tlf, mail, fodselsdato, 
medlemSidenDato, kontigentstatus 
FROM medlemmer 
ORDER BY kontigentstatus DESC, id';     
                         
//Henter med spørring
$con = dbConnect();
$result = mysqli_query($con, $sql);       
$medlemmer = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);              
$con->close();


?>



<!doctype html>
<html>
    <body>
        <p>
            <a href = "../../index.php">Tilbake til forsiden </a>
            <br>
        <p>
            <b>Medlemmene er som følger:</b>

            <table border=1>
                <tr>
                <?php foreach ($medlemmer[0] as $navn => $verdi){echo "<td><b>" . $navn . "</b></td>";}?>

                <?php foreach($medlemmer as $medlem):?>
                    <tr><?php foreach ($medlem as $navn => $verdi){
                        //Legger inn rad

                        if ($navn == "kjonn"){   //Endrer fra boolsk verdi
                            switch ($verdi){
                                case 0: $val = "Kvinne";         break;
                                case 1: $val = "Mann";           break;
                            }
                        }
                        elseif ($navn == "kontigentstatus"){
                            switch ($verdi){
                                case 0: $val = "Ikke betalt";    break;
                                case 1: $val = "Betalt";         break;
                            }
                        }
                        else{
                            $val = $verdi;                 //Legger verdi i $val
                        }
                        
                        echo "<td>" . $val . "</td>";}      //Utskrift i rute
                        ?>

                <?php   endforeach; ?>
            
                </tr>
            
            </table> 
        </p>
    </body>
</html>