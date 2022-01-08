<?php
require '../lib/medlem.class.php';

if(isset($_REQUEST['loggin'])){

    //Spørring - Henter passord
    $query = "SELECT passordliste.passord, medlemmer.mail
    FROM medlemmer
    JOIN passordliste ON passordliste.mid = medlemmer.id
    WHERE medlemmer.mail='" . $_REQUEST["mail"] . "'";


    $con = dbConnect();
    $result = mysqli_query($con, $query);    
    
    if($result->num_rows === 0){               //Sjekker om tom
        $brukerFunnet = false;
    }
    else{$brukerFunnet = true;}                //Setter funnet som verdi

    

    if ($brukerFunnet){
        
        $r = mysqli_fetch_all($result, MYSQLI_ASSOC);   //Henter passord om funnet
        mysqli_free_result($result);

        if(password_verify($_REQUEST["passord"], $r[0]["passord"])){

            mysqli_close($con);
            $medlem = medlem::medlemFraDB($r[0]['mail']);
 
            session_start();                            //Oppretter session

            $_SESSION['bruker']['innlogget'] = true;    //Gir array verdier
            $_SESSION['bruker']['medlem'] = serialize($medlem); //Sender obj

            header("Location: ../../index.php");    //Sender til forsiden
            exit;

            
        }
        //Feilmeldinger dersom brukernavn/passord ikke samsvarer
        else{echo "Du har tastet inn feil brukernavn eller passord";}
    }   
    else{echo "Du har tastet inn feil brukernavn eller passord";}

    mysqli_close($con);
}

?>


<html>
<header>
    <h2>NEO ungdomsklubb</h2>
    <h3>Login</h3>
</header>
<body>
    <p>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <div class="container">
            <label for="mail"><b>Mailadresse</b></label>
            <input type="text" placeholder="Skriv inn mailadresse" name="mail" required>

                
            <label for="passord"><b>Passord</b></label>
            <input type="password" placeholder="Skriv inn Passord" name="passord" required>
                
            <input type="submit" name="loggin" value="Logg inn" />
        </div>     
    </form>
    <p>
    (Bruk "post@mail.com" med passordet "qwerty" for å logge inn som admin.<br>
    se README.md for mer informasjon om nettsiden
    <p>
    Laget av Prosjektgruppe 25 <br>
    (Daniel Castberg og Steffen Abrahamsen)   
    </p>
</body>
</html>