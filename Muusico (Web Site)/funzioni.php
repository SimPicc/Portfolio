<?php

// stampa i prodotti di una categoria
function stampaProdotti($cid,$categoria,$email_U,$colore,$marca,$prezzo_min,$prezzo_max,$rating){
    $risultato= array("msg"=>"","status"=>"ok");
	$msg="";

	if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}

	//$sql = "SELECT * FROM prodotto WHERE categoria ='" . $categoria . "'";
    $sql = "SELECT  prodotto.codice, prodotto.nome, prodotto.colore, prodotto.prezzo, prodotto.quantitàM, prodotto.rating, prodotto.descrizione , utente.marca FROM prodotto INNER JOIN utente ON prodotto.email_V=utente.email WHERE utente.tipo='V' AND prodotto.categoria='$categoria'";

    $filtraggio = '';
    // se i parametri del filtro sono passati (cioè non vuoti), aggiungiamoli alla query SQL
    if(!empty($colore)){
        $sql.=" AND prodotto.colore='".$colore."'";
        $filtraggio.= "<b>Colore</b>: ".$colore."  ";
    }
    if(!empty($marca)){
        $sql.=" AND utente.marca = '".$marca."'";
        $filtraggio.= "<b>Marca</b>: ".$marca."  ";

    }
    if(!empty($prezzo_min)){
        $sql.=" AND prodotto.prezzo >= '".$prezzo_min."'";
        $filtraggio.= "<b>Prezzo minimo</b>: ".$prezzo_min."  ";
    }
    if(!empty($prezzo_max)){
        $sql.=" AND prodotto.prezzo <= '".$prezzo_max."'";
        $filtraggio.= "<b>Prezzo massimo</b>: ".$prezzo_max."  ";

    }
    if(!empty($rating)){
        $sql.=" AND prodotto.rating >= '".$rating."'";
        $filtraggio.= "<b>Rating</b>: ".$rating."+";

    }
    $sql.=";";
    $res = $cid->query($sql);

    if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }elseif($res->num_rows==0){
        $msg = "Non sono presenti prodotti secondo i filtri richiesti";
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else{
        
        echo '<div align="center" class="text-white"> ';
        if(empty($filtraggio)){
            $filtraggio = "Nessun filtro attivo";
            echo '<b>  '.$filtraggio.' </b>';
        }else{
            echo '<b> Filtri attivi - </b> '.$filtraggio.'';
        }
        
        echo '</div>';
        while($row=$res->fetch_assoc()){
            // Crea un'array associativa per il prodotto
            $prodotto[] = array(
                "codice" => $row["codice"], 
                "marca" => $row["marca"],
                "prezzo" => $row["prezzo"],
                "descrizione" => $row["descrizione"],
                "nome" => $row["nome"],
                "rating" => $row["rating"],
                "quantitàM"=>$row["quantitàM"]);
            $risultato["contenuto"]=$prodotto;
            // utilizziamo l'ultimo elemento dell'array $prodotto per accedere ai dati del prodotto corrente
            echo '<div class="card-group" id="listaProdotti">
            <div class="container mt-5">
            <div class="card mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <a href="prodotto.php?id=' . $prodotto[count($prodotto) - 1]['codice'] . '&cat='.$categoria.'">
                            <img src="images/' . $prodotto[count($prodotto) - 1]['codice'] . '.jpg" class="card-img" alt="">
                        </a>
                    </div>
                    <div class="col-md-8">
                        <div class="card-body d-flex flex-column" style="height: 100%;">
                            <div>
                                <h1 class="card-title"><a href="prodotto.php?id=' . $prodotto[count($prodotto) - 1]['codice'] . '&cat='.$categoria.'"><b>' . $prodotto[count($prodotto) - 1]['nome'] . '</b></a></h1>
                                <h4 class="card-text">Marca: ' . $prodotto[count($prodotto) - 1]['marca'] . '</h4>
                                <h4 class="card-text">Rating: ' . $prodotto[count($prodotto) - 1]['rating'] . '</h4>
                                <h5 class="card-text">' . $prodotto[count($prodotto) - 1]['descrizione'] . '</h5>
                                <h2 class="card-text"><b>' . $prodotto[count($prodotto) - 1]['prezzo'] . '€</b></h2>
                            </div>';
                            if ($email_U){
                                $res2=controlloUtente($cid, $email_U);
                                if ($res2["contenuto"]==0){ //compratore autorizzato e non bloccato
                                    echo '<div class="mt-auto">';
                                        if($prodotto[count($prodotto) - 1]['quantitàM']>0){
                                        echo '<a href="backend/modifyQ.php?codice_P=' . $prodotto[count($prodotto) - 1]['codice'] . '&cat='.$categoria.'&mod=add"><button type="button" class="btn btn-danger">Aggiungi al Carrello</button></a>';
                                        }else{
                                        echo '<h5 style="display:inline-block; width:200px; color:red;"><b>Prodotto Esaurito!</b></h5>';
                                        }
                                    echo '</div>';
                                }else if ($res2["contenuto"]==1){ //compratore bloccato
                                    echo '<div class="mt-auto">
                                        <h5>Utente Bloccato - Funzionalità non disponibile</h5>
                                        <button type="button" class="btn btn-danger">Aggiungi Al Carrello</button>
                                    </div>';
                                }else if ($res2["contenuto"]==4){ //compratore non anocra autorizzato
                                    echo '<div class="mt-auto">
                                            <h5>Utente Non Autorizzato - Funzionalità non disponibile</h5>
                                            <button type="button" class="btn btn-danger">Aggiungi Al Carrello</button>
                                    </div>';
                                }else if($res2["contenuto"]==6){
                                    echo '<a href="backend/rimuovi_P.php?codice=' . $prodotto[count($prodotto) - 1]['codice'] . '" onclick="return confirm(\'Confermi di voler eliminare il prodotto?\')"><button type="button" class="btn btn-danger">Elimina Prodotto</button></a>';
                                }
                            }else{
                            echo '<div class="mt-auto">
                                <h5><b>Fai il Log-In per poter Acquistare</b></h5>
                            </div>';
                            }
                        echo '</div>
                    </div>
                </div>
                </div>
            </div>
            </div>';
        }
    }
    return $risultato;
}

