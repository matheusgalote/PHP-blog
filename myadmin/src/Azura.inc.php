<?php

class Azura {
    const USER = 'root';
    const PASS = 'eins';
    const PATH = 'http://localhost/blog';
    const DATABASE = 'blog';
    const HOST = 'localhost';

    private $connect;
    private $path;
    private $result;

    public function __construct() {
        try {
            $this->connect = new PDO('mysql:host='.self::HOST.';dbname='.self::DATABASE, self::USER, self::PASS);
            $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $e) {
            print "Error!: " . $e->getMessage();
        }
    }


    /**
     * EFETIVA AS TRASAÇÕES DE |INSERT|UPDATE|DELETE
     *
     * @param string $sql | QUERY SQL
     * @return int|string
     */
    public function openDatabase($sql) {
        $this->result = $this->connect->query($sql);

        if ($this->result) {
            return $this->result;
        } else {
            return "Erro na query: " . $this->result . "<br>";
        }
    }

    /**
     * SELECIONA OS DADOS NO BANCO |SELECT
     *
     * Use array foreach para percorrer os dados e forma de array associativo
     * e acesse os valores através das chaves. Ex: value['cd_usuario']
     * 
     * @param string $sql | QUERY SQL
     * @return array
     */
    public function openDatabaseSelect($sql) {
        $smtp = $this->connect->prepare($sql);
        $smtp->execute();

        $smtp->setFetchMode(PDO::FETCH_ASSOC);

        return $smtp->fetchAll();
    }

    /**
     * RETORNA O ÚLTIMO ID INSERIDO NA TABELA |SELECT
     *
     * @param string $table | TABELA DO BANCO
     * @param string $cd    | PRIMARY KEY DA TABELA
     * @return int
     */
    public function nextId($table) {
        $cd = 'cd_'.$table;

        $smtp = $this->connect->prepare("SELECT * FROM $table ORDER BY $cd DESC LIMIT 1");
        $smtp->execute();

        $smtp->setFetchMode(PDO::FETCH_ASSOC);
        $result = $smtp->fetchAll();

        if ($result) 
            foreach($result as $key=>$value) {
                return $value[$cd] + 1;            
            }
        else return 1;
    }

    /**
     * RETORNA O PATH DA APLICAÇÃO
     *
     * @return string | URL
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * RESPONSÁVEL POR POPULAR A CLASSE ATRAVÉS DO REQUEST
     *
     * @param string $class
     * @param array $request
     * @return object
     */
    public function populateClass($class, $request) {
        $instance = new $class();

        // MELHORAR ISSO, MAS POR ENQUANTO CONCATENA O MÉTODO DE CHAVE PRIMÁRIA COM O NOME DA CLASSE
        $set = 'setCd_'.$class;

        // SETA O PRÓXIMO ID DISPONÍVEL PARA INSERÇÃO
        $instance->$set($this->nextId($class));

        foreach($request as $key=>$value) {
            $method = 'set' . ucwords($key);

            // SE O MÉTODO EXISTIR, SETA O VALOR NO OBJETO
            if (method_exists($instance, $method)) {
                $instance->$method($value);
            }
        }

        return $instance;
    }
}

class Aedra extends Azura {

    /**
     * INSERE NA TABELA DE ACORDO COM O OBJETO POPULADO
     *
     * Popula a tabela de acordo com os atributos da classe 
     * 
     * @param object $obj
     * @return boolean
     */
    public function insert($obj) {
        $class = get_class($obj);
        
        $keys = [];
        $values = [];
        
        foreach($obj as $key=>$value) {
            array_push($keys, $key);
            array_push($values, "'" . $value . "'"); // INSERE ASPAS SIMPLES EM CADA VALOR
        }
        
        $sql = " INSERT INTO " . strtolower($class) ;
        $sql .= " ( " . implode(", ", $keys) . ")";
        $sql .= " VALUES (" .  implode(", ", $values) . ")";

        if ($this->openDatabase($sql)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ATUALIZA A TABELA DO OBJETO PASSADO COMO PARÂMETRO
     *
     * É necessário que exista um método de puxar o código como o nome da tabela
     * 
     * Ex: getCd_usuario -> Usuario->getCd_usuario()
     * 
     * @param object $obj
     * @return boolean
     */
    public function update($obj) {
        $class = strtolower(get_class($obj));
        
        $sql = "UPDATE $class SET ";

        // SE O OBJETO FOI MODIFICADO O INCLUI NA QUERY
        foreach($obj as $key=>$value) {
            if($value != '')
                $sql .= " $key='$value', ";
        }

        // REMOVE A ÚLTIMA VÍRGULA DA QUERY
        $sql = rtrim($sql, ", ");

        // CHECA SE O MÉTODO GET DA CHAVE PRIMARIA EXISTE, E PEGA SEU CÓDIGO
        if(method_exists($obj,'getCd_'. $class)) {
            $getCd = 'getCd_'.$class;
            $sql .= " WHERE cd_$class = {$obj->$getCd()} ";
        }

        if ($this->openDatabase($sql)) {
            return true;
        } else {
            return false;
        }
    }
}