<?php
require '../lib/medlem.class.php';

require '../inc/phpmailer/PHPMailer.php';        //Legger til phpmailer filer
require '../inc/phpmailer/SMTP.php';
require '../inc/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;               //Definer namespace
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();

if(!isset($_SESSION['bruker']['innlogget']) ||     //Sjekker om innlogget
    ($_SESSION['bruker']['innlogget'] !== true)) {
    header("Location: ./login.funk.php");
    exit();
}
$brukerObj = unserialize($_SESSION['bruker']['medlem']);
    $brukerArr = $brukerObj->getArr();

if ((!in_array('admin', $brukerArr['roller'])) &&
    (!in_array('leder', $brukerArr['roller']))){     //Sjekker roller
    header("Location: ../../index.php");
    exit();
}

if (!isset($_COOKIE['mottakere'])){                 //Sjekker om cookie fins
    header("Location: ../../index.php");
    exit();
}


$cookie = $_COOKIE['mottakere'];            //Henter array med mail fra cookie
$cookie = stripslashes($cookie);
$mottakere = json_decode($cookie, TRUE);   

$avsender = "phpgruppe25@gmail.com"; 

if (isset($_POST['contact-send'])){

    $mail = new PHPMailer();                    //Lager instans av phpmailer
    $mail->isSMTP();                            //Sett mailer som smtp
    $mail->Host = "smtp.gmail.com";             //Definer smtp host
    $mail -> SMTPAuth = "true";                 //SMTP autotentikasjon
    $mail->SMTPSecure = "tls";                  //Sett type kryptering
    $mail->Port = "587";                        //Sett port 
    $mail->Username = "phpgruppe25@gmail.com";  //Avsender - Mail
    $mail->Password = "123qwerty!";             //Avsender - Passord

    $mail->Subject = $_POST['emne'];            //Sett emne
    $mail->SetFrom("phpgruppe25@gmail.com");    //Avsenderadresse
    $mail->Body = $_POST['melding'];            //Innhold body
    foreach ($mottakere as $mottaker){
        $mail->addAddress($mottaker);           //Sett mottakere
    }

    if ( $mail->Send() ) {                      //Sender mail
        echo "Email sendt";
    }else {
        echo "Email feilet. Venligst prøv igjen";
    }                             
    $mail->smtpClose();                         //Stopp smtp
}
?>

<html>
    <header>
        <a href = "../../index.php">Tilbake til forsiden </a>

        <h2>Send Mail</h2>
        <b>Mottaker(e):</b><br>
        <?php foreach($mottakere as $mottaker){
            echo $mottaker . "<br>";}
        ?>
    </header>
    <body>
        <p>
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">

            <label for="emne">Emne</label><br>
            <textarea name="emne" rows="1" cols="50">                
            </textarea> 

        <p>
            <label for="melding">Melding</label><br>
            <textarea name="melding" rows="5" cols="50">                
            </textarea> 
        <p>              
            <button type="submit" name="contact-send">Send</button> 
        </p>
    </body>
</html>