// func che stampa le recensioni per un prodotto, chiamato in stampaProdotto()
function stampaRecensioni($cid,$codice_P){
    $risultato= array("msg"=>"","status"=>"ok");
	$msg="";
    //join tra recensione e utente per prendere le informazioni delle recensioni
    $sql = "SELECT recensione.email_U, recensione.punteggio,recensione.testo,utente.nome,utente.cognome FROM recensione INNER JOIN utente ON recensione.email_U=utente.email WHERE codice_P='" . $codice_P . "';";
    $res = $cid->query($sql);
    if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else if($res->num_rows==0){
        $msg = "Non ci sono recensioni per questo prodotto";
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else{
        while($row=$res->fetch_assoc()){
        $recensione[] = array(
            "testo" => $row["testo"],
            "punteggio" => $row["punteggio"],
            "email_U" => $row["email_U"],
            "nome"=>$row["nome"],
            "cognome"=>$row["cognome"]);
        $risultato["contenuto"]=$recensione;

        echo '<div class="container mt-5">
        <div class="card mb-3">
            <div class="row">
                <div class="col-md-8">
                    <div class="card-body d-flex flex-column" style="height: 100%;">
                        <div>
                            <h2 class="card-text"><b>' . $recensione[count($recensione) - 1]['nome'] .' '. $recensione[count($recensione) - 1]['cognome']. '</b> Rate: '.$recensione[count($recensione) - 1]['punteggio'].'</h2>
                            <h3 class="card-text">' . $recensione[count($recensione) - 1]['testo'] . '</h>
                        </div>';
                        if (isset($_SESSION["email"])){
                            $res2=controlloUtente($cid,$_SESSION["email"]);
                            // gli admin avranno la possibilità di eliminare recensioni
                            if ($res2["contenuto"]==6){
                                echo '<span><a href="backend/elimina_R.php?cat='.$_GET["cat"].'&email_U='.$recensione[count($recensione) - 1]['email_U'].'&codice_P='.$codice_P.'" onclick="return confirm(\'Confermi di voler eliminare questa recensione?\')">
                                <button class="btn btn-danger">Elimina Recensione</button></a></span>';
                            }else if($res2["contenuto"]==0 || $res2["contenuto"]==1){
                                // controlliamo se il compratore è il proprietario della recensione
                                //list($risultato,$esiste)=checkRecensione($cid,$_SESSION["email"],$codice_P);
                                if($_SESSION["email"] == $recensione[count($recensione) - 1]["email_U"]){
                                    echo '<span><a href="backend/elimina_R.php?cat='.$_GET["cat"].'&email_U='.$recensione[count($recensione) - 1]['email_U'].'&codice_P='.$codice_P.'" onclick="return confirm(\'Confermi di voler eliminare questa recensione?\')">
                                <button class="btn btn-danger">Elimina Recensione</button></a></span>';
                                }
                            }
                        }
                        echo '</div>
                    </div>
                 </div>
            </div>
        </div>';
        }
    }
return $risultato;
}

// func per i compratori, che controlla l'esistenza di un carrello e ritorna il codice del carrello
// chiamato in carrello.php e funzioni in backend prima di modificare/eliminare un carrello
function checkCarrello($cid,$email_U){
    $risultato= array("msg"=>"","status"=>"ok");
	$msg="";
    // controllo accesso DB
    if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}
    // per prendere il carrello con stato=0 dell'utente corrente
    $sql = "SELECT * FROM carrello WHERE email_U='" . $email_U . "' AND stato=0;";
    $res = $cid->query($sql);
    if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else if($res->num_rows==0){
        // in caso di carello assente perchè utente nuovo --> viene creato un nuovo carrello
        $sql2 = "INSERT INTO carrello(email_U) VALUES ('$email_U');";
        $res2=$cid->query($sql2);
        if ($res2==null) {
            $msg2 = "La creazione del nuovo carrello è fallita<br/>";
            $risultato["status"]="ko";
            $risultato["msg"].=$msg2;
            return array(NULL,$risultato);
        }
        // ricorsione fatta per l'utente con carrello appena creato, cosi poi in out finale della func darà il codice_cart appena creato
        list($codice_cart,$risultato)=checkCarrello($cid,$email_U);
        return array($codice_cart,$risultato);
    }

    $row = $res->fetch_assoc();
    $codice_cart = $row["codice"];
    return array($codice_cart,$risultato);
}

// func per i compratori, chiamato in carrello.php per stampare i prodotti contenuti in un carrello
function stampaCarrello($cid,$email_U,$codice_cart,$stato_U){
    $checkAdd=checkAddress($cid,$email_U);//func per controllare se utente ha inserito dati di Indirizzo per la consegna
    $risultato= array("msg"=>"","status"=>"ok");
	$msg="";
    $prezzoCart_tot=0;
    $quantitàCart_tot=0;
    if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}
    
    $sql = "SELECT * FROM contiene INNER JOIN prodotto ON contiene.codice_P=prodotto.codice WHERE contiene.codice_C='$codice_cart';";
    $res = $cid->query($sql);
    if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else if($res->num_rows==0){
        $msg = "Nessun prodotto presente nel carrello";
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else{
        while($row=$res->fetch_assoc()){
        echo '<div class="row">
            <div class="col-lg-2">
            </div>
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4">
                                '.$row["nome"].' ('.$row["prezzo"].'€)
                            </div>
                            <div class="col-sm-2">';
                            //se quantità nel carrelllo è > di quantità mnel magazzino di quel prodotto viene modificata la quantità nel carrello con la quantità del prodotto massima possibile
                            if ($row["quantità_C"]>$row["quantitàM"]){ 
                                //se quantitàM è 0 
                                if($row["quantitàM"]==0){
                                echo 'Quantità: '.$row["quantitàM"].'<br>
                                <h5 style="color:red">Prodotto non disponibile, rimuoverlo dal Carrello per poter porcedere con l\'acquisto</h5>';
                                }else{//se invece quantità_C > quantità_M allora la quantità_C viene settata al massimo possibile e si indica Quantità Massima
                                    $quantitàM=$row["quantitàM"];
                                    $codice_P=$row["codice"];
                                    $sql2="UPDATE contiene SET quantità_C='$quantitàM'WHERE codice_C='$codice_cart' AND codice_P='$codice_P';";
                                    $res2 = $cid->query($sql2);
                                    if ($res2==null) {
                                        $msg = "Si sono verificati i seguenti errori:<br/>" . $res2->error;
                                        $risultato["status"]="ko";
                                        $risultato["msg"].=$msg;
                                    }
                                    echo 'Quantità: '.$row["quantitàM"].'<br>
                                    <button type="button" class="btn" '.$codice_cart.'">+</button>';
                                    if($stato_U["contenuto"]==0){//controllo se utente è bloccato
                                    echo '<button type="button" class="btn" onclick="subQ(\''.addslashes($row["codice"]).'\','.$codice_cart.')">-</button><br>';
                                    }else{
                                    echo '<button type="button" class="btn" '.$codice_cart.'">-</button>';
                                    }
                                    echo '<h5 style="color:red"><b>Quantità Massima</b></h5>';
                                }
                            }else if($row["quantità_C"]==$row["quantitàM"]){
                                echo 'Quantità: '.$row["quantitàM"].'<br>
                                <button type="button" class="btn" '.$codice_cart.'">+</button>';
                                if($stato_U["contenuto"]==0){//controllo se utente è bloccato
                                echo '<button type="button" class="btn" onclick="subQ(\''.addslashes($row["codice"]).'\','.$codice_cart.')">-</button><br>';
                                }else{
                                    echo '<button type="button" class="btn" '.$codice_cart.'">-</button>';
                                }
                                echo '<h5 style="color:red"><b>Quantità Massima</b></h5>';
                            
                            }else{
                            echo 'Quantità: '.$row["quantità_C"].'<br>';
                                if($stato_U["contenuto"]==0){//controllo se utente è bloccato
                                    echo '<button type="button" class="btn" onclick="addQ(\''.addslashes($row["codice"]).'\','.$codice_cart.')">+</button>
                                    <button type="button" class="btn" onclick="subQ(\''.addslashes($row["codice"]).'\','.$codice_cart.')">-</button>';
                                }else{
                                    echo '<button type="button" class="btn" '.$codice_cart.'">+</button>
                                        <button type="button" class="btn" '.$codice_cart.'">-</button>';
                                }
                            }
                            echo '</div>
                            <div class="col-sm-3">
                                Prezzo Totale: '.$row["quantità_C"]*$row["prezzo"].'€
                            </div>
                            <div class="col-sm-1">';
                            if($stato_U["contenuto"]==0){//controllo se utente è bloccato
                                echo '<a href="backend/rimuovi_Carr.php?codice_P=' . $row["codice"] . '&codice_C='.$codice_cart.'" ><button type="button" class="btn btn-danger">Rimuovi</button></a>';
                            }else{
                                echo '<a href="#" ><button type="button" class="btn btn-danger">Rimuovi</button></a>';
                            }
                            echo '</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
        $prezzoCart_tot+=$row["prezzo"]*$row["quantità_C"];
        $quantitàCart_tot+=$row["quantità_C"];
        }

        echo '<div class="row">
        <div class="col-lg-2">
        </div>
        <div class="col-lg-2">
            <h3 style="color:white;"><b>Prezzo Totale: '.$prezzoCart_tot.'€</b></h3>
        </div>
        <div class="col-lg-2">
            <h3 style="color:white;"><b>Numero Pezzi: '.$quantitàCart_tot.'</b></h3>
        </div>
        <div class="col-lg-2">';
        if($checkAdd["status"]=="ko"){
            echo '<a href="#" onclick="return alert(\'Aggiornare Via,Città,Provincia,Cap,Nazione e Codice Fiscale nei Dati Utente per proseguire\')"><button type="button" class="btn btn-success">Acquista Carrello</button></a>';
        }else if($stato_U["contenuto"]==1){//se l'utente bloccato
            echo '<a href="#"><button type="button" class="btn btn-success">Acquista Carrello</button></a>';
        }else {
            echo '<a href="backend/confermaOrdine.php?codice_C='.$codice_cart.'&prezzoTot='.$prezzoCart_tot.'" onclick="return confirm(\'Confermi di voler acquistare questo carrello?\')"><button type="button" class="btn btn-success">Acquista Carrello</button></a>';
        }
        echo '</div>
        <div class="col-lg-1">';
        if($stato_U["contenuto"]==0){
            echo '<a href="backend/annulla_Carr.php?codice_C='.$codice_cart.'" onclick="return confirm(\'Confermi di voler eliminare questo carrello?\')"><button type="button" class="btn btn-danger">Elimina Carrello</button></a>';
        }else{
            echo '<a href="#"><button type="button" class="btn btn-danger">Elimina Carrello</button></a>';
        }
        echo '</div>
    </div>';
    }
    return $risultato;
}

