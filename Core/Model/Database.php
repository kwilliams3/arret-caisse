<?php

namespace Core\Model;

class Database{

    private $db_name;
    private $db_server;
    private $db_user;
    private $db_pass;
    private $con;
    private $SageConnect;

    public function __construct($DB_SERVER, $DB_USER, $DB_Name, $DB_Pass){
        $this->db_name = $DB_Name;
        $this->db_server = $DB_SERVER;
        $this->db_user = $DB_USER;
        $this->db_pass = $DB_Pass;
    }

     public function getCon(){
        if($this->con === null){
            $serverName = "";
            $connection1 = array("Database"=>"ArretsCaisses","UID"=>"sa", "PWD"=>"");
            $conn1 = sqlsrv_connect($serverName, $connection1);
            $this->con = $conn1 ;
			
        }
        return $this->con;
    }

    public function getConSage(){
        if($this->SageConnect === null){
            $server = "";
			
            $connect = array("Database"=>"basex3", "UID"=>"sa", "PWD"=>"");
            $conn = sqlsrv_connect($server, $connect);
            $this->SageConnect = $conn ;
			
			// var_dump('$conn');
			// var_dump($conn);
			// die();
        }
        return $this->SageConnect;
    }

    public function getConSageP($adresseIP,$baseDonnees){
		$session = Session::getInstance();
        $user = $_SESSION['user'];

        if($this->SageConnect === null){
            $server = $adresseIP;
			$base = explode(' ',$baseDonnees);

            $connect = array("Database"=>$base[0], "UID"=>"sa", "PWD"=>"dir*SIEX!1982");
            $conn = sqlsrv_connect($server, $connect);
            $this->SageConnect = $conn ;
        }
        return $this->SageConnect;
    }

    public function query($statement, $attributes = null, $sage = false, $serveur = null){
        if($attributes){
            if($sage === true && $serveur){
                if(strcmp('ERP',$serveur) == 0){
                    $stmt = sqlsrv_query($this->getConSage(), $statement, $attributes);
                    if( $stmt === false) {
						$stmt = 'Non Connection';
                        //die( print_r( sqlsrv_errors(), true) );
                    }
                }else{
                    $stmt = sqlsrv_query($this->getConSage(), $statement, $attributes);
                    if( $stmt === false) {
						$stmt = 'Non Connection';
                        //die( print_r( sqlsrv_errors(), true) );
                    }
                }
            }else{
                $stmt = sqlsrv_query($this->getCon(), $statement, $attributes);
                if( $stmt === false) {
                    die( print_r( sqlsrv_errors(), true) );
                }
            }
            return $stmt;
        }else{
            if($sage === true && $serveur){
                if(strcmp('ERP',$serveur) == 0){
					
					
					if( $this->getConSage() === false) {
						
						$stmt = 'Non Connection';
						
					}else{
						$stmt = sqlsrv_query($this->getConSage(), $statement, $attributes);
						if( $stmt === false) {
							$stmt = 'Non Connection';
							//die( print_r( sqlsrv_errors(), true) );
						}
					}
                }else{
					
					if( $this->getConSage() === false) {
						
						$stmt = 'Non Connection';
						
					}else{
						$stmt = sqlsrv_query($this->getConSage(), $statement, $attributes);
						if( $stmt === false) {
							$stmt = 'Non Connection';
							//die( print_r( sqlsrv_errors(), true) );
						}
					}
					
                }
            }else{
                $stmt = sqlsrv_query($this->getCon(), $statement, $attributes);
                if( $stmt === false) {
                    die( print_r( sqlsrv_errors(), true) );
                }
            }
            return $stmt;
        }
    }

    public function queryCount($statement,$sage = false, $adresseIP = null , $baseDonnees = null){
        $params = array();
        $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
        if($sage && $adresseIP && $baseDonnees){
            $stmtCount = sqlsrv_query($this->getConSage($adresseIP,$baseDonnees), $statement , $params, $options );
        }else{
            $stmtCount = sqlsrv_query($this->getCon(), $statement , $params, $options );
        }
        $nbre_total = sqlsrv_num_rows($stmtCount);
        return $nbre_total;
    }

    public function Prepare($statement,$attributes){
        $stmt = sqlsrv_prepare($this->getCon(), $statement, $attributes);
        if( $stmt === false) {
            die( print_r( sqlsrv_errors(), true) );
        }
        $stmt1 = sqlsrv_execute($stmt);

        if($stmt1 === false){
            die( print_r( sqlsrv_errors(), true) );
        }
        return $stmt1;

    }

    public function prepareQ($statement, $attributes){
        $stmt = sqlsrv_prepare($this->getCon(), $statement, $attributes);
        if( $stmt === false) {
            die( print_r( sqlsrv_errors(), true) );
        }
        $stmt1 = sqlsrv_execute($stmt);

        if($stmt1 === false){
            die( print_r( sqlsrv_errors(), true) );
        }

        $resultats = array();
        $pers = array();

        if($one){
            if($result = sqlsrv_fetch($stmt)) {
                $nom = sqlsrv_get_field( $stmt, 1);
                $age = sqlsrv_get_field( $stmt, 2);
                $date = sqlsrv_get_field( $stmt, 3);
                $pers = array("nom"=>$nom,
                    "age"=>$age,
                    "date"=>$date
                );
            }
            return $pers;
        }else{
            while ($result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
                $pers = array("nom"=>$result['nomUser'],
                    "age"=>$result['ageUser'],
                    "date"=>$result['dateCreation']
                );
                $resultats[] = $pers;
            }
            return $resultats;
        }

    }


}

?>
