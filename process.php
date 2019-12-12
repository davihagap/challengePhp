<?php

    function get_dir_files($dir){
        return array_diff(scandir($dir), array('.', '..')); 
    }

    function read_Unidade_Negocio($l){
        return array(
            'COD_EST' => $l[2],
            'NOME' => $l[3],
            'CNPJ' => $l[4],
            'UF' => $l[5],
            'IE' => $l[6],
            'COD_MUN' => $l[7],
            'IM' => $l[8],
            'SUFRAMA' => $l[9],
            'NOTAS' => array(),
            'CLI_FOR' => array(),
            'UNI_MED' => array(),
            'PROD_SERV' => array()
        );
    }

    function read_Cliente_Fornecedor($l){
        return array(
            'COD_PART' => $l[2],
            'NOME' => $l[3],
            'COD_PAIS' => $l[4],
            'CNPJ' => $l[5],
            'CPF' => $l[6],
            'IE' => $l[7],
            'COD_MUN' => $l[8],
            'SUFRAMA' => $l[9],
            'END' => $l[10],
            'NUM' => $l[11],
            'COMPL' => $l[12],
            'BAIRRO' => $l[13]
        );
    }

    function read_Unidade_Medida($l){
        return array(
            'UNID' => $l[2],
            'DESCR' => $l[3]
        );
    }

    function read_Produto_Servico($l){
        return array(
            'COD_ITEM' => $l[2],
            'DESCR_ITEM' => $l[3],
            'COD_BARRA' => $l[4],
            'COD_ANT_ITEM' => $l[5],
            'UNID_INV' => $l[6],
            'TIPO_ITEM' => $l[7],
            'COD_NCM' => $l[8],
            'EX_IPI' => $l[9],
            'COD_GEN' => $l[10],
            'COD_LST' => $l[11],
            'ALIQ_ICMS' => $l[12]
        );
    }

    function read_Nota_Fiscal($l){
        return array(
            'IND_OPER' => $l[2],
            'IND_EMIT' => $l[3],
            'COD_PART' => $l[4],
            'COD_SIT' => $l[5],
            'SER' => $l[6],
            'SUB' => $l[7],
            'NUM_DOC' => $l[8],
            'CHV_NFSE' => $l[9],
            'DT_DOC' => $l[10],
            'DT_EXE_SERV' => $l[11],
            'VL_DOC' => $l[12],
            'IND_PGTO' => $l[13],
            'VL_DESC' => $l[14],
            'VL_BC_PIS' => $l[15],
            'VL_PIS' => $l[16],
            'VL_BC_COFINS' => $l[17],
            'VL_COFINS' => $l[18],
            'VL_PIS_RET' => $l[19],
            'VL_COFINS_RET' => $l[20],
            'VL_ISS' => $l[21],
            'ITENS' => array()
        );
    }

    function read_Item_Nota_Fiscal($l){
        return array(
            'NUM_ITEM' => $l[2],
            'COD_ITEM' => $l[3],
            'DESCR_COMPL' => $l[4],
            'VL_ITEM' => $l[5],
            'VL_DESC' => $l[6],
            'NAT_BC_CRED' => $l[7],
            'IND_ORIG_CRED' => $l[8],
            'CST_PIS' => $l[9],
            'VL_BC_PIS' => $l[10],
            'ALIQ_PIS' => $l[11],
            'VL_PIS' => $l[12],
            'CST_COFINS' => $l[13],
            'VL_BC_COFINS' => $l[14],
            'ALIQ_COFINS' => $l[15],
            'VL_COFINS' => $l[16],
            'COD_CTA' => $l[17],
            'COD_CCUS' => $l[18]
        );
    }

    if ($argc != 4){
        echo 'Argumentos invalidos...';
        exit;
    }

    $PATTERN_CNPJ = '/[0-9]{2}[\.]?[0-9]{3}[\.]?[0-9]{3}[\/]?[0-9]{4}[-]?[0-9]{2}/';
    $PATTERN_ALLOWED_EXTENSIONS = '/.*\.txt/'; //para acrescentar extensoes substituir txt por (txt|html|...)
    $PATTERN_DIREND = '/.*\/\//';
    $CNPJ = $argv[1];
    $PATH_ORIGIN = $argv[2];
    $PATH_DEST = $argv[3];

    //verificação de diretórios
    if (!file_exists($PATH_ORIGIN)){
        echo 'Diretorio de origem invalido...';
        exit;
    }else{
        //se diretorio nao terminar com '/', acrescenta
        if (!(preg_match($PATTERN_DIREND, $PATH_ORIGIN))){
            $PATH_ORIGIN .= '/';
        }
        if (!(preg_match($PATTERN_DIREND, $PATH_DEST))){
            $PATH_DEST .= '/';
        }
        if (!(is_dir($PATH_DEST))){
            mkdir($PATH_DEST);
        }
    }

    //verificação de cnpj
    if (!(preg_match($PATTERN_CNPJ, $CNPJ))){
        echo 'Cnpj invalido...';
        exit;
    }

    $paths = get_dir_files($PATH_ORIGIN);

    //inicializa objetos necessarios
    $unidades_negocio = array();
    $cnpj_atual = '';

    foreach($paths as $path){
        //verifica extensao do arquivo
        if (!(preg_match($PATTERN_ALLOWED_EXTENSIONS, $path))){
            echo 'arquivo '.$path.' com extensao invalida';
            continue;
        }
        
        //le o arquivo e percorre cada um de suas linhas
        $file = file($PATH_ORIGIN.$path);
        foreach($file as $reg){
            //extrai os dados necessários
            $line = explode('|', $reg);
            switch ($line[1]) {

                case '0140':
                    $un = read_Unidade_Negocio($line);
                    if(!isset($unidades_negocio[$un['CNPJ']])){
                        $unidades_negocio[$un['CNPJ']] = $un;
                    }
                    $cnpj_atual = $un['CNPJ'];
                    break;

                case '0150':
                    $cf = read_Cliente_Fornecedor($line);
                    $unidades_negocio[$cnpj_atual]['CLI_FOR'][$cf['COD_PART']] = $cf;
                    break;

                case '0190':
                    $un = read_Unidade_Medida($line);
                    $unidades_negocio[$cnpj_atual]['UNI_MED'][$un['UNID']] = $un;
                    break;

                case '0200':
                    $ps = read_Produto_Servico($line);
                    $unidades_negocio[$cnpj_atual]['PROD_SERV'][$ps['COD_ITEM']]= $ps;
                    break;

                case 'A010':
                    $cnpj_atual = $line[2];
                    break;

                case 'A100':
                    $nf = read_Nota_Fiscal($line);
                    array_push($unidades_negocio[$cnpj_atual]['NOTAS'], $nf);
                    break;

                case 'A170':
                    $inf = read_Item_Nota_Fiscal($line);
                    array_push($unidades_negocio[$cnpj_atual]['NOTAS'][count($unidades_negocio[$cnpj_atual]['NOTAS']) - 1]['ITENS'], $inf);
                    break;
            }
        }
    }
    
    //formatacao dos dados para saida
    foreach($unidades_negocio as $un){
        if ($un['CNPJ'] != $CNPJ){
            continue;
        }
        if (!(is_dir($PATH_DEST.$un['CNPJ']))){
            mkdir($PATH_DEST.$un['CNPJ']);
            mkdir($PATH_DEST.$un['CNPJ'].'/efd-piscofins');
        }

        $notas = array();
        $notas['VENDAS'] = array();
        $notas['COMPRAS'] = array();

        foreach($un['NOTAS'] as $n){

            $indice = $n['IND_OPER'] == 1 ? 'VENDAS' : 'COMPRAS';
            $atributo_doc = $n['IND_OPER'] == 1 ? 'DOC_CLIENTE' : 'CNPJ_FORNECEDOR';
            $participante_nome = '';
            $participante_doc = '';
            if($n['COD_PART'] != ''){
                $participante_doc = $un['CLI_FOR'][$n['COD_PART']]['CPF'] == '' ? $un['CLI_FOR'][$n['COD_PART']]['CNPJ'] : $un['CLI_FOR'][$n['COD_PART']]['CPF'];
                $participante_nome = $un['CLI_FOR'][$n['COD_PART']]['NOME'];
            }

            $itens_formatado = array();
            foreach($n['ITENS'] as $i){
                $desc_item = $un['PROD_SERV'][$i['COD_ITEM']]['DESCR_ITEM'];
                $unid_item = $un['PROD_SERV'][$i['COD_ITEM']]['UNID_INV'] == '' ? '' : $un['UNI_MED'][$un['PROD_SERV'][$i['COD_ITEM']]['UNID_INV']]['UNID'];
                array_push($itens_formatado, array(
                    'DATA' => $n['DT_DOC'],
                    'CODIGO' => $i['COD_ITEM'],
                    'DESCRICAO' => $desc_item,
                    'UNIDADE' => $unid_item,
                    'TOTAL' => $i['VL_ITEM']
                ));
            }
            $nota_formatado = array(
                'DATA' => $n['DT_DOC'],
                $atributo_doc => $participante_doc,
                'RAZAO_SOCIAL' => $participante_nome,
                'VALOR_TOTAL' => $n['VL_DOC'],
                'ITENS' => $itens_formatado
                
            );

            $data = date_create_from_format('dmY', $n['DT_DOC']);
            if(!isset($notas[$indice][$data->format('Y/m')])){
                $notas[$indice][$data->format('Y/m')] = array();
            }
            array_push($notas[$indice][$data->format('Y/m')], $nota_formatado);
        } 

        $unidade_formatado = array(
            'CNPJ' => $un['CNPJ'],
            'NOME' => $un['NOME'],
            'NOTAS' => $notas
        );

        $saida = utf8_encode(json_encode($unidade_formatado));
        
        //criacao do arquivo de saida
        $f = fopen($PATH_DEST.$un['CNPJ'].'/efd-piscofins/'.$un['CNPJ'].'_compras_vendas.json', 'w');
        fwrite($f, $saida);
        fclose($f);
                

    }
    
?>