// func per i compratori, chiamata in print_Ordine.php
function stampaOrdine($cid,$codice_O,$email_U){
    $risultato= array("msg"=>"","status"=>"ok");
	$msg="";
    $prezzoCart_tot=0;
    $quantitàCart_tot=0;
    $sql="SELECT contiene.codice_P, contiene.quantità_C,prodotto.nome,prodotto.prezzo,prodotto.categoria,carrello.data_C,carrello.stato FROM contiene INNER JOIN prodotto ON contiene.codice_P=prodotto.codice INNER JOIN carrello ON contiene.codice_C=carrello.codice WHERE codice_C='" . $codice_O . "';";
    $res = $cid->query($sql);

    if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else if($res->num_rows==0){
        $msg = "Non sono presenti prodotti nel carrello";
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else{
        while($row=$res->fetch_assoc()){
            echo '<div class="row">
                <div class="col-lg-2">
                </div>
                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    '.$row["nome"].' ('.$row["prezzo"].'€)
                                </div>
                                <div class="col-sm-2">
                                    Quantità: '.$row["quantità_C"].'
                                </div>
                                <div class="col-sm-3">
                                    Prezzo: '.$row["quantità_C"]*$row["prezzo"].'€
                                </div>
                                <div class="col-sm-3">';
                                    //se data di consegna è minore o uguale alla data corrente allora si può scrivere la recensione
                                    //usiamo controllo utente per vedere se l'utente non sia bloccato
                                    $status=controlloUtente($cid, $email_U);
                                    list($risultato,$esiste)=checkRecensione($cid,$email_U,$row["codice_P"]);
                                    if($row["data_C"]<=date("Y-m-d")&&$status["contenuto"]==0&&$row["stato"]!="2"){
                                        // checkRecensione controllo se l'utente ha gia scritto una recensione per quel prodotto
                                        if($esiste==0){
                                            echo '<a href="recensione.php?codice_P='.$row["codice_P"].'&email_U='.$email_U.'&esiste='.$esiste.'&nome_P='.$row["nome"].'"><button class="btn btn-primary">Scrivi Recensione</button></a>';
                                        }else{
                                            echo '<button class="btn btn-success" onclick="showRecensione(\''.addslashes($row["codice_P"]).'\')">Visualizza Recensione</button>
                                            <hr>
                                            <a href="recensione.php?codice_P='.$row["codice_P"].'&email_U='.$email_U.'&esiste='.$esiste.'&nome_P='.$row["nome"].'&cat='.$row["categoria"].'"><button class="btn btn-primary">Modifica Recensione</button></a>
                                            <hr>
                                            <a href="backend/elimina_R.php?codice_P='.$row["codice_P"].'&email_U='.$email_U.'" onclick="return confirm(\'Confermi di voler eliminare questa recensione?\')"><button class="btn btn-danger">Elimina Recensione</button></a>';
                                        }
                                    // status==1 è un compratore bloccato
                                    }else if($row["data_C"]<=date("Y-m-d")&&$status["contenuto"]==1){
                                        if($esiste==0){
                                    echo '<h5>Utente Bloccato - Scrittura Recensione non disponibile</h5>';
                                    }else{
                                    echo '<button class="btn btn-success" onclick="showRecensione(\''.addslashes($row["codice_P"]).'\')">Visualizza Recensione</button>
                                    <hr>
                                    <h5>Utente Bloccato - Modifica non disponibile</h5>
                                    <hr>
                                    <a href="backend/elimina_R.php?codice_P='.$row["codice_P"].'&email_U='.$email_U.'" onclick="return confirm(\'Confermi di voler eliminare questa recensione?\')"><button class="btn btn-danger">Elimina Recensione</button></a>';
                                    }
                                    }else if($row["stato"]=="2"){
                                        echo '<button class="btn btn-warning">Ordine Annullato</button>
                                              <h5>Pubblicazione Recensione Non Consentita</h5>';
                                    }else {
                                        echo '<h5>Prodotto non ancora consegnato - Funzionalità non disponibile</h5>
                                        <button type="button" class="btn btn-primary">Scrivi Recensione</button>';
                                    }
                            echo '</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
            $prezzoCart_tot+=$row["prezzo"]*$row["quantità_C"];
            $quantitàCart_tot+=$row["quantità_C"];        
        }
        echo '<div class="row">
            <div class="col-lg-2">
            </div>
            <div class="col-lg-2">
                <h3 style="color:white;"><b>Prezzo Totale: '.$prezzoCart_tot.'€</b></h3>
            </div>
            <div class="col-lg-2">
                <h3 style="color:white;"><b>Numero Pezzi: '.$quantitàCart_tot.'</b></h3>
            </div>
        </div>';
        }
    return $risultato;
}

// func chiamato in func stampaOrdine() per controllare se esiste una recensione dall'utente
function checkRecensione($cid,$email_U,$codice_P){
    $risultato= array("msg"=>"","status"=>"ok");
	$msg="";
    $esiste=0;
    // controllo accesso DB
    if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else 
            $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}
    // per prendere il carrello con stato=0 dell'utente corrente
    $sql = "SELECT * FROM recensione WHERE email_U='" . $email_U . "' AND codice_P='".$codice_P."';";
    $res = $cid->query($sql);
    if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
        return array($risultato,NULL);
    }else if($res->num_rows==0){
        $esiste=0;
    }else{
        $esiste=1;
    }
    return array($risultato,$esiste);
}

// func chiamata in addRecensione-exe per creare o modificare una recensione
function aggiungiRecensione($cid,$recensione,$email_U,$rating, $codice_P, $esiste){
    $risultato= array("msg"=>"","status"=>"ok");
	$msg="";

	if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}

    // se una recensione dall'utente per il prodotto con $codice_P non esiste ancora --> crea recensione
    // se invece esiste --> modifica la recensione esistente
    if($esiste==0){
        $sql = "INSERT INTO recensione(email_U,codice_P,testo,punteggio) VALUES ('$email_U', '$codice_P', '$recensione', '$rating');";
    }else{
        $sql = "UPDATE recensione SET punteggio = '$rating', testo = '$recensione' WHERE email_U = '$email_U' AND codice_P = '$codice_P';";
    }
    $res=$cid->query($sql);

    if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }
    return $risultato;
}

// func che aggiorna la rating media di un prodotto dopo aggiunta e eliminazione recensioni
function calcolaRate($cid,$codice_P){
    $risultato= array("msg"=>"","status"=>"ok");
	$msg="";

	if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}

    
    $rate=0; // var che sarà il rate finale del prodotto
    $cnt=0; // var per contare numero di recensioni

    // query per calcolare la nuova rating media
    $sql="SELECT*FROM recensione WHERE codice_P='" . $codice_P . "'; ";
    $res=$cid->query($sql);
    if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else if($res->num_rows==0){
        $msg = "La tabella non contiene tuple";
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else{
        // calcolo rating media
        while($row=$res->fetch_assoc()){
            $rate+=$row["punteggio"];
            $cnt++;
        }
        $rate=$rate/$cnt;
        $rate=round($rate,1);
    }
    // seconda query per aggiornare la rating media al DB
    $sql2="UPDATE prodotto SET rating='" . $rate . "' WHERE codice='" . $codice_P . "'; ";
    $res2=$cid->query($sql2);
    if ($res2==null) {
        $msg2 = "Si sono verificati i seguenti errori:<br/>" . $res2->error;
        $risultato["status"]="ko";
        $risultato["msg"].=$msg2;
    }else if($res2->num_rows==0){
        $msg2 = "La tabella non contiene tuple";
        $risultato["status"]="ko";
        $risultato["msg"].=$msg2;
    }
return $risultato;    
}

