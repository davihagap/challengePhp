<?php
    //Não foram encontradas divergências nos arquivos processados, ao menos não nos registros utilizados no desafio.
    //Entretanto se tornou necessária a utilização do registro A010 para saber a qual cnpj uma determinada nota pertence.

    require_once('Process.php');

    const PATTERN_CNPJ = '/[0-9]{2}[\.]?[0-9]{3}[\.]?[0-9]{3}[\/]?[0-9]{4}[-]?[0-9]{2}/';
    const PATTERN_DIREND = '/.*\/\//';

    if ($argc != 4){
        echo 'Argumentos invalidos...';
        exit;
    }

    $CNPJ = $argv[1];
    $PATH_ORIGIN = $argv[2];
    $PATH_DEST = $argv[3];

    //verificação de diretórios
    if (!file_exists($PATH_ORIGIN)){
        echo 'Diretorio de origem invalido...';
        exit;
    }

    //tratamento no nome dos diretorios
    if (!(preg_match(PATTERN_DIREND, $PATH_ORIGIN))){
        $PATH_ORIGIN .= '/';
    }
    if (!(preg_match(PATTERN_DIREND, $PATH_DEST))){
        $PATH_DEST .= '/';
    }

    //cria diretorios se nao existirem
    if (!(is_dir($PATH_DEST))){
        mkdir($PATH_DEST);
    }
    if (!(is_dir($PATH_DEST.$CNPJ))){
        mkdir($PATH_DEST.$CNPJ);
        mkdir($PATH_DEST.$CNPJ.'/efd-piscofins');
    }   

    //verificação de cnpj
    if (!(preg_match(PATTERN_CNPJ, $CNPJ))){
        echo 'Cnpj invalido...';
        exit;
    }
    
    $dados = Process::extrai_dados($PATH_ORIGIN);
    $saida = utf8_encode(json_encode(Process::formata_dados($dados, $CNPJ)));

    //criacao do arquivo de saida
    $f = fopen($PATH_DEST.$CNPJ.'/efd-piscofins/'.$CNPJ.'_compras_vendas.json', 'w');
    fwrite($f, $saida);
    fclose($f);
?>