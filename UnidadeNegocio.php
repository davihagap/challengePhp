<?

class UnidadeNegocio{

    var $Cnpj;
    var $Nome;
    var $Notas;    

    function __construct ($cnpj, $nome){
        $this->Cnpj = $cnpj;
        $this->Nome = $nome;
    }

}



?>