// func chiamato in prodotto.php per stampare la pagina di un prodotto
function stampaProdotto($cid,$codice_P,$email_U,$categoria){
    $risultato= array("msg"=>"","status"=>"ok");
    $risultato2= array("msg"=>"","status"=>"ok");
	$msg="";

	if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}
    $sql = "SELECT  prodotto.codice, prodotto.nome, prodotto.colore, prodotto.prezzo, prodotto.quantitàM, prodotto.rating, prodotto.descrizione , utente.marca FROM prodotto INNER JOIN utente ON prodotto.email_V=utente.email WHERE utente.tipo='V' AND prodotto.codice='$codice_P';";
    $res = $cid->query($sql);
    if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else if($res->num_rows==0){
        $msg = "La tabella non contiene tuple";
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
        }
        else{
            $row=$res->fetch_assoc();
                // creiamo un'array associativa per i campi
            $prodotto[] = array(
                "codice" => $row["codice"],
                "marca" => $row["marca"],
                "prezzo" => $row["prezzo"],
                "descrizione" => $row["descrizione"],
                "nome" => $row["nome"],
                "rating" => $row["rating"],
                "quantitàM" => $row["quantitàM"]);
            $risultato["contenuto"]=$prodotto;

            // stampiamo i dettagli del prodotto
            echo '<div class="container mt-5">
            <div class="card mb-3">
                <div class="row">
                    <div class="col-md-4">
                            <img src="images/' . $prodotto[count($prodotto) - 1]['codice'] . '.jpg" class="card-img" alt="">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body d-flex flex-column" style="height: 100%;">
                            <div>
                                <h1 class="card-title"><b>' . $prodotto[count($prodotto) - 1]['nome'] . '</b></h1>
                                <h4 class="card-text">Marca: ' . $prodotto[count($prodotto) - 1]['marca'] . '</h4>
                                <h4 class="card-text">Rating: ' . $prodotto[count($prodotto) - 1]['rating'] . '</h4>
                                <h5 class="card-text">' . $prodotto[count($prodotto) - 1]['descrizione'] . '</h5>
                                <h2 class="card-text"><b>' . $prodotto[count($prodotto) - 1]['prezzo'] . '€</b></h2>
                            </div>';
                            if ($email_U){ //controllo stato e tipo utente
                            $res2=controlloUtente($cid, $email_U);
                            if ($res2["contenuto"]==0){
                                echo '<div class="mt-auto">';
                                    if($prodotto[count($prodotto) - 1]['quantitàM']>0){
                                    echo '<a href="backend/modifyQ.php?codice_P=' . $prodotto[count($prodotto) - 1]['codice'] . '&cat='.$categoria.'&mod=add"><button type="button" class="btn btn-danger">Aggiungi al Carrello</button></a>';
                                    }else{
                                    echo '<h5 style="display:inline-block; width:200px; color:red;"><b>Prodotto Esaurito!</b></h5>';
                                    }
                                    echo '</div>';
                            }else if ($res2["contenuto"]==1){
                                echo '<div class="mt-auto">
                                <h5>Utente Bloccato - Funzionalità non disponibile</h5>
                                <button type="button" class="btn btn-danger">Aggiungi Al Carrello</button>
                            </div>';
                            }else if ($res2["contenuto"]==4){
                                echo '<div class="mt-auto">
                                <h5>Utente Non Autorizzato - Funzionalità non disponibile</h5>
                                <button type="button" class="btn btn-danger">Aggiungi Al Carrello</button>
                            </div>';
                            }else if($res2["contenuto"]==6){
                                echo '<a href="backend/rimuovi_P.php?codice=' . $prodotto[count($prodotto) - 1]['codice'] . '" onclick="return confirm(\'Confermi di voler eliminare il prodotto?\')"><button type="button" class="btn btn-danger">Elimina Prodotto</button></a>';
                            }
                        }else{
                            echo '<div class="mt-auto">
                                <h5><b>Fai il Log-In per poter Acquistare</b></h5>
                            </div>';
                        }
                       echo '</div>
                    </div>
                </div>
            </div>
        </div>';
        // stampiamo le recensioni del prodotto
    $risultato2=stampaRecensioni($cid,$codice_P);
    }
    return array($risultato,$risultato2);
}

// func per i compratori e venditori, chiamato in signup-exe.php per inserire un'utente al DB
function scriviUtente($cid,$nome,$cognome,$email,$password,$cf,$pi,$marca,$tipo,$data_nascita,$via,$città,$provincia,$cap,$nazione){
    $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");
    $msg="";
    $errore=false;
    $nome = trim($nome);
    $cognome = trim($cognome);
    $email = trim($email);
    $password = trim($password);
    $data_nascita=trim($data_nascita);
    $tipo=trim($tipo);
    $cf = trim($cf);
    $pi=trim($pi);
    $marca=trim($marca);
    $via = trim($via);
    $città = trim($città);
    $provincia = trim($provincia);
    $cap = trim($cap);
    $nazione = trim($nazione);
    if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
        if (!is_null($cid))
            $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
        else $risultato["msg"]="errore nella connessione al db ";
        return $risultato;
    }

    // controlli sui parametri di input
    if (empty($nome) || empty($cognome) || empty($email) || empty($password)){
        $errore = true;
        $msg = "Uno o più parametri obbligatori non è specificato<br/>";
    }
	if (strlen($password)<3)
	{
		$errore = true;
		$msg .= "La password deve essere una stringa di almeno 3 caratteri <br/>";
	}
	if (!ctype_alpha($nome) || !ctype_alpha($cognome))
	{
		$errore = true;
		$msg .= "Nome/cognome non deve avere numeri/simboli<br/>";
	}

    //query per controllo email nel database
    $sql2="SELECT * FROM utente WHERE email='$email';";
    $res2=$cid->query($sql2);

    if($res2->num_rows > 0){
        $errore = true;
		$msg .= "L'email inserita è già registrata<br/>";
    }

    if (!$errore){
        if(empty($data_nascita))
        {
            // se data_nascita non entrato dall'utente non la mettiamo nella query --> se no, da errore
            $sql = "INSERT INTO utente(nome, cognome, email, password, cf,pi,marca,tipo, via, città, provincia, cap, nazione) VALUES ('$nome', '$cognome', '$email', '$password', '$cf', '$pi','$marca','$tipo','$via', '$città', '$provincia', '$cap', '$nazione');";
            $res=$cid->query($sql);
        }else{
            $sql = "INSERT INTO utente(nome, cognome, email, password, cf,pi,marca,tipo,data_nascita, via, città, provincia, cap, nazione) VALUES ('$nome', '$cognome', '$email', '$password', '$cf', '$pi','$marca','$tipo','$data_nascita','$via', '$città', '$provincia', '$cap', '$nazione');";
            $res=$cid->query($sql);
        }
            
        if ($res==1)
            $risultato["msg"]="Operazione di inserimento si è conclusa con successo";
        else{
            $risultato["status"]="ko";
            $risultato["msg"]="Operazione di inserimento è fallita";
        }
    }else {
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }
    return $risultato;
}

// func per i gli utenti, chiamato in updateProfilo-exe.php per aggiornare i dati di un'utente
function aggiornaUtente($cid,$nome,$cognome,$email,$password,$cf,$pi,$marca,$data_nascita,$via,$città,$provincia,$cap,$nazione){
    $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");
    $msg="";
    $errore=false;
    $nome = trim($nome);
    $cognome = trim($cognome);
    $email = trim($email);
    $password = trim($password);
    //$data_nascita=trim($data_nascita);
    $cf = trim($cf);
    $pi=trim($pi);
    $marca=trim($marca);
    $via = trim($via);
    $città = trim($città);
    $provincia = trim($provincia);
    $cap = trim($cap);
    $nazione = trim($nazione);

    if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
        if (!is_null($cid))
            $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
        else $risultato["msg"]="errore nella connessione al db ";
        return $risultato;
    }
    // controlli sui parametri di input
    if (empty($nome) || empty($cognome)){
        $errore = true;
        $msg = "Uno o più parametri obbligatori non è specificato<br/>";
    }
	if (strlen($password)<3)
	{
		$errore = true;
		$msg .= "La password deve essere una stringa di almeno 3 caratteri <br/>";
	}

	if (!ctype_alpha($nome) || !ctype_alpha($cognome))
	{
		$errore = true;
		$msg .= "Nome/cognome non deve avere numeri/simboli<br/>";
	}
    
    // controlliamo esistenza utente in DB
    $sql2 = "SELECT * FROM utente WHERE email='$email';";
    $res2=$cid->query($sql2);

    if ($res2==null){
        $risultato["status"]="ko";
        $risultato["msg"]="Operazione di inserimento è fallita";
        return $risultato;
    }
    
    $riga1 = $res2->fetch_assoc();
    
    $virgola = 0; // usato per controllare la procedura e aggiungere virgole tra le stringhe

