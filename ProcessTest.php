<?php
use PHPUnit\Framework\TestCase;

require_once('Process.php');

class ProcessTest extends TestCase
{
    public function testExtraiDados()
    {
        $result = Process::extrai_dados('./arquivos/');
        
        $quantNotas = 0;
        $quantCliFor = 0;
        $quantProdServ = 0;
        $quantItens = 0;
        $quantUniMed = 0;


        foreach($result as $un){
            $quantNotas += count($un['NOTAS']);            
            $quantCliFor += count($un['CLI_FOR']);
            $quantProdServ += count($un['PROD_SERV']);
            $quantUniMed += count($un['UNI_MED']);
            foreach($un['NOTAS'] as $i){
                $quantItens += count($i['ITENS']);
            }
        }

        $this->assertEquals(2, count($result));
        $this->assertEquals(9, $quantNotas);
        $this->assertEquals(12,$quantItens);
        $this->assertEquals(2, $quantCliFor);
        $this->assertEquals(7 , $quantProdServ);
        $this->assertEquals(6 , $quantUniMed);
    }

    public function testFormataDados()
    {
        //mock manual :(
        $un = array(
            '65445489446132' => array('CNPJ' => '65445489446132', 'NOME' => 'blablabla ltda'),
            '12345678955555' => array(
                'CNPJ' => '12345678955555', 
                'NOME' => 'blablabla2 ltda',
                'NOTAS' => array(
                    array(
                        'IND_OPER' => '1',
                        'COD_PART' => '',
                        'DT_DOC' => '01022019',
                        'VL_DOC' =>'4000',
                        'ITENS' => array(
                            array(
                                'NUM_ITEM' => '1',
                                'COD_ITEM' => 'p1',
                                'VL_ITEM' => '2000'
                            ),
                            array(
                                'NUM_ITEM' => '2',
                                'COD_ITEM' => 'p1',
                                'VL_ITEM' => '2000'
                            )
                        )
                    ),
                    array(
                        'IND_OPER' => '1',
                        'COD_PART' => '',
                        'DT_DOC' => '05022019',
                        'VL_DOC' =>'4500',
                        'ITENS' => array(
                            array(
                                'NUM_ITEM' => '1',
                                'COD_ITEM' => 'p1',
                                'VL_ITEM' => '4500'
                            )
                        )
                    ),
                    array(
                        'IND_OPER' => '0',
                        'COD_PART' => '3',
                        'DT_DOC' =>'01022019',
                        'VL_DOC' => '8000',
                        'ITENS' => array(
                            array(
                                'NUM_ITEM' => '1',
                                'COD_ITEM' => 'p1',
                                'VL_ITEM' => '8000'
                            )
                        )
                    )
                ),
                'CLI_FOR' => array(
                    '3' => array( 'COD_PART' => $l[2],
                    'NOME' => 'açougue joao',
                    'CNPJ' => '44444444444444',
                    'CPF' => ''
                    )
                ),
                'UNI_MED' => array(
                    'KG' => array(
                        'UNID' => 'KG',
                        'DESCR' => 'kilograma'
                    )
                ),
                'PROD_SERV' => array(
                    'p1' => array(
                        'COD_ITEM' => 'p1',
                        'DESCR_ITEM' => 'algum produto',
                        'UNID_INV' => 'KG',
                        'TIPO_ITEM' => 'laskd'
                    )
                )
            ),
        );

        $result = Process::formata_dados($un, '12345678955555');
        
        $quantNotas = count($result['NOTAS']['COMPRAS']['2019/02']) + count($result['NOTAS']['VENDAS']['2019/02']);
        $quantItens = 0;
        foreach($result['NOTAS']['COMPRAS']['2019/02'] as $n){
            $quantItens += count($n['ITENS']);
        }
        foreach($result['NOTAS']['VENDAS']['2019/02'] as $n){
            $quantItens += count($n['ITENS']);
        }

        $this->assertEquals(3, $quantNotas);
        $this->assertEquals(4, $quantItens);
    }
}
?>