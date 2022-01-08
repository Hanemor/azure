<?php
//Klasse for medlemmer.
//Metoder tar array med verdier som parameter

require __DIR__ . '/../inc/mysqli.inc.php';

class medlem{
    private $id;
    private $fornavn;
    private $etternavn;
    private $adresse;

    private $postnummer;
    private $tlf;
    private $mail;
    private $fodselsdato;
    private $kjonn;

    private $roller      = array();
    private $interesser  = array();
    private $aktiviteter = array();
    private $dato;
    private $kontigentstatus;




    private function setVerdier($arr){             //Setter inn verdier fra array

        foreach($arr as $k => $v){                 //Går igjennom alle verdier

            switch($k){
                case 'id':              $this->id = $v;                 break;
                case 'fornavn':         $this->fornavn = $v;            break;
                case 'etternavn':       $this->etternavn = $v;          break;
                case 'adresse':         $this->adresse = $v;            break;        
                case 'postnummer':      $this->postnummer = $v;         break;
                case 'tlf':             $this->tlf = $v;                break;
                case 'mail':            $this->mail = $v;               break;
                case 'fodselsdato':     $this->fodselsdato = $v;        break;
                case 'kjonn':           $this->kjonn = $v;              break;
                case 'roller':          $this->roller = $v;             break;
                case 'interesser':      $this->interesser = $v;         break;
                case 'aktiviteter':     $this->aktiviteter = $v;        break;
                case 'fodselsdato':     $this->fodselsdato = $v;        break;
                case 'dato':            $this->dato = $v;               break;
                case 'medlemSidenDato': $this->dato = $v;               break;
                case 'kontigentstatus': $this->kontigentstatus = $v;    break;
            }
        }
    }    
    
    public static function sjekkOmGyldig($arr){   //Henter array med evt feilmeldinger
    
        $messages = array();                           //Lagrer feilmeldinger i array

        if (empty($arr['fornavn'])){                   //Tom verdi
            $messages[] = "Du må fylle inn fornavn";            
        }
        if (empty($arr['etternavn'])){   
            $messages[] = "Du må fylle inn etternavn";          
        }

        if (empty($arr['adresse'])){   
            $messages[] = "Du må fylle inn adresse";            
        }
        if (empty($arr['postnummer'])){   
            $messages[] = "Du må fylle inn postnummer";         
        }
        elseif((1000 > $arr['postnummer']) ||          //Ugyldig verdi
            ( $arr['postnummer'] > 9999 )){
                $messages[] = "Ugyldig postnummer";          
        }

        if (empty($arr['tlf'])){   
            $messages[] = "Du må fylle inn tlf";                
        }
        elseif((10000000 > $arr['tlf']) || 
            ( $arr['tlf'] > 99999999 )){
                $messages[] = "Ugyldig tlf";           
            }
        if (empty($arr['mail'])){   
            $messages[] = "Du må fylle inn mail";               
        }

        if (empty($arr['fodselsdato'])){   
            $messages[] = "Du må fylle inn fødselsdato";        
        }
        if (!isset($arr['kjonn'])){   
            $messages[] = "Du må fylle inn kjønn";              
        }
        if (empty($arr['interesser'])){   
            $messages[] = "Du må fylle inn minst en interesse";  
        }
        if (empty($arr['roller'])){   
            $messages[] = "Du må fylle inn minst en rolle";     
        }
        if (empty($arr['dato'])){   
            $messages[] = "Du må fylle inn 'medlem-siden' dato";  
        }
        if (!isset($arr['kontigentstatus'])){  
            $messages[] = "Du må fylle inn kontigentstatus";    
        }
    
        return $messages;          //Returnerer array
    }

    public function getArr(){                     //Henter Array med verdier fra objekt

        $arr = array(
            'id'                => $this->id,
            'fornavn'           => $this->fornavn,
            'etternavn'         => $this->etternavn,
            'adresse'           => $this->adresse,
            'postnummer'        => $this->postnummer,
            'tlf'               => $this->tlf,
            'mail'              => $this->mail,
            'fodselsdato'       => $this->fodselsdato,
            'kjonn'             => $this->kjonn,
            'roller'            => $this->roller,
            'interesser'        => $this->interesser,
            'aktiviteter'       => $this->aktiviteter,
            'dato'              => $this->dato,
            'kontigentstatus'   => $this->kontigentstatus
        );

        return $arr;                             //Returnerer array
    }

    public static function lagMedlem($arr){      //Returnerer obj

        $obj = new medlem();                     //Lager objekt
        $obj->setVerdier($arr);                  //Setter inn verdier
        return $obj;
    }

