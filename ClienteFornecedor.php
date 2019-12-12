<?

class ClienteFornecedor{

    var $Cnpj_Owner;
    var $Cod_Part;
    var $Nome;
    var $Cnpj;
    var $Cpf;    

    function __construct ($cnpj, $nome){
        $this->Cnpj = $cnpj;
        $this->Nome = $nome;
    }

}



?>