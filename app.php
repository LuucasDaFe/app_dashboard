<?
//class dashboard
class Dashboard{
    public $data_inicio;
    public $data_fim;
    public $numeroVendas;
    public $totalVendas;
    public function __get($atributo){
        return $this->$atributo;
    }
    public function __set($atributo, $valor){
        $this->$atributo = $valor;
        return $this;
    }
}

//classe de conexão bd
class Conexao{
    private $host = 'localhost';
    private $dbname = 'dashboard';
    private $user = 'root';
    private $pass = '';
    public function conectar(){
        try{
            $conexao = new PDO("mysql:host=$this->host;dbname=$this->dbname", "$this->user", "$this->pass");

            $conexao->exec('set charset utf8');

            return $conexao;
        } catch (PDOException $e){
            echo '<p>'.$e->getMessage().'<p>';
        }
    }
}

//classe (model)
class Bd{
    private $conexao;
    private $dashbard;
    public function __construct(Conexao $conexao, Dashboard $dashbard){
        $this->conexao = $conexao->conectar();
        $this->dashbard = $dashbard;
    }

    public function getNumeroVendas(){
        $query = '
        select 
            count(*) as numero_vendas 
        from 
            tb_vendas 
        where 
            data_venda 
        BETWEEN 
            :data_inicio and :data_fim';

        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':data_inicio', $this->dashbard->__get('data_inicio'));
        $stmt->bindValue(':data_fim', $this->dashbard->__get('data_fim'));
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->numero_vendas;
    }

    public function getTotalVendas(){
        $query = '
        select 
            SUM(total) as total_vendas 
        from 
            tb_vendas 
        where 
            data_venda 
        BETWEEN 
            :data_inicio and :data_fim';

        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':data_inicio', $this->dashbard->__get('data_inicio'));
        $stmt->bindValue(':data_fim', $this->dashbard->__get('data_fim'));
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->total_vendas;
    }
}

//lógica do script
$dashbard = new Dashboard();

$conexao = new Conexao();

$competencia = explode('-' ,$_GET['competencia']);
$ano = $competencia[0];
$mes = $competencia[1];

$dias_do_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

$dashbard->__set('data_inicio', $ano.'/'.$mes.'-01');
$dashbard->__set('data_fim', $ano.'-'.$mes.'-'.$dias_do_mes);

$bd = new Bd($conexao, $dashbard);

$dashbard->__set('numeroVendas', $bd->getNumeroVendas());
$dashbard->__set('totalVendas', $bd->getTotalVendas());
echo json_encode($dashbard);

//print_r();


?>