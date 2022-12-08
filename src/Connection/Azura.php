<?php
namespace Connection;

use PDO;
use PDOException;

class Azura {
    const USER = 'root';
    const PASS = '';
    const PATH = 'http://localhost/blog';
    const DATABASE = 'blog';
    const HOST = 'localhost';

    protected $connect;
    protected $path;
    protected $result;

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
        return 1;
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
     * EXECUTA QUERY
     *
     * @param string $query
     * @param array $params
     * @return PDOStatement
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->connect->prepare($query);
            $stmt->execute($params);
            return $stmt;

        } catch(PDOException $e) {
            die('ERRO: '. $e->getMessage());
        }
    }

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

        $insertArrayValues = (array) $obj; // Transforma o objeto em array
        
        $values = array_values($insertArrayValues);
        $keys = array_keys($insertArrayValues); 
        $binds = array_pad([], count($insertArrayValues), '?'); // Monta os binds da query
        
        // Monta a query
        $sql = " INSERT INTO " . strtolower($class) ;
        $sql .= " ( " . implode(", ", $keys) . ")";
        $sql .= " VALUES (" .  implode(", ", $binds) . ")";

        $this->execute($sql, $values);

        return $this->connect->lastInsertId();
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

    /**
     * REMOVE O ELEMENTO DA TABELA
     *
     * @param object $obj
     * @return int|string
     */
    public function delete($obj) {
        $class = strtolower(get_class($obj));

        // A PRIMARY KEY DA TABELA
        $cd = 'cd_'.$class;

        // O GET DA PRIMARY KEY DA TABELA
        $get = 'get'.ucwords($cd);

        $sql = "DELETE FROM $class WHERE $cd = {$obj->$get()} ";

        return $this->openDatabase($sql);
    }

    /**
     * MÉTODO DE SELEÇÃO
     *
     * @param object $obj
     * @return array
     */
    public function select($obj) {
        $class = strtolower(get_class($obj));

        $sql = "SELECT * FROM $class ";

        return $this->openDatabaseSelect($sql);
    }

    /**
     * MÉTODO RESPONSÁVEL POR CARREGAR OS DADOS DO OBJETO PARA UPDATE
     *
     * @param object $obj
     * @return object
     */
    public function load($obj) {
        $class = strtolower(get_class($obj));

        // A PRIMARY KEY DA TABELA
        $cd = 'cd_'.$class;

        // O GET DA PRIMARY KEY DA TABELA
        $get = 'get'.ucwords($cd);

        $sql = "SELECT * FROM $class WHERE $cd = {$obj->$get()} ";

        $result = $this->openDatabaseSelect($sql);

        foreach($result as $res) {
            foreach($res as $key=>$value) {
                $set = 'set'.ucwords($key);
                $obj->$set($value);
            }
        }

        return $obj;
    }

    /**
     * MÉTODO MONTADOR DE TABELAS DO OBJETO
     *
     * Monta uma tabela html com os atributos do objeto buscados no banco.
     * 
     * @param object $obj
     * @return HTMLTable
     */
    public function list($obj) {
        $class = strtolower(get_class($obj));

        $list = $this->select($obj);

        // PEGA A ESTRUTURA DA TABELA 
        $sql = "SELECT * FROM $class LIMIT 1 ";

        // RETORNA AS COLUNAS COM OS VALORES DO PRIMEIRO ELEMENTO DA TABELA REFERENCIADA
        $columns = $this->openDatabaseSelect($sql);

        $table = '<table class="tabela">';
        $table .= '<thead>';
        $table .= '<tr>';
        $table .= '<td class="cel"></td>';
        $table .= '<td class="cel"></td>';

        // AS COLUNAS DA TABELA SEM OS VALORES ATRIBUTOS
        $keys = [];

        foreach($columns as $column) {
            foreach($column as $key=>$value) {
                $table .= '<td class="cel head-table">'. $key .'</td>';
                array_push($keys, $key);
            }
        }

        $table .= '</tr>';
        $table .= '</thead>';
        $table .= '<tbody>';

        foreach($list as $lst) {
            $table .= '<tr class="cel">';

            $table .= '<th class="cel"><a href=?e=D&cd=' . $lst[$keys[0]] . ' class=button><i class="fa-solid fa-circle-minus"></i></a></th>'; 
            $table .= '<th class="cel"><a href=?e=V&cd=' . $lst[$keys[0]] . ' class=button><i class="fa-solid fa-square-pen"></i></a></th>'; 

            // ITERA SOBRE AS KEYS E RETORNA OS VALORES ASSOCIADOS AS COLUNAS EM QUESTAO
            foreach($keys as $key) {
                $table .= '<th class="cel content-table">' . $lst[$key] . '</th>';
            }
            $table .= '<tr>';
        }


        $table .= '</table>';

        echo $table;
    }
}