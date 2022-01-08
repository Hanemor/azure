<?php
//Klasse for aktiviteter.
//Metoder tar array med verdier som parameter

require '../lib/medlem.class.php';

class aktivitet{
    private $navn;
    private $ansvarlig_id;
    private $dato;

    private function setVerdier($arr){                  //Lager objekt fra array

        foreach($arr as $k => $v){
            switch($k){
                case 'aktivitet':   $this->navn = $v;           break;
                case 'leder':       $this->ansvarlig_id = $v;   break;        
                case 'dato':        $this->dato = $v;           break;
            }
        }
    }

    public static function sjekkOmGyldig($arr){         //Henter array med evt feilmeldinger
    
        $messages = array();    //Lagrer feilmeldinger i array

        if (empty($arr['aktivitet'])){   
            $messages[] = "Du må fylle inn navn på aktivitet";                    
        }

        if (empty($arr['leder'])){   
            $messages[] = "Du må velge en leder";                
        }

        if (empty($arr['dato'])){   
            $messages[] = "Du må fylle inn datoen til aktiviteten";  
        }
    
        return $messages;       //Returnerer feilmeldinger
    }
    
    public static function lagAktivitet($arr){          //Lager objekt
        
        $obj = new aktivitet();                         
        $obj->setVerdier($arr);                         //Setter inn verdier fra array
        return $obj;                                    //Returnerer objekt
    }

    public function sendTilDB(){                        //Sender verdier i obj til DB

        $con = dbConnect();                             //Lager mysqli

        //Prepared statement
        $query = $con->prepare('INSERT INTO aktiviteter (aktiviteter.navn, 
        aktiviteter.ansvarlig_id, aktiviteter.dato)
        VALUES(?,?,?)');

        //Setter inn parametere
        $query->bind_param('sss', $this->navn, 
        $this->ansvarlig_id, $this->dato);

        //Kjører spørring
        $query->execute();
    }
}

?>