if (!$errore){
        // per ogni campo controlliamo l'esistenza e confrontiamo i dati, se è diverso cioè aggiornato --> aggiungiamolo allo string della query

        $sql = "UPDATE Utente SET ";
        if (!empty($nome) && $nome != $riga1["nome"]){
            $sql .= "nome = '" . $nome."' ";
            $virgola = 1;
        }
        if (!empty($cognome) && $cognome != $riga1["cognome"]){
            if ($virgola == 1){
                $sql .= ", ";
            }
            $sql .= "cognome = '" . $cognome."' ";
            $virgola = 1;
        }
        if (!empty($password) && $password != $riga1["password"]){
            if ($virgola == 1){
                $sql .= ", ";
            }
            $sql .= "password = '" . $password."' ";
            $virgola = 1;
        }
        if (!empty($cf) && $cf != $riga1["cf"]){
            if ($virgola == 1){
                $sql .= ", ";
            }
            $sql .= "cf = '" . $cf."' ";
            $virgola = 1;
        }
        if (!empty($pi) && $pi != $riga1["pi"]){
            if ($virgola == 1){
                $sql .= ", ";
            }
            $sql .= "pi = '" . $pi."' ";
            $virgola = 1;
        }
        if (!empty($marca) && $marca != $riga1["marca"]){
            if ($virgola == 1){
                $sql .= ", ";
            }
            $sql .= "marca = '" . $marca."' ";
            $virgola = 1;
        }
        if ($data_nascita != '' && $data_nascita != $riga1["data_nascita"]){
            if ($virgola == 1){
                $sql .= ", ";
            }
            $sql .= "data_nascita = '" . $data_nascita."' ";
            $virgola = 1;
        }
        if (!empty($via) && $via != $riga1["via"]){
            if ($virgola == 1){
                $sql .= ", ";
            }
            $sql .= "via = '" . $via."' ";
            $virgola = 1;
        }
        if (!empty($città) && $città != $riga1["città"]){
            if ($virgola == 1){
                $sql .= ", ";
            }
            $sql .= "città = '" . $città."' ";
            $virgola = 1;
        }
        if (!empty($provincia) && $provincia != $riga1["provincia"]){
            if ($virgola == 1){
                $sql .= ", ";
            }
            $sql .= "provincia = '" . $provincia."' ";
            $virgola = 1;
        }
        if (!empty($cap) && $cap != $riga1["cap"]){
            if ($virgola == 1){
                $sql .= ", ";
            }
            $sql .= "cap = '" . $cap."' ";
            $virgola = 1;
        }
        if (!empty($nazione) && $nazione != $riga1["nazione"]){
            if ($virgola == 1){
                $sql .= ", ";
            }
            $sql .= "nazione = '" . $nazione."' ";
            $virgola = 1;
        }

if($virgola==1){
        $sql .= "WHERE email = '" . $riga1["email"]."';";
        $res=$cid->query($sql);
        //$risultato["msg"]=$sql;

        if ($res==1)
            $risultato["msg"].="Operazione di inserimento si è conclusa con successo<br/>";
        else{
            $risultato["status"]="ko";
            $risultato["msg"]="Operazione di inserimento è fallita";
        }
    }else {
        $risultato["status"]="ko";
        $risultato["msg"]="Non è stata fatta nessuna richiesta di modifica";
    }
}
    return $risultato;
}

// func per i compratori, chiamato in updateProdotto-exe.php, usato per aggiornare i dettagli di un prodotto
function aggiornaProdotto($cid,$nome,$codice,$categoria,$colore,$prezzo,$descrizione,$quantitàM,$flagImg){
    $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");
    $msg="";
    $nome = trim($nome);
    $codice = trim($codice);
    $categoria = trim($categoria);
    $colore = trim($colore);
    $prezzo = trim($prezzo);
    $descrizione=trim($descrizione);
    $quantitàM=trim($quantitàM);
    // controllo accesso DB
    if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
        if (!is_null($cid))
            $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
        else $risultato["msg"]="errore nella connessione al db ";
        return $risultato;
    }

    // controllo correttezza inserimento di tutti i dati required
    if (empty($nome) || empty($categoria) || empty($colore) || empty($prezzo) || empty($descrizione)){
        $msg = "Uno o più parametri obbligatori non sono specificati<br/>";
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
        return $risultato;
    }

    // se la quantità in magazzino = 0, php la legge come empty
    if(empty($quantitàM)){
        $quantitàM = 0;
    }

    // query per prendere il prodotto da modificare
    $sql2 = "SELECT * FROM prodotto WHERE codice='$codice';";
    $res2=$cid->query($sql2);
    // controllo su query per prodotto da modificare
    if ($res2!=null)
        $risultato2["msg"]="Operazione di controllo del prodotto si è conclusa con successo";
    else{
        $risultato2["status"]="ko";
        $risultato2["msg"]="Operazione di controllo del prodotto è fallita";
        return $risultato2;
    }
    // richiesta query e creazione query a seconda delle modifiche richieste
    $riga1 = $res2->fetch_assoc();
    $virgola = 0; // usato per controllare la procedura e aggiungere virgole tra le stringhe

    // per ogni campo controlliamo l'esistenza e confrontiamo i dati, se è diverso cioè aggiornato --> aggiungiamolo allo string della query
    $sql = "UPDATE prodotto SET ";
    if (!empty($nome) && $nome != $riga1["nome"]){
        $sql .= "nome = '" . $nome."' ";
        $virgola = 1;
    }
    if (!empty($categoria) && $categoria != $riga1["categoria"]){
        if ($virgola == 1){
            $sql .= ", ";
        }
        $sql .= "categoria = '" . $categoria."' ";
        $virgola = 1;
    }
    if (!empty($colore) && $colore != $riga1["colore"]){
        if ($virgola == 1){
            $sql .= ", ";
        }
        $sql .= "colore = '" . $colore."' ";
        $virgola = 1;
    }
    if (!empty($prezzo) && $prezzo != $riga1["prezzo"]){
        if ($virgola == 1){
            $sql .= ", ";
        }
        $sql .= "prezzo = '" . $prezzo."' ";
        $virgola = 1;
    }
    if (!empty($descrizione) && $descrizione != $riga1["descrizione"]){
        if ($virgola == 1){
            $sql .= ", ";
        }
        $sql .= "descrizione = '" . $descrizione."' ";
        $virgola = 1;
    }
    if ($quantitàM != '' && $quantitàM != $riga1["quantitàM"]){
        if ($virgola == 1){
            $sql .= ", ";
        }
        $sql .= "quantitàM = '" . $quantitàM."' ";
        $virgola = 1;
    }
    // se virgola==1 almeno una modifica dei dati è stata fatta e quindi viene completata la query
    if($virgola!=0){
    $sql .= "WHERE codice = '" . $riga1["codice"]."';";
    $res=$cid->query($sql);
        if ($res==1)
            $risultato["msg"]="Operazione di modifica si è conclusa con successo";
        else{
            $risultato["status"]="ko";
            $risultato["msg"]="Operazione di modifica è fallita";
        }
    }else{
        // se è stata caricata un immagine flagImg=1
        if ($flagImg==1){
            $risultato["msg"]="Operazione di modifica si è conclusa con successo";
        }else{
            $risultato["status"]="ko";
            $risultato["msg"]="Non è stata fatta nessuna richiesta di modifica";
        }
    }
    return $risultato;
}

