<?php
    
    $title = "Index";
    require_once('header.php');
    require_once('DBConnection.php');
    require_once('settingsDB.php');
    
    ini_set ('display_errors', 1);
    error_reporting (E_ALL);
        
    function cleanSearchWords($search){
        //Set characters to erase
        $erase = array('/\,/', '/\./', '/\-/', '/\+/', '/\?/', '/\¿/', '/\=/', '/\(/', '/\)/',
         '/\//', '/\&/', '/\_/', '/\%/', '/\$/', '/\</', '/\>/', '/\!/', '/\¡/', '/\·/', '/\:/',
          '/\;/', '/\Ç/', '/\*/', '/\^/', '/\`/', '/\[/', '/\]/', '/\`/', "/\'/", '/\"/');
        //Replace chars -> white space "
        $words = preg_replace($erase, " ", $search);
        //Generate array with the result
        $array_words = explode(' ', $words);
        //Clean empty elements in array
        return array_filter($array_words);
    }
    
    function searchDescriptionInDB($array_words, $dbc, $num_tuplas){
        
        if($num_tuplas > 0){
            
            echo '<table>
                 <tr>
                     <th>Job ID</th>
                     <th>Title</th>
                     <th>Description</th>
                     <th>City</th>
                     <th>Company</th>
                     <th>Date Posted</th>
                 </tr>';
                 
            $likes = array();
            foreach($array_words as $word){
                $likes[] = "`description` LIKE '%".$word."%'";
            }
            
            $clausulaLike = implode(' OR ', $likes);
            $max_tuplas=5;
            $paginas = ceil($num_tuplas / $max_tuplas);
            
            if(isset($_GET['pagina'])){
                $pagina = $_GET['pagina'];
            } else {
                $pagina = 1;
            }
            
            $inicio = ($pagina - 1) * $max_tuplas;
            $sql= "SELECT * FROM `riskyjobs` WHERE ".$clausulaLike." LIMIT ".$inicio.",".$max_tuplas.";";
            $resultset = $dbc->getQuery($sql);
            
            while($row = $resultset->fetch()){
                echo '<tr>
                        <td>'.$row['job_id'].'</td>
                        <td>'.$row['title'].'</td>
                        <td class="description">'.$row['description'].'</td>
                        <td>'.$row['city'].'</td>
                        <td>'.$row['company'].'</td>
                        <td>'.$row['date_posted'].'</td>
                      </tr>';
            }
            echo'</table>';
            
            // Poner botones
            $searchWords = implode('%20',$array_words);
            echo '<ul class="paginacion">';
            switch($pagina){
                case 1:
                    echo '<li class="selected">Anterior</li>
                          <li class="selected">1</li>';
                    for ($cont = 2; $cont<= $paginas; $cont++){
                        echo '<li class="selected"><a href="index.php?pagina='.$cont.'&lista_words='.$searchWords.'">'.$cont.'</a></li>';
                    }
                    echo '<li class="selected"><a href="index.php?pagina=2&lista_words='.$searchWords.'">Siguiente</a></li>';
                    break;
                    
                case $paginas:
                    echo '<li class="selected"><a href="index.php?pagina='.($pagina-1).'&lista_words='.$searchWords.'">Anterior</a></li>';
                    
                    for ($cont = 1; $cont<= ($paginas-1); $cont++){
                        echo '<li class="selected"><a href="index.php?pagina='.$cont.'&lista_words='.$searchWords.'">'.$cont.'</a></li>';
                    }
                    
                    echo '<li class="selected">'.$paginas.'</li>
                          <li class="selected">Siguiente</li>';    
                    
                    break;
                    
                default:
                    echo '<li class="selected"><a href="index.php?pagina='.($pagina-1).'&lista_words='.$searchWords.'">Anterior</a></li>';
                    
                    for ($cont = 1; $cont<= $paginas; $cont++){
                        if ($cont==$pagina) {
                            echo '<li class="selected">'.$cont.'</li>';
                        } else {
                            echo '<li class="selected"><a href="index.php?pagina='.$cont.'&lista_words='.$searchWords.'">'.$cont.'</a></li>';
                        }
                        
                    }
                    
                    echo '<li class="selected"><a href="index.php?pagina='.($pagina+1).'&lista_words='.$searchWords.'">Siguiente</a></li>';
                    break;
            } 
            echo '</ul>';
        } else {
            $error = 3;
            echo $errormsg = "<b>No results found</b>";
            
        }
        
        
    }
    
    function getNumTuplas($array_words, $dbc){
        $likes = array();
        foreach($array_words as $word){
            $likes[] = "`description` LIKE '%".$word."%'";
        }
        $clausulaLike = implode(' OR ', $likes);
        $sql= "SELECT * FROM `riskyjobs` WHERE ".$clausulaLike.";";
        $resultset = $dbc->getQuery($sql);
        $num_tuplas = $resultset->rowCount();
        return $num_tuplas;
    }
    
    
    ?>
    
    <header>
        <form action="<?php echo $_SERVER['PHP_SELF']?>" enctype="" method="post">
            <div class="logo">Risky Jobs</div>
            <label for="search">Search:</label>
            <input type="text" name="search"/>
            <input type="submit" name="submit" value="GO!"/>
        </form>
    </header>
    
    <?php
    
        if(isset($_GET['lista_words'])){
            
            // Extraemos de la url las palabras de busqueda
            $lista_row = preg_replace('/%20/',' ',$_GET['lista_words']);
            $lista_words = explode (' ', $lista_row);
            
            $dbc = new DBConnection($dbsettings);
                    if(!$dbc){
                            $error = 2;
                            $errormsg = "<b>Error connecting database</b>";
                            
                    } else {
                        
                        // Comprobar que hay resultados de la búsqueda
                        $num_tuplas = getNumTuplas($lista_words, $dbc);
                        searchDescriptionInDB($lista_words, $dbc, $num_tuplas);
                        $error = 0;
                    }
            
            
        } else {
    
            if(isset($_POST['submit'])){
                if(!empty($_POST['search'])){
                    $search = $_POST['search'];
                    $dbc = new DBConnection($dbsettings);
                    if(!$dbc){
                            $error = 2;
                            $errormsg = "<b>Error connecting database</b>";
                            
                    } else {
                        $num_tuplas = getNumTuplas(cleanSearchWords($search), $dbc);
                        searchDescriptionInDB(cleanSearchWords($search), $dbc, $num_tuplas);
                        
                        $error = 0;
                    }
                }
                
            }
        }
        
        require_once('footer.php');
    
?>