    public static function medlemFraDB($mail){   //Returnerer objekt med verdier fra DB

        //Henter verdier fra db

        //Medlemmer
        $m_query = "SELECT * FROM medlemmer WHERE
            medlemmer.mail='" . $mail . "'";

        //Roller
        $r_query = "SELECT roller.id, roller.navn
            FROM medlemmer
            JOIN rolleregister on medlemmer.id = rolleregister.mid
            JOIN roller on rolleregister.rid = roller.id
            WHERE medlemmer.mail ='" . $mail . "'";

        //Aktiviteter
        $a_query = "SELECT aktiviteter.id, aktiviteter.navn
            FROM medlemmer
            JOIN aktivitetspåmelding on medlemmer.id = aktivitetspåmelding.mid
            JOIN aktiviteter on aktivitetspåmelding.aid = aktiviteter.id
            WHERE medlemmer.mail ='" . $mail . "'";

        //Interesser
        $i_query = "SELECT interesser.id, interesser.navn
            FROM medlemmer
            JOIN interesseregister on medlemmer.id = interesseregister.mid
            JOIN interesser on interesseregister.iid = interesser.id
            WHERE medlemmer.mail ='" . $mail . "'";


        
        $con = dbConnect();                                 //Oppretter mysqli

        //Medlem
        $result = mysqli_query($con, $m_query);             //Henter medlem
        if(is_object($result)) {    
            $m = mysqli_fetch_all($result, MYSQLI_ASSOC);   //Arr
            $medlemArr = $m[0];                             //Rad
            mysqli_free_result($result);                    //Frigir minne
        }
        
        //Roller
        $result = mysqli_query($con, $r_query);               
        if(is_object($result)) {      
            $r = mysqli_fetch_all($result, MYSQLI_ASSOC);
            foreach($r as $verdi){
                $medlemArr["roller"][] = $verdi['navn'];
            }
            mysqli_free_result($result);
        }
        
        //Aktiviteter
        $result = mysqli_query($con, $a_query);                   
        if(is_object($result)) {  
            $a = mysqli_fetch_all($result, MYSQLI_ASSOC);
            foreach($a as $verdi){
                $medlemArr["aktiviteter"][] = $verdi['navn'];
            }  
            mysqli_free_result($result);
        } 
        
        //Interesser
        $result = mysqli_query($con, $i_query);   
        if(is_object($result)) {       
            $i = mysqli_fetch_all($result, MYSQLI_ASSOC);
            foreach($i as $verdi){
                $medlemArr["interesser"][] = $verdi['navn'];
            }
            mysqli_free_result($result);
        }
        
        $con->close();                              //Lukker mysqli
        $medlem = medlem::lagMedlem($medlemArr);    //Lager objekt
        return $medlem;                             //Returnerer objekt
    }

    public function sendTilDB(){                //Laster objekt opp på DB

        $con = dbConnect();                     //mysqli

        //Prepared statement - Insert medlem
        $m_query = $con->prepare('INSERT INTO medlemmer(medlemmer.fornavn, 
        medlemmer.etternavn, medlemmer.adresse, medlemmer.postnummer, 
        medlemmer.tlf, medlemmer.mail, medlemmer.fodselsdato, 
        medlemmer.kjonn, medlemmer.medlemSidenDato, medlemmer.kontigentstatus)
        VALUES(?,?,?,?,?,?,?,?,?,?)');

        //Setter inn verdier fra obj
        $m_query->bind_param('sssiissisi', $this->fornavn, $this->etternavn, 
        $this->adresse, $this->postnummer, $this->tlf, $this->mail, 
        $this->fodselsdato, $this->kjonn, $this->dato, $this->kontigentstatus);

        $m_query->execute();    //Utfører query
        $m_query->close();      //Lukker query

        //Query - Henter id
        $i_query = "SELECT id FROM medlemmer WHERE     
        mail = '" . $this->mail . "';";
        
        //Utfører spørring
        $result = mysqli_query($con, $i_query);         
        $i = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);                 
        $this->id = $i[0]["id"];                        //Legger til ID i obj


        //Legger inn interesser
        foreach ($this->interesser as $interesse){      
            $sqlInsertInteresse = $con->prepare
                ('INSERT INTO interesseregister (mid, iid)
                VALUES (?, ?)'
            );
            $sqlInsertInteresse->bind_param( "ii", $this->id, $interesse);
            $sqlInsertInteresse->execute();
            $sqlInsertInteresse->close();
        }

        //Legger inn roller
        foreach ($this->roller as $rolle){
            $sqlInsertRolle = $con->prepare
                ('INSERT INTO rolleregister (mid, rid)
                VALUES (?, ?)'
            );
            $sqlInsertRolle->bind_param("ii", $this->id, $rolle,);
            $sqlInsertRolle->execute();
            $sqlInsertRolle->close();
        }
        