// func per i compratori, chiamato in aggiungi_P-exe.php che aggiunge un prodotto al DB
function aggiungiProdotto($cid,$codice,$email_V,$nome,$categoria,$colore,$prezzo,$descrizione,$quantitàM){
    $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");
    $msg="";
    $errore=false;
    $codice = trim($codice);
    $nome = trim($nome);
    $categoria = trim($categoria);
    $colore = trim($colore);
    $prezzo = trim($prezzo);
    $descrizione=trim($descrizione);
    $quantitàM=trim($quantitàM);
    if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
        if (!is_null($cid))
            $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
        else $risultato["msg"]="errore nella connessione al db ";
        return $risultato;
    }
    if (empty($codice) || empty($nome) || empty($categoria) || empty($colore) || empty($prezzo)|| empty($descrizione)|| empty($quantitàM)){
        $errore = true;
        $msg = "è obbligatorio Inserire tutti i dati<br>";
    }

    //query per controllo codice prodotto nel database
    $sql2="SELECT * FROM prodotto WHERE codice='$codice';";
    $res2=$cid->query($sql2);

    if($res2->num_rows > 0){
        $errore = true;
		$msg .= "Il codice inserito è già utilizzato, sceglierne un'altro<br/>";
    }
    if (!$errore){
        // se il codice prodotto è valido, aggiungiamo il prodotto al DB
        $sql = "INSERT INTO prodotto(nome,codice,email_V,categoria,colore,prezzo,descrizione,quantitàM,rating) VALUES ('$nome', '$codice', '$email_V', '$categoria', '$colore', '$prezzo', '$descrizione','$quantitàM',NULL);";
        $res=$cid->query($sql);
        if ($res==1)
            $risultato["msg"]="Operazione di inserimento si è conclusa con successo";
        else{
            $risultato["status"]="ko";
            $risultato["msg"]="Operazione di inserimento è fallita";
        }
    }else {
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }
    return $risultato;
}

// func chiamata in modifyQ.php e in StampaProdotti() e StampaProdotto() che aggiunge o elimina un prodotto al carrello dell'utente corrente
function modifyQ($cid, $codice_P,$codice_cart,$mod){
    
    $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");
    $msg="";
    $errore=false;
    
    if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
            if (!is_null($cid)){
                $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
            }else {
                $risultato["msg"]="errore nella connessione al db ";
            }
            return $risultato;
        
    }

    // query che controlla la presenza nel carrello del prodotto
    $sql="SELECT contiene.codice_C, contiene.codice_P, contiene.quantità_C, prodotto.quantitàM FROM contiene INNER JOIN prodotto ON contiene.codice_P = prodotto.codice WHERE contiene.codice_P='$codice_P' AND contiene.codice_C='$codice_cart';";
    $res=$cid->query($sql);
    $row=$res->fetch_assoc();
    // se si, vuol dire che nel carrello c'è questo prodotto e dobbiamo incrementare/decrementare la sua quantita in carrello
if($res->num_rows > 0){
        // controllo sulla modalità di modifica della quantità (add o sub)
        if($mod=="add"){
        $quantita = $row["quantità_C"]+1;
        }else{
        $quantita = $row["quantità_C"]-1;
        }
        // controlliamo la disponibilità in magazzino prima di fare la modifica di quantità al carrello
        
        if ($quantita <= $row["quantitàM"]&&$quantita>0){
            // allora incremento/decremento
            $sql2 = "UPDATE contiene SET quantità_C='$quantita' WHERE codice_C = '$codice_cart' AND codice_P = '$codice_P';";
            $res2=$cid->query($sql2);

            if ($res2==null){
                $risultato["msg"]= "Operazione non riuscita: $cid->errno: $cid->error()<br/>";
                $risultato["status"]="ko";
            }
            else{
                $risultato["msg"] = "L'operazione si &egrave; conclusa con successo";
                $risultato["status"]="ok";
            }
            return $risultato;

        }else{
            // in base all'operzione richiesta la modificaa della quantità non è possibile per il numero di porodotti in magazzino
            $risultato["msg"]="Operazione non possibile in base alla quantità del prodotto in magazzino";
            $risultato["status"]="ko";
            return $risultato;
        }
    }else {//se non ci sono tuple con quel codice Prodotto in contiene vuol dire che non si avrà la quantità del prodotto nel magazzino
        //facciamo una query pper repndere la quantità del prodotto nel magazzino
        $sql2="SELECT quantitàM FROM prodotto WHERE codice='$codice_P';";
        $res2=$cid->query($sql2);
        if ($res==null){
		$risultato["msg"]= "Problema nella richiesta al DB: $cid->errno: $cid->error()<br/>";
		$risultato["status"]="ko";
	    }
	    $row2=$res2->fetch_assoc(); 
            if ($row2["quantitàM"]>=1&&$mod=="add"){
        // il prodotto non è presente nel carrello lo aggiungiamo con qt=1
        
                $sql3 = "INSERT INTO contiene(codice_C,codice_P,quantità_C) VALUES ('$codice_cart', '$codice_P', 1);";
                $res3=$cid->query($sql3);
                if ($res3==null){
                    $risultato["msg"]= "Problema nell'aggiunta del prodotto al carrello: $cid->errno: $cid->error()<br/>";
                    $risultato["status"]="ko";
                }
            }
            
        }
    return $risultato;
}

// Funzione che aggiorna lo stato di un ordine
function modify_O($cid,$codice_C,$stato_O){
    //  0 -> carrrello non acquistato (default se un carrello non è acquistato)
    //  1 -> carrello acquistato
    //  2 -> ordine annullato
    //  3 -> ordine recesso
    $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");
    $msg="";
    $errore=false;
    
    if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
            if (!is_null($cid)){
                $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
            }else {
                $risultato["msg"]="errore nella connessione al db ";
            }
            return $risultato;
    }
    //query per settare il nuovo stato al carrello Annullato(2)/Recesso(3)
    $sql=" UPDATE carrello SET stato='$stato_O' WHERE codice='$codice_C';";
    $res = $cid->query($sql);
	if ($res==null)
	{
		$risultato["msg"]= "Problema nell'aggiornammento dello stato del carrello: $cid->errno: $cid->error()<br/>";
		$risultato["status"]="ko";
	}
	else
	{
	   $risultato["msg"] = "L'operazione di aggiornamento dello stato del carrello si &egrave; conclusa con successo";
	   $risultato["status"]="ok";
	}
    
    // SELECT per avere i dati del carrello contente i prodotti dell ordine annullato/recesso e aggiornare la quantità in magazzino (reinserimento dei prodotti nel magazzino perchè ordine annullato/recesso, valido per entrambi gli stati)
    $sql2="SELECT contiene.codice_C, contiene.codice_P, contiene.quantità_C, prodotto.quantitàM FROM contiene INNER JOIN prodotto ON contiene.codice_P = prodotto.codice WHERE contiene.codice_C='$codice_C';";
    $res2 = $cid->query($sql2);
	if ($res2==null)
	{
		$risultato["msg"].="Problema nella query di estrazione contiene inner prodotto: $cid->errno: $cid->error()<br/>";
		$risultato["status"]="ko";
	}
	else
	{
	   $risultato["msg"] .="Query si &egrave; conclusa con successo";
	   $risultato["status"]="ok";
	}

    while($row2=$res2->fetch_assoc()){
        $New_quantitàM=$row2["quantitàM"]+$row2["quantità_C"];
        $codice_P=$row2["codice_P"];
        $sql3="UPDATE prodotto SET quantitàM='$New_quantitàM' WHERE codice='$codice_P' ;";
        $res3 = $cid->query($sql3);
        if ($res3==null){
		$risultato["msg"].="Problema nell'aggiornamento della quantitàM del prodotto: $cid->errno: $cid->error()<br/>";
		$risultato["status"]="ko";
	    }else{
	    $risultato["msg"] .="L'operazione di aggiornamento della quantitàM si &egrave; conclusa con successo";
	    $risultato["status"]="ok";
	    }

    }

return $risultato;
}

