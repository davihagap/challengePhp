<?php

    class Process{

        public function get_dir_files($dir){
            return array_diff(scandir($dir), array('.', '..')); 
        }
    
        public function read_Unidade_Negocio($l){
            return array(
                'NOME' => $l[3],
                'CNPJ' => $l[4],
                'NOTAS' => array(),
                'CLI_FOR' => array(),
                'UNI_MED' => array(),
                'PROD_SERV' => array()
            );
        }
    
        public function read_Cliente_Fornecedor($l){
            return array(
                'COD_PART' => $l[2],
                'NOME' => $l[3],
                'CNPJ' => $l[5],
                'CPF' => $l[6]
            );
        }
    
        public function read_Unidade_Medida($l){
            return array(
                'UNID' => $l[2],
                'DESCR' => $l[3]
            );
        }
    
        public function read_Produto_Servico($l){
            return array(
                'COD_ITEM' => $l[2],
                'DESCR_ITEM' => $l[3],
                'UNID_INV' => $l[6],
                'TIPO_ITEM' => $l[7]
            );
        }
    
        public function read_Nota_Fiscal($l){
            return array(
                'IND_OPER' => $l[2],
                'COD_PART' => $l[4],
                'DT_DOC' => $l[10],
                'VL_DOC' => $l[12],
                'ITENS' => array()
            );
        }
    
        public function read_Item_Nota_Fiscal($l){
            return array(
                'NUM_ITEM' => $l[2],
                'COD_ITEM' => $l[3],
                'VL_ITEM' => $l[5]
            );
        }
    
        public static function formata_dados($unidades_negocio, $CNPJ){
            
            //formatacao dos dados para saida
            $un = $unidades_negocio[$CNPJ];      
    
            //formata as notas da unidade de negocio
            $notas = array();
            $notas['VENDAS'] = array();
            $notas['COMPRAS'] = array();
    
            foreach($un['NOTAS'] as $n){
    
                //recupera dados do participante e identifica se a nota e de compra ou venda
                $indice = $n['IND_OPER'] == 1 ? 'VENDAS' : 'COMPRAS';
                $atributo_doc = $n['IND_OPER'] == 1 ? 'DOC_CLIENTE' : 'CNPJ_FORNECEDOR';
                $participante_nome = '';
                $participante_doc = '';
                if($n['COD_PART'] != ''){
                    $participante_doc = $un['CLI_FOR'][$n['COD_PART']]['CPF'] == '' ? $un['CLI_FOR'][$n['COD_PART']]['CNPJ'] : $un['CLI_FOR'][$n['COD_PART']]['CPF'];
                    $participante_nome = $un['CLI_FOR'][$n['COD_PART']]['NOME'];
                }
    
                //formata os itens da nota
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
    
                //organiza notas por data
                $data = date_create_from_format('dmY', $n['DT_DOC']);
                if(!isset($notas[$indice][$data->format('Y/m')])){
                    $notas[$indice][$data->format('Y/m')] = array();
                }
    
                //popula notas final
                array_push($notas[$indice][$data->format('Y/m')], $nota_formatado);
            } 
    
            //popula objeto final
            $unidade_formatado = array(
                'CNPJ' => $un['CNPJ'],
                'NOME' => $un['NOME'],
                'NOTAS' => $notas
            );
            
    
            return $unidade_formatado;
    
        }
    
        public static function extrai_dados($origem){
    
            $unidades_negocio = array();
            $cnpj_atual = '';
    
            $paths = Process::get_dir_files($origem);    
            foreach($paths as $path){                
                //le o arquivo e percorre suas linhas
                $file = file($origem.$path);
                foreach($file as $reg){
                    //extrai os dados necessários
                    $line = explode('|', $reg);
                    switch ($line[1]) {
        
                        case '0140':
                            $un = Process::read_Unidade_Negocio($line);
                            if(!isset($unidades_negocio[$un['CNPJ']])){
                                $unidades_negocio[$un['CNPJ']] = $un;
                            }
                            $cnpj_atual = $un['CNPJ'];
                            break;
        
                        case '0150':
                            $cf = Process::read_Cliente_Fornecedor($line);
                            $unidades_negocio[$cnpj_atual]['CLI_FOR'][$cf['COD_PART']] = $cf;
                            break;
        
                        case '0190':
                            $un = Process::read_Unidade_Medida($line);
                            $unidades_negocio[$cnpj_atual]['UNI_MED'][$un['UNID']] = $un;
                            break;
        
                        case '0200':
                            $ps = Process::read_Produto_Servico($line);
                            $unidades_negocio[$cnpj_atual]['PROD_SERV'][$ps['COD_ITEM']]= $ps;
                            break;
        
                        case 'A010':
                            $cnpj_atual = $line[2];
                            break;
        
                        case 'A100':
                            $nf = Process::read_Nota_Fiscal($line);
                            array_push($unidades_negocio[$cnpj_atual]['NOTAS'], $nf);
                            break;
        
                        case 'A170':
                            $inf = Process::read_Item_Nota_Fiscal($line);
                            array_push($unidades_negocio[$cnpj_atual]['NOTAS'][count($unidades_negocio[$cnpj_atual]['NOTAS']) - 1]['ITENS'], $inf);
                            break;
                    }
                }
            }
    
            return $unidades_negocio;
        }

    }
?>