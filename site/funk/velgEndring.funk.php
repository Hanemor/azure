<?php
require '../lib/medlem.class.php';


session_start();

//Sender brukeren til login-siden om ikke innlogget
if(!isset($_SESSION['bruker']['innlogget']) ||         
    ($_SESSION['bruker']['innlogget'] !== true)) {
    header("Location: ./login.funk.php");
    exit();
}
$brukerObj = unserialize($_SESSION['bruker']['medlem']);
    $brukerArr = $brukerObj->getArr();

//Sjekker om admin
if (!in_array('admin', $brukerArr['roller'])){     
    header("Location: ../../index.php");
    exit();
}

if (isset($_POST['contact-send']) && isset($_POST['medlem'])){

    //Sender valgt medlem som cookie
    setcookie('mail', "", time() - 21600);
    setcookie('mail', $_POST['medlem'], time() + 21600);
    
    header("Location: endreMedlem.funk.php");
    exit();
}

?>


<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Velg medlem</title>
    </head>
    <body>

    <p>
        <a href = "../../index.php">Tilbake til forsiden </a><br>
        <h2>Endre medlem</h2><br>
    </p>

    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">

        <p>                             <!––Henter fra/sender med $_POST -->

            <label for="medlem">Velg personen du ønsker å endre</label><br>
            <select name="medlem" > 

            <option value='' disabled selected>Medlemmer</option>
                
                <?php //Henter alternativer fra DB
                $con = dbConnect();
                $query = "SELECT fornavn, etternavn, mail FROM medlemmer";

                $result = mysqli_query($con, $query);           
                $rader = mysqli_fetch_all($result, MYSQLI_ASSOC);
                mysqli_free_result($result);                                 //frigir minne
                mysqli_close($con);  
                
                foreach($rader as $index => $medlem){
                    echo '<option value=' . $medlem['mail'] . ' >' .
                     $medlem['fornavn'] . ' ' . $medlem['etternavn'] . '</option>';
                }
                ?>

            </select>
    
    <p>               <!––"send" knapp -->
        <button type="submit" name="contact-send">Send</button>                       
    </p>
</form>
</body> 
</html>