// func per i compratori, chiamato in rimuovi_Carr.php per rimuovere un prodotto dal carrello corrente, rimuovendo dal DB la tupla di "contiene" correlata
function rimuovi_Carr($cid, $codice_P,$codice_C){
    $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");
    $msg="";
    if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
        if (!is_null($cid))
            $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
        else $risultato["msg"]="errore nella connessione al db ";
        return $risultato;
    }

    // query per rimuovere il prodotto dal carrello
    $sql = "DELETE FROM contiene WHERE codice_C = '$codice_C' AND codice_P = '$codice_P';";
	$res = $cid->query($sql);

	if ($res==null)
	{
		$risultato["msg"]= "Problema nella rimozione del prodotto: $cid->errno: $cid->error()<br/>";
		$risultato["status"]="ko";
	}
	else
	{
	   $risultato["msg"] = "L'operazione di rimozione si &egrave; conclusa con successo";
	   $risultato["status"]="ok";
	}
	return $risultato;
}

function checkAddress ($cid,$email_U){
    $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");
    $msg="";
    if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
        if (!is_null($cid))
            $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
        else $risultato["msg"]="errore nella connessione al db ";
        return $risultato;
    }
    $sql="SELECT * FROM utente WHERE email='$email_U';";
    $res=$cid->query($sql);
    if ($res==null){
		$risultato["msg"]= "Problema nel confronto di quantità dei prodotti: $cid->errno: $cid->error()<br/>";
		$risultato["status"]="ko";
	}else{
        $row=$res->fetch_assoc();
        if(empty($row["via"])||empty($row["città"])||empty($row["provincia"])||empty($row["cap"])||empty($row["nazione"])||empty($row["cf"])){
            $risultato["status"]="ko";
            $risultato["msg"]="Indirizzo o Codice Fiscale non fornito completamente dall'utente, impossibile completare l'acquisto.\nAggiornare i dati";
        }
        
    }
    return $risultato;
}

// func per i compratori, chiamato in confermaOrdine.php per conferma l'acquisto
// 1. carrello diventa ordine dell'utente
// 2. controllo ancora il magazzino per ogni prodotto
// 3. togliamo i prodotti venduti dal magazzino
function conferma_Carr($cid, $codice_C, $prezzoTot){
    $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");
    $msg="";
    if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
        if (!is_null($cid))
            $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
        else $risultato["msg"]="errore nella connessione al db ";
        return $risultato;
    }


    // query per fare il confronto delle quantità nel magazzino
    $sql="SELECT contiene.quantità_C, prodotto.quantitàM, prodotto.codice FROM contiene INNER JOIN prodotto ON contiene.codice_P = prodotto.codice WHERE contiene.codice_C='$codice_C';";
    $res=$cid->query($sql);
    if ($res==null)
	{
		$risultato["msg"]= "Problema nel confronto di quantità dei prodotti: $cid->errno: $cid->error()<br/>";
		$risultato["status"]="ko";
	}else{
        while($row=$res->fetch_assoc()){
            // controlliamo la disponibilità in magazzino dei prodotti contenuti in carrello
            if ($row["quantità_C"] > $row["quantitàM"]){ 
                $risultato["msg"]= "Alcuni prodotti non sono più disponibili";
                $risultato["status"]="ko";
                return $risultato;
            }else{
                // 2. vengono tolto dal magazzino
                $codice_P=$row["codice"];
                $quantitaNuova = $row["quantitàM"] - $row["quantità_C"];
                $sql3 = "UPDATE prodotto SET quantitàM='$quantitaNuova' WHERE codice = '$codice_P';";
                $res3 = $cid->query($sql3);
            }
        }
        // diventano mio: query per confermare l'ordine
        $sql2 = "UPDATE carrello SET stato=1, prezzoTot='$prezzoTot', data_A=CURDATE(), data_C=DATE_ADD(CURDATE(),INTERVAL 5 DAY) WHERE codice = '$codice_C';";
        $res2 = $cid->query($sql2);

        if ($res2==null && $res3==null)
        {
            $risultato["msg"]= "Problema nella conferma del carrello: $cid->errno: $cid->error()<br/>";
            $risultato["status"]="ko";
        }else{
        $risultato["msg"] = "L'operazione di conferma ordine si &egrave; conclusa con successo";
        $risultato["status"]="ok";
        }
    }
	return $risultato;
}

// func chiamato in login-exe.php per login utente
function loginUtente($cid,$email,$password){
  $utente = array();

  $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");

    if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
        if (!is_null($cid))
                $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
        else $risultato["msg"]="errore nella connessione al db ";
        return $risultato;
    }
    $sql= "SELECT * FROM utente WHERE email='$email' AND password='$password';";
    $res = $cid->query($sql);
    if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
        // il prossimo controllo viene fatto perchè user e pw sono su una sola tupla
    }else if($res->num_rows==0 || $res->num_rows>1){
        $msg = "email o password sbagliate";
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else{ 
        // L'interrogazione è andata a buon fine e posso leggere le tuple risultato
        // fetch_assoc() prende i dati dal database e li associa ai dati che tu hai dato
        // $row avrà i dati del database grazie a fetch_assoc(), quindi ad utente associamo i vari campi alle righe del database
        if ($row=$res->fetch_assoc()){
        $utente=array("email" => $row["email"], "password" =>$row["password"],"tipo"=>$row["tipo"],"cognome"=>$row["cognome"],"nome"=>$row["nome"]);}
        $risultato["contenuto"]=$utente;
    }
return $risultato;
}

// func per gli admin, chiamato in blocca_U per bloccare/sbloccare un'utente, aggiornando il block_status
function bloccaUtente($cid, $email,$statU){//statU serve per capire se l'utente deve essere bloccato o sbloccato
	$risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");

	if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}
    if($statU==0){//se l''utete non è bloccato allora aggiorna lo stato e bloccalo
	$sql = "UPDATE utente SET block_status='1' WHERE email = '$email';";
	$res = $cid->query($sql);
    }else{//se l'utente è bloccato allora sbloccalo
    $sql = "UPDATE utente SET block_status='0' WHERE email = '$email';";
	$res = $cid->query($sql);
    }
	  if ($res==null)
		{
			$risultato["msg"]= "Problema nel blocco dell'utente: $cid->errno: $cid->error()<br/>";
			$risultato["status"]="ko";
		}
	  else
	  {
	   $risultato["msg"] = "L'operazione di blocco si &egrave; conclusa con successo";
	   $risultato["status"]="ok";
	  }
	  return $risultato;
}

// func per gli admin, chiamato in autorizza-exe.php per autorizzare gli utenti in attesa
function autorizzaUtente($cid, $email){
	$risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");

	if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}

	$sql = "UPDATE utente SET autorizzazione='1' WHERE email = '$email';";
	$res = $cid->query($sql);

    if ($res==null)
    {
        $risultato["msg"]= "Problema nell'autorizzazione dell'utente: $cid->errno: $cid->error()<br/>";
        $risultato["status"]="ko";
    }
    else
    {
    $risultato["msg"] = "L'operazione di autorizzazione si &egrave; conclusa con successo";
    $risultato["status"]="ok";
    }
    return $risultato;
}

// func per gli admin, chiamato in elimina_U.php per rimuovere un'utente dal DB
function eliminaUtente($cid, $email){
	$risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");

	if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}

    $sql = "DELETE FROM utente WHERE email = '$email';";
	$res = $cid->query($sql);

	if ($res==null)
	{
		$risultato["msg"]= "Problema nel eliminazione dell'utente: $cid->errno: $cid->error()<br/>";
		$risultato["status"]="ko";
	}
	else
	{
	   $risultato["msg"] = "L'operazione di eliminazione si &egrave; conclusa con successo";
	   $risultato["status"]="ok";
	}
	return $risultato;
}

// func per i venditori, chiamato in rimuovi_P.php per rimuovere un prodotto dal sito/DB
function eliminaProdotto($cid, $codice){
	$risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");

	if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}

    $sql = "DELETE FROM prodotto WHERE codice = '$codice';";
	$res = $cid->query($sql);

	if ($res==null)
	{
		$risultato["msg"]= "Problema nel eliminazione dell'utente: $cid->errno: $cid->error()<br/>";
		$risultato["status"]="ko";
	}
	else
	{
	   $risultato["msg"] = "L'operazione di eliminazione si &egrave; conclusa con successo";
	   $risultato["status"]="ok";
	}
	return $risultato;
}

