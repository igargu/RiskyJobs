<?php

    class DBConnection{
        //Recibe un array con los settings de acceso a la BBDD
        protected $_config;
        //Conexión a la BBDD
        public $dbc;
        
        //Abre una nueva conexión con la BBDD
        public function __construct(array $config){
            //Asignamos los settings
            $this -> _config = $config;
            //Intentamos conseguir una instancia de la conexión
            $this -> getPDOConnection();
        }
        
        //Cierra la conexión la BBDD
        public function __destruct(){
            $this -> dbc = NULL;
        }
        
        //Obtenemos una conexión a la BBDD mediante una instancia de la clase PDO
        private function getPDOConnection(){
            //Comprobamos si la conexión ya ha sido establecida
            if($this->dbc == NULL){
                //Creamos la conexión. Primero formamos el dsn
              $dsn = "" .
                $this->_config['driver'] .
                ":host=" . $this->_config['host'] .
                ";dbname=" . $this->_config['dbname'];
                //Hacemos la  conexión persistente y activamos el lanzamiento de excepciones
                $options = array(
                    PDO::ATTR_PERSISTENT    => true,
                    PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
                );
                try{
                    
                    $this->dbc = new PDO($dsn, $this->_config['username'], $this->_config['password'], $options);
                    
                }catch(PDOException $e){
                    
                    echo __LINE__ . $e->getMessage();
                    
                }
            }
        }
        
        /*Function runQuery
        *Ejecuta una consulta de tipo INSERT, UPDATE o DELETE
        *@param sql string sentencia con la consulta sql tipo INSERT, UPDATE o DELETE
        *@return num_tuplas int número de tuplas afectadas por la consulta
        */
        public function runQuery($sql){ //getter para las consultas que devuelven un nº de filas afectadas
            try{
                
                $num_tuplas = $this -> dbc -> exec($sql);
                
            }catch(PDOException $e){
                
                echo __LINE__ . $e->getMessage();
                
            }
            
            return $num_tuplas;
            
        }
        
        /*Function getQuery
        *Ejecuta un consulta que devuelve un resultset
        *@param sql string sentencia con la consulta sql tipo SELECT
        *@return resultset array asociativo con las tuplas y campos devueltos por la consulta
        */
        public function getQuery($sql){ //getter para las consultas que devuelven un resultset
            try{
                
                $resultset = $this -> dbc -> query($sql);
                $resultset -> setFetchMode(PDO::FETCH_ASSOC);
                
            }catch(PDOException $e){
                
                echo __LINE__ . $e->getMessage();
                
            }
            
            return $resultset;
            
        }
        
    }

?>