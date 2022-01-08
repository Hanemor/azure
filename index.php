<?php
require 'site/lib/medlem.class.php';

session_start();

//Sender brukeren til login-siden  om ikke innlogget
if(!isset($_SESSION['bruker']['innlogget']) ||
    $_SESSION['bruker']['innlogget'] !== true) {
    header("Location: site/funk/login.funk.php");
    exit();
}

//Henter array fra session
 $obj = unserialize($_SESSION['bruker']['medlem']);
 $arr = $obj->getArr();     
?>




<html>
    <header>
        <h2>Velkommen <?php echo $arr['fornavn']?>!</h2>
    </header>

    <body>
        <p>
            <a href = "site/funk/visProfil.funk.php">
                Min profil            </a><br><br>
            <a href = "site/funk/hentAlle.funk.php">
                Vis alle medlemmer     </a><br>
            <a href = "site/funk/hentMedFilter.funk.php">
                Filtrer medlemmer </a><br><br>   
             
            
            <?php       //Lenker til beskyttede sider

                if(in_array("admin", $arr['roller'])){
                    echo '<a href = "site/funk/nyttMedlem.funk.php">
                            Nytt medlem </a><br> 
                          <a href = "site/funk/velgEndring.funk.php">
                            Endre Medlem </a><br><br>
                          <a href = "site/funk/aktivitetsPåmelding.funk.php">
                            Vis aktiviteter og påmeldte</a><br>
                          <a href = "site/funk/nyAktivitet.funk.php">
                            Legg til aktiviteter </a><br>';
                }    
                if((in_array("admin", $arr['roller'])) || (in_array("leder", $arr['roller']))){
                    echo '<br><a href = "site/funk/mailVelgMange.funk.php">
                            Send Mail til en gruppe</a><br>';
                    echo '<a href = "site/funk/mailVelgEn.funk.php">
                            Send Mail til ett medlem</a><br>';
                }
            ?>
        <p>
            <a href = "site/funk/loggUt.funk.php">Logg Ut </a><br><br>
        </p>
    </body>

</html>