// func per i compratori, chiamato in annulla_Carr.php per annullare un carrello
// questa operazione rimuove tutte le tuple di "contiene" correlati e il carrello stesso
function elimina_Carr($cid, $codice_C){
	$risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");

	if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}

    // rimozione carrello dalla tabella carrello
    $sql = "DELETE FROM carrello WHERE codice = '$codice_C';";
	$res = $cid->query($sql);

    // rimuoviamo anche tutte le tuple di "contiene" che hanno codice_C
    $sql2 = "DELETE FROM contiene WHERE codice_C = '$codice_C';";
	$res2 = $cid->query($sql2);

	if ($res==null && $res2==null)
	{
		$risultato["msg"]= "Problema nel rimozione del carrello: $cid->errno: $cid->error()<br/>";
		$risultato["status"]="ko";
	}
	else
	{
	   $risultato["msg"] = "L'operazione di rimozione carrello si &egrave; conclusa con successo";
	   $risultato["status"]="ok";
	}
	return $risultato;
}

// func chiamato in elimina_R.php per rimuovere una recensione da un prodotto da parte dall'utente che l'ha scritto o l'admin
function eliminaRecensione($cid, $email_U, $codice_P){
	$risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");

	if ($cid == null || $cid->connect_errno) {
		$risultato["status"]="ko";
		if (!is_null($cid))
		     $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
		else $risultato["msg"]="errore nella connessione al db ";
		return $risultato;
	}

    $sql = "DELETE FROM recensione WHERE email_U = '$email_U' AND codice_P='$codice_P';";
	$res = $cid->query($sql);

	if ($res==null)
	{
		$risultato["msg"]= "Problema nel eliminazione della recensione: $cid->errno: $cid->error()<br/>";
		$risultato["status"]="ko";
	}
	else
	{
	   $risultato["msg"] = "Operazione di eliminazione si &egrave; conclusa con successo";
	   $risultato["status"]="ok";
	}
	return $risultato;
}

// func che controlla e ritorna il tipo di utente. sono codificati i tipi di utenti
function controlloUtente($cid, $email){
$risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");

  if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
        if (!is_null($cid))
                $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
        else $risultato["msg"]="errore nella connessione al db ";
    return $risultato;
  }
  $sql= "SELECT * FROM utente WHERE email='$email';";
  $res = $cid->query($sql);
  if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }
    else{
        if ($row=$res->fetch_assoc())
        {
            $status= $row["block_status"];
            $tipo= $row["tipo"];
            $autorizzazione=$row["autorizzazione"];
        }

        if($tipo=='C'&&$status==0&&$autorizzazione==1){ //Compratore non bloccato e autorizzato
            $risultato["contenuto"]=0;
        }else if($tipo=='C'&&$status==1&&$autorizzazione==1){ //Compratore bloccato
            $risultato["contenuto"]=1;
        }else if($tipo=='V'&&$status==0&&$autorizzazione==1){ //Venditore non bloccato
            $risultato["contenuto"]=2;
        }else if($tipo=='V'&&$status==1){ //Venditore bloccato
            $risultato["contenuto"]=3;
        }else if ($tipo=='C'&&$autorizzazione==0){//Compratore non ancora autorizzato
            $risultato["contenuto"]=4;
        }else if ($tipo=='V'&&$autorizzazione==0){//Venditore non ancora autorizzato
            $risultato["contenuto"]=5;
        }else{$risultato["contenuto"]=6;} //In caso di Admin

    }
    return $risultato;
}

// func chiamato in cat_prodotti.php per stampare le categorie (e le immagini) del catalogo
function LeggiCategorie($cid){
    $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");

    if ($cid == null || $cid->connect_errno) {
        $risultato["status"]="ko";
        if (!is_null($cid))
        $risultato["msg"]="errore nella connessione al db " . $cid->connect_error;
        else $risultato["msg"]="errore nella connessione al db ";
        return $risultato;
    }

    // query che prende le categorie
    $sql= "SELECT DISTINCT categoria FROM prodotto;";
    $res = $cid->query($sql);
    if ($res==null) {
        $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
        $risultato["status"]="ko";
        $risultato["msg"]=$msg;
    }else{
        $cont=1;
        while ($cat=$res->fetch_assoc())
        {
        if($cont%3==1){
            echo '<div class="card-group">';
        }
        // stampiamo un card con gli immagini per ogni categoria
        echo '<div class="card">
                <a href="categoria.php?id='.$cat["categoria"].'">
                <img src="images/'.$cat["categoria"].'.jpg" class="card-img-top" alt="...">
                </a>
                <div class="card-body">
                    <h2 class="card-title" style="text-align:center"><a href="categoria.php?id='.$cat["categoria"].'"><b>'.strtoupper($cat["categoria"]).'</b></a></h2>
                </div>
            </div>';
        if($cont%3==0){
            echo '</div>';
        }
        $cont++;
        }
        echo '</div>';
    }
return $risultato;
}

// func chiamato in categoria.php per filtrare i prodotti di una categoria
function filtroParametrico($cid,$categoria,$colore1,$marca1,$prezzo_min1,$prezzo_max1,$rating1){
    $risultato2 = array("status"=>"ok","msg"=>"", "contenuto"=>"");
    $risultato = array("status"=>"ok","msg"=>"", "contenuto"=>"");

    echo '
    <div align="center" class="text-white"> <!-- per centrare il filtro -->
    <form action="backend/filtro-exe.php" method="POST">
        <label for="colore">Colore:</label>
        <select name="colore">';
        // prendiamo i colori distinti dei prodotti in questa categoria
        $sql= "SELECT DISTINCT colore FROM prodotto WHERE categoria='$categoria';";
        $res=$cid->query($sql);
        if ($res==null) {
            $msg = "Si sono verificati i seguenti errori:<br/>" . $res->error;
            $risultato["status"]="ko";
            $risultato["msg"]=$msg;
        }else{
            echo '<option value=""></option>';
            while ($colore=$res->fetch_assoc()){
                if($colore["colore"]==$colore1){
                echo '<option value="'.$colore["colore"].'" selected>'.$colore["colore"].'</option>';
                }else{
                echo '<option value="'.$colore["colore"].'">'.$colore["colore"].'</option>';
                }
            }
        }

        echo '</select>
        <label for="marca">Marca:</label>
            <select name="marca">';
        // MARCHE
        $sql2= "SELECT DISTINCT utente.marca FROM utente INNER JOIN prodotto ON utente.email=prodotto.email_V WHERE utente.tipo='V' AND prodotto.categoria='$categoria';";
        $res2=$cid->query($sql2);
        if ($res2==null) {
            $msg = "Si sono verificati i seguenti errori:<br/>" . $res2->error;
            $risultato2["status"]="ko";
            $risultato2["msg"]=$msg;
        }else{
            echo '<option value=""></option>';
            while ($marca=$res2->fetch_assoc()){
                if($marca["marca"]==$marca1){
                echo '<option value="'.$marca["marca"].'" selected>'.$marca["marca"].'</option>';
                }else{
                echo '<option value="'.$marca["marca"].'">'.$marca["marca"].'</option>';
                }
            }
        }
        echo '</select>
        
        <label for="prezzo_min">Prezzo Minimo:</label>
        <input name="prezzo_min" type="number" value="'.$prezzo_min1.'"></input>
        <label for="prezzo_max">Prezzo Massimo:</label>
        <input name="prezzo_max" type="number" value="'.$prezzo_max1.'"></input>
        
        <label for="rating">Rating:</label>
        <select name="rating">
            <option value=""></option>';
            for($i=1;$i<=5;$i++){
                if($i==$rating1){
                echo '<option selected>'.$i.'+</option>';
                }else{
                echo '<option>'.$i.'+</option>';
                }
            }

            echo '</select>
        <input type="hidden" name="cat" value="'.$categoria.'"></input>
        <input type="submit" name="filtra" value="Filtra"></input>
        <input type="submit" name="reset" value="Reset"></input>
    </form> 
    </div>';


return array($risultato,$risultato2);
}

?>