        //Legger inn aktiviteter
        foreach ($this->aktiviteter as $aktivitet){
            $sqlInsertAktivitet = $con->prepare
                ('INSERT INTO aktivitetspåmelding (mid, aid)
                VALUES (?, ?)'
            );
            $sqlInsertAktivitet->bind_param( "ii", $this->id, $aktivitet);
            $sqlInsertAktivitet->execute();
            $sqlInsertAktivitet->close();
        }
        $con->close();                          //Lukker mysqli
        
    }

    public function verdiTilID(){               //Endrer navn på elementer til deres id i DB

        //Interesser
        foreach ($this->interesser as $index => $interesse){
            switch($interesse){
                case "Fotball": 
                    unset($this->interesser[$index]); 
                    $this->interesser[] = 1; break;
                case "Dart":
                    unset($this->interesser[$index]); 
                    $this->interesser[] = 2; break;
                case "Biljard":
                    unset($this->interesser[$index]); 
                    $this->interesser[] = 3; break;
                case "Dans":    
                    unset($this->interesser[$index]); 
                    $this->interesser[] = 4; break;
            }
        }sort($this->interesser);       //Sorterer array


        //Roller
        foreach ($this->roller as $index => $rolle){
            switch($rolle){
                case "admin": 
                    unset($this->roller[$index]); 
                    $this->roller[] = 1; break;
                case "leder":
                    unset($this->roller[$index]); 
                    $this->roller[] = 2; break;
                case "medlem":
                    unset($this->roller[$index]); 
                    $this->roller[] = 3; break;
            }
        }sort($this->roller);


        //Aktiviteter
        $con = dbConnect();
        $query = "SELECT id, navn FROM aktiviteter";
        $result = mysqli_query($con, $query);           
        $rader = mysqli_fetch_all($result, MYSQLI_ASSOC); //Henter rader fra DB          
        mysqli_free_result($result);                      //Frigir minne
        mysqli_close($con);  

        foreach ($rader as $rad){
            if (in_array($rad['navn'], $this->aktiviteter)){
                unset($this->aktiviteter[array_search($rad['navn'], 
                    $this->aktiviteter)]);
                $this->aktiviteter[] = $rad['id'];
            }
        }sort($this->aktiviteter);

    }

    public function endreDB($endringer){        //Endrer DB til å samsvare med objekt

        $con = dbConnect();     //Mysqli

        //Prepared statement - Endre medlem
        $query = $con->prepare('UPDATE medlemmer
        SET fornavn = ?, etternavn = ?, adresse = ?, 
        postnummer = ?, tlf = ?, mail = ?, fodselsdato = ?, 
        kjonn = ?, medlemSidenDato = ?, kontigentstatus = ?
        WHERE id = ?');

        //Setter inn verdier
        $query->bind_param('sssiissisii', $this->fornavn, 
        $this->etternavn, $this->adresse, $this->postnummer, 
        $this->tlf, $this->mail, $this->fodselsdato,
        $this->kjonn, $this->dato, $this->kontigentstatus,
        $this->id);
        $query->execute();
        $query->close();


        //Sletter eksisterende atributter

        if (in_array('roller', $endringer)){        //Sletter roller
            $delete = $con->prepare('DELETE 
                FROM rolleregister  
                WHERE mid=' . $this->id
            );
            $delete->execute();
            $delete->close();
        }
            
        if (in_array('interesser', $endringer)){    //Sletter interesser
            $delete = $con->prepare('DELETE 
                FROM interesseregister  
                WHERE mid=' . $this->id
            );
            $delete->execute();
            $delete->close();
        }

        if (in_array('aktiviteter', $endringer)){   //Sletter aktiviteter
            $delete = $con->prepare('DELETE 
                FROM aktivitetspåmelding  
                WHERE mid=' . $this->id
            );
            $delete->execute();
            $delete->close();
        }       


        //Legger inn nye atributter

        if (in_array('roller', $endringer)){        //Setter inn roller

            foreach ($this->roller as $rolle){
                $query = "INSERT INTO rolleregister (mid, rid)
                VALUES (?, ?);";

                $stmt = $con->prepare($query);
                $stmt->bind_param('ii', $this->id, $rolle);
                $stmt->execute();
                $stmt->close();
            }                
        }
        
        if (in_array('interesser', $endringer)){    //Setter inn interesser

            foreach ($this->interesser as $interesse){
                $query = "INSERT INTO interesseregister (mid, iid)
                VALUES (?, ?);";

                $stmt = $con->prepare($query);
                $stmt->bind_param('ii', $this->id, $interesse);
                $stmt->execute();
                $stmt->close();
            }                
        }
        

        if (in_array('aktiviteter', $endringer)){   //Setter inn aktiviteter

            foreach ($this->aktiviteter as $aktivitet){
                $query = "INSERT INTO aktivitetspåmelding (mid, aid)
                VALUES (?, ?);";

                $stmt = $con->prepare($query);
                $stmt->bind_param('ii', $this->id, $aktivitet);
                $stmt->execute();
                $stmt->close();
            }                
        }
        $con->close();
    }
}



?>