<?php

namespace NFePHP\BPe;

/**
 * Classe a constru��o do xml do BPe modelo 63
 * Esta classe basica est� estruturada para montar XML do BPe para o
 * layout vers�o 1.00, os demais modelos ser�o derivados deste
 *
 * @category  API
 * @package   NFePHP\Bpe\
 * @copyright Copyright (c) 2019
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Anderson Minuto Consoni Vaz <anderson at wdhouse dot com dot br>
 * @link      http://github.com/nfephp-org/sped-bpe for the canonical source repository
 */

use stdClass;
use RuntimeException;
use InvalidArgumentException;
use DOMElement;
use DateTime;
use NFePHP\Common\Keys;
use NFePHP\Common\DOMImproved as Dom;
use NFePHP\Common\Strings;

class Make
{
    /**
     * @var array
     */
    public $erros = [];

    /**
     * versao
     * numero da vers�o do xml da CTe
     * @var string
     */
    public $versao = '1.00';
    /**
     * xml
     * String com o xml do documento fiscal montado
     * @var string
     */
    public $xml = '';
    /**
     * dom
     * Vari�vel onde ser� montado o xml do documento fiscal
     * @var \NFePHP\Common\Dom\Dom
     */
    public $dom;

    /**
     * tpAmb
     * tipo de ambiente
     * @var string
     */
    public $tpAmb = '2';

    /**
     * mod
     * modelo da BPe 63
     * @var integer
     */
    public $mod = 63;

    /**
     * Informa��es do BPe
     * @var \DOMNode
     */
    private $BPe = '';

    /**
     * Identifica��o do BPe
     * @var \DOMNode
     */
    private $infBPe = '';

    /**
     * Identifica��o do BPe
     * @var \DOMNode
     */
    private $ide = '';

    /**
     * Identifica��o do emitente do BPe
     * @var \DOMNode
     */
    private $emit = '';

    /**
     * Identifica��o do endere�o do emitente do BPe
     * @var \DOMNode
     */
    private $enderEmit = '';

    /**
     * Identifica��o do comprador
     * @var \DOMNode
     */
    private $comp = '';

    /**
     * Identifica��o do endere�o do comprador
     * @var \DOMNode
     */
    private $enderComp = '';

    /**
     * Identifica��o da agencia
     * @var \DOMNode
     */
    private $agencia = '';

    /**
     * Identifica��o do endere�o da agencia
     * @var \DOMNode
     */
    private $enderAgencia = '';

    /**
     * Informa��es substitui��o BPe
     * @var \DOMNode
     */
    private $infBPeSub = '';

    /**
     * Informa��es do detalhamento da passagem
     * @var \DOMNode
     */
    private $infPassagem = '';

    /**
     * Informa��es do passageiro
     * @var \DOMNode
     */
    private $infPassageiro = '';

    /**
     * Informa��es da viagem
     * @var \DOMNode
     */
    private $infViagem = '';

    /**
     * Informa��es da travessia
     * @var \DOMNode
     */
    private $infTravessia = '';

    /**
     * Informa��es dos valores do BPe
     * @var \DOMNode
     */
    private $infValorBPe = '';

    /**
     * Informa��es componentes dos valores do BPe
     * @var \DOMNode
     */
    private $infValorBPeComp = array();

    /**
     * Informa��es relativas a impostos
     * @var \DOMNode
     */
    private $imp = '';

    /**
     * Informa��es relativas ao ICMS
     * @var \DOMNode
     */
    private $impICMS = '';

    /**
     * Informações Adicionais
     * @var \DOMNode
     */
    private $infAdic = '';

    /**
     * Informa��es relativas a presta��o sujeito a tributacao normal do ICMS
     * @var \DOMNode
     */
    private $icms = '';

    /**
     * Informa��es relativas a presta��o sujeito a tributacao normal do ICMS
     * @var \DOMNode
     */
    private $chBPe = '';

    /**
     * Dados do pagamento
     * @var \DOMNode
     */
    private $pag = array();

    /**
     * Dados Autorizados para download do XML
     * @var \DOMNode
     */
    private $autXML = array();

    /**
     * Informa��es suplementares do BPe
     * @var \DOMNode
     */
    private $infBPeSupl = '';

    public function __construct()
    {
        $this->dom = new Dom('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = false;
    }

    /**
     * Returns xml string and assembly it is necessary
     * @return string
     */
    public function getXML()
    {
        if (empty($this->xml)) {
            $this->montaBPe();
        }
        return $this->xml;
    }

    public function taginfBPe($std)
    {
        $chave = preg_replace('/[^0-9]/', '', $std->Id);
        $this->infBPe = $this->dom->createElement('infBPe');
        $this->infBPe->setAttribute('Id', 'BPe' . $chave);
        $this->infBPe->setAttribute('versao', $std->versao);
        return $this->infBPe;
    }

    /**
     * Retorns the key number of BPe (44 digits)
     * @return string
     */
    public function getChave()
    {
        return $this->chBPe;
    }


    public function tagide($std)
    {
        $this->tpAmb = $std->tpAmb;
        $this->mod = $std->mod;
        $identificador = '#4 <ide> - ';
        $this->ide = $this->dom->createElement('ide');
        $this->dom->addChild(
            $this->ide,
            'cUF',
            $std->cUF,
            true,
            $identificador . 'C�digo da UF do emitente do CT-e'
        );
        $this->dom->addChild(
            $this->ide,
            'tpAmb',
            $std->tpAmb,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'mod',
            $std->mod,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'serie',
            $std->serie,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'nBP',
            $std->nBP,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'cBP',
            str_pad($std->cBP, 8, '0', STR_PAD_LEFT),
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'cDV',
            $std->cDV,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'modal',
            $std->modal,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'dhEmi',
            $std->dhEmi,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'tpEmis',
            $std->tpEmis,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'verProc',
            $std->verProc,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'tpBPe',
            $std->tpBPe,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'indPres',
            $std->indPres,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'UFIni',
            $std->UFIni,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'cMunIni',
            $std->cMunIni,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'UFFim',
            $std->UFFim,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'cMunFim',
            $std->cMunFim,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->ide,
            'dhCont',
            $std->dhCont,
            false,
            $identificador . 'Data e Hora da entrada em conting�ncia'
        );
        $this->dom->addChild(
            $this->ide,
            'xJust',
            Strings::replaceSpecialsChars(substr(trim($std->xJust), 0, 256)),
            false,
            $identificador . 'Justificativa da entrada em conting�ncia'
        );
        return $this->ide;
    }

    /**
     * @param $std
     * @return DOMElement|\DOMNode
     */
    public function tagemit($std)
    {
        $identificador = '#3 <emit> - ';
        $this->emit = $this->dom->createElement('emit');
        $this->dom->addChild(
            $this->emit,
            'CNPJ',
            $std->CNPJ,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->emit,
            'IE',
            Strings::onlyNumbers($std->IE),
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->emit,
            'IEST',
            $std->IEST ? Strings::onlyNumbers($std->IEST) : null,
            false,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->emit,
            'xNome',
            $std->xNome,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->emit,
            'xFant',
            $std->xFant,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->emit,
            'IM',
            $std->IM,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->emit,
            'CNAE',
            $std->CNAE,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->emit,
            'CRT',
            $std->CRT,
            true,
            $identificador . ''
        );
        $this->TAR = $std->TAR;
        //        if (isset($std->TAR)) {
        //            $this->dom->addChild(
        //                $this->emit,
        //                'TAR',
        //                $std->TAR,
        //                true,
        //                $identificador . ''
        //            );
        //        }
        return $this->emit;
    }

    /**
     * @param $std
     * @return DOMElement
     */
    public function tagenderEmit($std)
    {
        $identificador = '#33 <enderEmit> - ';
        $this->enderEmit = $this->dom->createElement('enderEmit');
        $this->dom->addChild(
            $this->enderEmit,
            'xLgr',
            $std->xLgr,
            true,
            $identificador . 'Logradouro'
        );
        $this->dom->addChild(
            $this->enderEmit,
            'nro',
            $std->nro,
            true,
            $identificador . 'N�mero'
        );
        $this->dom->addChild(
            $this->enderEmit,
            'xCpl',
            $std->xCpl,
            false,
            $identificador . 'Complemento'
        );
        $this->dom->addChild(
            $this->enderEmit,
            'xBairro',
            $std->xBairro,
            true,
            $identificador . 'Bairro'
        );
        $this->dom->addChild(
            $this->enderEmit,
            'cMun',
            $std->cMun,
            true,
            $identificador . 'C�digo do munic�pio'
        );
        $this->dom->addChild(
            $this->enderEmit,
            'xMun',
            $std->xMun,
            true,
            $identificador . 'Nome do munic�pio'
        );
        $this->dom->addChild(
            $this->enderEmit,
            'CEP',
            $std->CEP,
            false,
            $identificador . 'CEP'
        );
        $this->dom->addChild(
            $this->enderEmit,
            'UF',
            $std->UF,
            true,
            $identificador . 'Sigla da UF'
        );
        if (isset($std->fone)) {
            $this->dom->addChild(
                $this->enderEmit,
                'fone',
                $std->fone,
                false,
                $identificador . 'Telefone'
            );
        }
        $this->dom->addChild(
            $this->enderEmit,
            'email',
            $std->email,
            false,
            $identificador . 'Endereço de E-mail'
        );
        return $this->enderEmit;
    }

    /**
     * @param $std
     * @return DOMElement|\DOMNode
     */
    public function tagcomp($std)
    {
        $identificador = '#45 <comp> - ';
        $this->comp = $this->dom->createElement('comp');
        $this->dom->addChild(
            $this->comp,
            'xNome',
            $std->xNome,
            true,
            $identificador . ''
        );
        if (isset($std->CPF)) {
            $this->dom->addChild(
                $this->comp,
                'CPF',
                $std->CPF,
                true,
                $identificador . ''
            );
        } else if (isset($std->idEstrangeiro)) {
            $this->dom->addChild(
                $this->comp,
                'idEstrangeiro',
                $std->idEstrangeiro,
                true,
                $identificador . ''
            );
        } else {
            $this->dom->addChild(
                $this->comp,
                'CNPJ',
                $std->CNPJ,
                true,
                $identificador . ''
            );
        }
        $this->dom->addChild(
            $this->comp,
            'IE',
            $std->IE,
            false,
            $identificador . ''
        );
        return $this->comp;
    }

    /**
     * @param $std
     * @return DOMElement
     */
    public function tagenderComp($std)
    {
        $identificador = '#51 <enderComp> - ';
        $this->enderComp = $this->dom->createElement('enderComp');
        $this->dom->addChild(
            $this->enderComp,
            'xLgr',
            $std->xLgr,
            true,
            $identificador . 'Logradouro'
        );
        $this->dom->addChild(
            $this->enderComp,
            'nro',
            $std->nro,
            true,
            $identificador . 'N�mero'
        );
        $this->dom->addChild(
            $this->enderComp,
            'xCpl',
            $std->xCpl,
            false,
            $identificador . 'Complemento'
        );
        $this->dom->addChild(
            $this->enderComp,
            'xBairro',
            $std->xBairro,
            true,
            $identificador . 'Bairro'
        );
        $this->dom->addChild(
            $this->enderComp,
            'cMun',
            $std->cMun,
            true,
            $identificador . 'C�digo do munic�pio'
        );
        $this->dom->addChild(
            $this->enderComp,
            'xMun',
            $std->xMun,
            true,
            $identificador . 'Nome do munic�pio'
        );
        $this->dom->addChild(
            $this->enderComp,
            'CEP',
            $std->CEP,
            false,
            $identificador . 'CEP'
        );
        $this->dom->addChild(
            $this->enderComp,
            'UF',
            $std->UF,
            true,
            $identificador . 'Sigla da UF'
        );
        $this->dom->addChild(
            $this->enderComp,
            'cPais',
            $std->cPais,
            true,
            $identificador . 'Codigo do Pais'
        );
        $this->dom->addChild(
            $this->enderComp,
            'xPais',
            $std->xPais,
            true,
            $identificador . 'Nome do Pais'
        );
        if (isset($std->fone)) {
            $this->dom->addChild(
                $this->enderComp,
                'fone',
                $std->fone,
                false,
                $identificador . 'Telefone'
            );
        }
        $this->dom->addChild(
            $this->enderComp,
            'email',
            $std->email,
            false,
            $identificador . 'Email'
        );
        return $this->enderComp;
    }

    /**
     * @param $std
     * @return DOMElement|\DOMNode
     */
    public function tagagencia($std)
    {
        $identificador = '#64 <agencia> - ';
        $this->agencia = $this->dom->createElement('agencia');
        $this->dom->addChild(
            $this->agencia,
            'xNome',
            $std->xNome,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->agencia,
            'CNPJ',
            $std->CNPJ,
            true,
            $identificador . ''
        );
        return $this->agencia;
    }

    /**
     * @param $std
     * @return DOMElement
     */
    public function tagenderAgencia($std)
    {
        $identificador = '#67 <enderAgencia> - ';
        $this->enderAgencia = $this->dom->createElement('enderAgencia');
        $this->dom->addChild(
            $this->enderAgencia,
            'xLgr',
            $std->xLgr,
            true,
            $identificador . 'Logradouro'
        );
        $this->dom->addChild(
            $this->enderAgencia,
            'nro',
            $std->nro,
            true,
            $identificador . 'N�mero'
        );
        $this->dom->addChild(
            $this->enderAgencia,
            'xCpl',
            $std->xCpl,
            false,
            $identificador . 'Complemento'
        );
        $this->dom->addChild(
            $this->enderAgencia,
            'xBairro',
            $std->xBairro,
            true,
            $identificador . 'Bairro'
        );
        $this->dom->addChild(
            $this->enderAgencia,
            'cMun',
            $std->cMun,
            true,
            $identificador . 'C�digo do munic�pio'
        );
        $this->dom->addChild(
            $this->enderAgencia,
            'xMun',
            $std->xMun,
            true,
            $identificador . 'Nome do munic�pio'
        );
        $this->dom->addChild(
            $this->enderAgencia,
            'CEP',
            $std->CEP,
            false,
            $identificador . 'CEP'
        );
        $this->dom->addChild(
            $this->enderAgencia,
            'UF',
            $std->UF,
            true,
            $identificador . 'Sigla da UF'
        );
        $this->dom->addChild(
            $this->enderAgencia,
            'cPais',
            $std->cPais,
            true,
            $identificador . 'Codigo do Pais'
        );
        $this->dom->addChild(
            $this->enderAgencia,
            'xPais',
            $std->xPais,
            true,
            $identificador . 'Nome do Pais'
        );
        if (isset($std->fone)) {
            $this->dom->addChild(
                $this->enderAgencia,
                'fone',
                $std->fone,
                false,
                $identificador . 'Telefone'
            );
        }
        $this->dom->addChild(
            $this->enderAgencia,
            'email',
            $std->email,
            false,
            $identificador . 'Email'
        );
        return $this->enderAgencia;
    }

    /**
     * @param $std
     * @return DOMElement
     */
    public function taginfBPeSub($std)
    {
        $identificador = '#80 <infBPeSub> - ';
        $this->infBPeSub = $this->dom->createElement('infBPeSub');
        $this->dom->addChild(
            $this->infBPeSub,
            'chBPe',
            $std->chBPe,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infBPeSub,
            'tpSub',
            $std->tpSub,
            true,
            $identificador . ''
        );
        return $this->infBPeSub;
    }

    /**
     * @param $std
     * @return DOMElement
     */
    public function taginfPassagem($std)
    {
        $identificador = '#83 <infPassagem> - ';
        $this->infPassagem = $this->dom->createElement('infPassagem');
        $this->dom->addChild(
            $this->infPassagem,
            'cLocOrig',
            $std->cLocOrig,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infPassagem,
            'xLocOrig',
            $std->xLocOrig,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infPassagem,
            'cLocDest',
            $std->cLocDest,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infPassagem,
            'xLocDest',
            $std->xLocDest,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infPassagem,
            'dhEmb',
            $std->dhEmb,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infPassagem,
            'dhValidade',
            $std->dhValidade,
            true,
            $identificador . ''
        );
        return $this->infPassagem;
    }
    /**
     * @param $std
     * @return DOMElement
     */
    public function taginfPassageiro($std)
    {
        $identificador = '#90 <infPassageiro> - ';
        $this->infPassageiro = $this->dom->createElement('infPassageiro');
        $this->dom->addChild(
            $this->infPassageiro,
            'xNome',
            $std->xNome,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infPassageiro,
            'CPF',
            $std->CPF,
            false,
            $identificador . ''
        );
        if (isset($std->tpDoc)) {
            $this->dom->addChild(
                $this->infPassageiro,
                'tpDoc',
                $std->tpDoc,
                true,
                $identificador . ''
            );
            $this->dom->addChild(
                $this->infPassageiro,
                'nDoc',
                $std->nDoc,
                true,
                $identificador . ''
            );
            if (isset($std->xDoc)) {
                $this->dom->addChild(
                    $this->infPassageiro,
                    'xDoc',
                    $std->xDoc,
                    false,
                    $identificador . ''
                );
            }
        }
        if (isset($std->dNasc)) {
            $this->dom->addChild(
                $this->infPassageiro,
                'dNasc',
                $std->dNasc,
                false,
                $identificador . ''
            );
        }
        if (isset($std->fone)) {
            $this->dom->addChild(
                $this->infPassageiro,
                'fone',
                $std->fone,
                false,
                $identificador . ''
            );
        }
        if (isset($std->email)) {
            $this->dom->addChild(
                $this->infPassageiro,
                'email',
                $std->email,
                false,
                $identificador . ''
            );
        }
        return $this->infPassageiro;
    }

    /**
     * @param $std
     * @return DOMElement
     */
    public function taginfViagem($std)
    {
        $identificador = '#99 <infViagem> - ';
        $this->infViagem = $this->dom->createElement('infViagem');
        $this->dom->addChild(
            $this->infViagem,
            'cPercurso',
            $std->cPercurso,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infViagem,
            'xPercurso',
            $std->xPercurso,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infViagem,
            'tpViagem',
            $std->tpViagem,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infViagem,
            'tpServ',
            $std->tpServ,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infViagem,
            'tpAcomodacao',
            $std->tpAcomodacao,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infViagem,
            'tpTrecho',
            $std->tpTrecho,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infViagem,
            'dhViagem',
            $std->dhViagem,
            true,
            $identificador . ''
        );
        if (isset($std->dhConexao)) {
            $this->dom->addChild(
                $this->infViagem,
                'dhConexao',
                $std->dhConexao,
                true,
                $identificador . ''
            );
        }
        if (isset($std->prefixo)) {
            $this->dom->addChild(
                $this->infViagem,
                'prefixo',
                $std->prefixo,
                true,
                $identificador . ''
            );
        }
        if (isset($std->poltrona)) {
            $this->dom->addChild(
                $this->infViagem,
                'poltrona',
                $std->poltrona,
                true,
                $identificador . ''
            );
        }
        if (isset($std->plataforma)) {
            $this->dom->addChild(
                $this->infViagem,
                'plataforma',
                $std->plataforma,
                true,
                $identificador . ''
            );
        }
        return $this->infViagem;
    }

    /**
     * @param $std
     * @return DOMElement
     */
    public function taginfTravessia($std)
    {
        $identificador = '#188 <infTravessia> - ';
        $this->infTravessia = $this->dom->createElement('infTravessia');
        $this->dom->addChild(
            $this->infTravessia,
            'tpVeiculo',
            $std->tpVeiculo,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infTravessia,
            'sitVeiculo',
            $std->sitVeiculo,
            true,
            $identificador . ''
        );
        return $this->infTravessia;
    }


    /**
     * @param $std
     * @return DOMElement
     */
    public function taginfValorBPe($std)
    {
        $identificador = '#114 <infValorBPe> - ';
        $this->infValorBPe = $this->dom->createElement('infValorBPe');
        $this->dom->addChild(
            $this->infValorBPe,
            'vBP',
            $std->vBP,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infValorBPe,
            'vDesconto',
            $std->vDesconto,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infValorBPe,
            'vPgto',
            $std->vPgto,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $this->infValorBPe,
            'vTroco',
            $std->vTroco,
            true,
            $identificador . ''
        );
        if (isset($std->tpDesconto)) {
            $this->dom->addChild(
                $this->infValorBPe,
                'tpDesconto',
                $std->tpDesconto,
                true,
                $identificador . ''
            );
            $this->dom->addChild(
                $this->infValorBPe,
                'xDesconto',
                $std->xDesconto,
                true,
                $identificador . ''
            );
            if (isset($std->cDesconto)) {
                $this->dom->addChild(
                    $this->infValorBPe,
                    'cDesconto',
                    $std->cDesconto,
                    true,
                    $identificador . ''
                );
            }
        }
        return $this->infValorBPe;
    }

    /**
     * @param $std
     * @return \DOMNode
     */
    public function taginfValorBPeComp($std)
    {
        $identificador = '#122 <Comp> - ';
        $infValorBPeCompN = $this->dom->createElement('Comp');
        $this->dom->addChild(
            $infValorBPeCompN,
            'tpComp',
            $std->tpComp,
            true,
            $identificador . ''
        );
        $this->dom->addChild(
            $infValorBPeCompN,
            'vComp',
            $std->vComp,
            true,
            $identificador . ''
        );
        $this->infValorBPeComp[] = $infValorBPeCompN;
        return $this->infValorBPeComp;
    }

    /**
     * @param $std
     * @return DOMElement
     */
    public function tagimp($std)
    {
        $identificador = '#125 <imp> - ';
        $this->imp = $this->dom->createElement('imp');
        $this->impICMS = $this->dom->createElement('ICMS');

        if (isset($std->vTotTrib)) {
            $this->dom->addChild(
                $this->imp,
                'vTotTrib',
                $std->vTotTrib,
                true,
                $identificador . ''
            );
        }
        if (isset($std->infAdFisco)) {
            $this->dom->addChild(
                $this->imp,
                'infAdFisco',
                $std->infAdFisco,
                true,
                $identificador . ''
            );
        }
        return $this->imp;
    }

    /**
     * tagICMS
     * Informações relativas ao ICMS
     * #194
     *
     * @return DOMElement
     */
    public function tagicms($std)
    {
        $identificador = 'N01 <ICMSxx> - ';
        switch ($std->CST) {
            case '00':
                $icms = $this->dom->createElement("ICMS00");
                $this->dom->addChild($icms, 'CST', $std->CST, true, "$identificador  Tributação do ICMS = 00");
                $this->dom->addChild($icms, 'vBC', $std->vBC, true, "$identificador  Valor da BC do ICMS");
                $this->dom->addChild($icms, 'pICMS', $std->pICMS, true, "$identificador  Alíquota do imposto");
                $this->dom->addChild($icms, 'vICMS', $std->vICMS, true, "$identificador  Valor do ICMS");
                break;
            case '20':
                $icms = $this->dom->createElement("ICMS20");
                $this->dom->addChild($icms, 'CST', $std->CST, true, "$identificador  Tributação do ICMS = 20");
                $this->dom->addChild(
                    $icms,
                    'pRedBC',
                    $std->pRedBC,
                    true,
                    "$identificador  Percentual da Redução de BC"
                );
                $this->dom->addChild($icms, 'vBC', $std->vBC, true, "$identificador  Valor da BC do ICMS");
                $this->dom->addChild($icms, 'pICMS', $std->pICMS, true, "$identificador  Alíquota do imposto");
                $this->dom->addChild($icms, 'vICMS', $std->vICMS, true, "$identificador  Valor do ICMS");
                break;
            case '40':
                $icms = $this->dom->createElement("ICMS45");
                $this->dom->addChild($icms, 'CST', $std->CST, true, "$identificador  Tributação do ICMS = 40");
                break;
            case '41':
                $icms = $this->dom->createElement("ICMS45");
                $this->dom->addChild($icms, 'CST', $std->CST, true, "$identificador  Tributação do ICMS = 41");
                break;
            case '51':
                $icms = $this->dom->createElement("ICMS45");
                $this->dom->addChild($icms, 'CST', $std->CST, true, "$identificador  Tributação do ICMS = 51");
                break;
            case '90':
                $icms = $this->dom->createElement("ICMS90");
                $this->dom->addChild($icms, 'CST', $std->CST, true, "$identificador  Tributação do ICMS = 90");
                $this->dom->addChild(
                    $icms,
                    'pRedBC',
                    $std->pRedBC,
                    false,
                    "$identificador  Percentual da Redução de BC"
                );
                $this->dom->addChild($icms, 'vBC', $std->vBC, true, "$identificador  Valor da BC do ICMS");
                $this->dom->addChild($icms, 'pICMS', $std->pICMS, true, "$identificador  Alíquota do imposto");
                $this->dom->addChild($icms, 'vICMS', $std->vICMS, true, "$identificador  Valor do ICMS");
                $this->dom->addChild($icms, 'vCred', $std->vCred, false, "$identificador Valor do Crédito Outorgado/Presumido");
                break;
            case 'SN':
                $icms = $this->dom->createElement("ICMSSN");
                $this->dom->addChild($icms, 'CST', 90, true, "$identificador Tributação do ICMS = 90");
                $this->dom->addChild($icms, 'indSN', '1', true, "$identificador  Indica se contribuinte é SN");
                break;
        }
        $this->icms = $icms;
        return $this->icms;
    }



    /**
     * tagautXML
     * tag Bpe/infMDFe/autXML
     *
     * Autorizados para download do XML do MDF-e
     *
     * @param string $cnpj
     * @param string $cpf
     *
     * @return DOMElement
     */
    public function tagautXML(stdClass $std)
    {
        $identificador = '#175 <autXML> - ';
        $autXML = $this->dom->createElement("autXML");
        $this->dom->addChild(
            $autXML,
            "CNPJ",
            $std->CNPJ ?? null,
            false,
            $identificador . "CNPJ do autorizado"
        );
        $this->dom->addChild(
            $autXML,
            "CPF",
            $std->CPF ?? null,
            false,
            $identificador . "CPF do autorizado"
        );
        $this->autXML[] = $autXML;
        return $this->autXML;
    }

    /**
     * taginfAdic
     * Grupo de Informações Adicionais
     * tag MDFe/infMDFe/infAdic (opcional)
     *
     * @param  string $infAdFisco
     * @param  string $infCpl
     *
     * @return DOMElement
     */
    public function taginfAdic(stdClass $std)
    {
        $identificador = '#178 <infAdic> - ';
        $infAdic = $this->dom->createElement("infAdic");
        $this->dom->addChild(
            $infAdic,
            "infAdFisco",
            $std->infAdFisco,
            false,
            $identificador . "Informações Adicionais de Interesse do Fisco"
        );
        $this->dom->addChild(
            $infAdic,
            "infCpl",
            $std->infCpl,
            false,
            $identificador . "Informações Complementares de interesse do Contribuinte"
        );
        $this->infAdic = $infAdic;
        return $infAdic;
    }

    /**
     * @param $std
     * @return DOMElement
     */
    public function tagpag($std)
    {
        $identificador = '#160 <pag> - ';
        $pag = $this->dom->createElement('pag');
        $this->dom->addChild(
            $pag,
            'tPag',
            $std->tPag,
            true,
            $identificador . 'Forma de Pagamento'
        );
        $this->dom->addChild(
            $pag,
            'xPag',
            $std->xPag,
            false,
            $identificador . 'Descrição da forma de pagamento 99 - Outros'
        );
        $this->dom->addChild(
            $pag,
            'nDocPag',
            $std->nDocPag,
            false,
            $identificador . 'Número do documento ou carteira apresentada nas formas de pagamento diferentes de 03 e 04'
        );
        $this->dom->addChild(
            $pag,
            'vPag',
            $std->vPag,
            true,
            $identificador . 'Valor do Pagamento'
        );
        if (isset($std->card)) {
            $identificadorCard = '[2] <card> - ';
            $card = $this->dom->createElement("card");
            $stdCard = $std->card;
            $this->dom->addChild(
                $card,
                "tpIntegra",
                $stdCard->tpIntegra,
                true,
                $identificadorCard . "Tipo de Integração do processo de pagamento"
            );
            $this->dom->addChild(
                $card,
                "CNPJ",
                $stdCard->CNPJ ?? null,
                false,
                $identificadorCard . "CNPJ da credenciadora de cartão de crédito/débito"
            );
            $this->dom->addChild(
                $card,
                "tBand",
                $stdCard->tBand ?? null,
                false,
                $identificadorCard . "Bandeira da operadora de cartão de crédito/débito"
            );
            $this->dom->addChild(
                $card,
                "xBand",
                $stdCard->xBand ?? null,
                false,
                $identificadorCard . "Descrição da operadora de cartão para 99 - Outros"
            );
            $this->dom->addChild(
                $card,
                "cAut",
                $stdCard->cAut ?? null,
                false,
                $identificadorCard . "Número de autorização da operação"
            );
            $this->dom->addChild(
                $card,
                "nsuTrans",
                $stdCard->nsuTrans ?? null,
                false,
                $identificadorCard . "Número sequencial único da transação"
            );
            $this->dom->addChild(
                $card,
                "nsuHost",
                $stdCard->nsuHost ?? null,
                false,
                $identificadorCard . "Número sequencial único do Host"
            );
            $this->dom->addChild(
                $card,
                "nParcelas",
                $stdCard->nParcelas ? str_pad($stdCard->nParcelas, 3, '0', STR_PAD_LEFT) : null,
                false,
                $identificadorCard . "Número de parcelas"
            );
            $this->dom->addChild(
                $card,
                "infAdCard",
                $stdCard->infAdCard ?? null,
                false,
                $identificadorCard . "Informações adicionais operacionais para integração do cartão de crédito"
            );
            $this->dom->appChild($pag, $card, 'Falta tag "card"');
        }
        return $this->pag[] = $pag;
    }

    /**
     * @param $std
     * @return DOMElement
     */
    public function taginfBPeSupl($std)
    {
        $this->infBPeSupl = $this->dom->createElement('infBPeSupl');
        $cdata = $this->dom->createCDATASection($std->qrCodBPe);
        $qrCodBPe = $this->dom->createElement('qrCodBPe');
        $qrCodBPe->appendChild($cdata);
        $this->infBPeSupl->appendChild($qrCodBPe);
        return $this->infBPeSupl;
    }

    /**
     * Tag raiz do documento xml
     * Fun��o chamada pelo m�todo [ monta ]
     * @return \DOMElement
     */
    private function buildBPe()
    {
        if (empty($this->BPe)) {
            $this->BPe = $this->dom->createElement('BPe');
            $this->BPe->setAttribute('xmlns', 'http://www.portalfiscal.inf.br/bpe');
        }
        return $this->BPe;
    }

    public function montaBPe()
    {
        if (count($this->erros) > 0) {
            return false;
        }
        $this->buildBPe();
        $this->dom->appChild($this->infBPe, $this->ide, 'Falta tag "infBPe"');
        $this->dom->appChild($this->emit, $this->enderEmit, 'Falta tag "emit"');
        $this->dom->addChild(
            $this->emit,
            'TAR',
            $this->TAR,
            false,
            'TAR'
        );
        $this->dom->appChild($this->infBPe, $this->emit, 'Falta tag "infCte"');

        if ($this->comp != '') {
            if ($this->enderComp != '') {
                $this->dom->appChild($this->comp, $this->enderComp, 'Falta tag "comp"');
            }
            $this->dom->appChild($this->infBPe, $this->comp, 'Falta tag "infCte"');
        }
        if ($this->agencia) {
            $this->dom->appChild($this->agencia, $this->enderAgencia, 'Falta tag "agencia"');
            $this->dom->appChild($this->infBPe, $this->agencia, 'Falta tag "infCte"');
        }
        if ($this->infBPeSub != '') {
            $this->dom->appChild($this->infBPe, $this->infBPeSub, 'Falta tag "infCte"');
        }
        if ($this->infPassageiro != '') {
            $this->dom->appChild($this->infPassagem, $this->infPassageiro, 'Falta tag "infPassagem"');
        }
        $this->dom->appChild($this->infBPe, $this->infPassagem, 'Falta tag "infCte"');
        if ($this->infTravessia != '') {
            $this->dom->appChild($this->infViagem, $this->infTravessia, 'Falta tag "infViagem"');
        }
        $this->dom->appChild($this->infBPe, $this->infViagem, 'Falta tag "infCte"');
        if (count($this->infValorBPeComp) > 0) {
            foreach ($this->infValorBPeComp as $key => $value) {
                $this->dom->appChild($this->infValorBPe, $value, 'Falta tag "infValorBPe"');
            }
        }
        if ($this->infValorBPe) {
            $this->dom->appChild($this->infBPe, $this->infValorBPe, 'Falta tag "infCte"');
        }
        if ($this->icms) {
            $this->dom->appChild($this->impICMS, $this->icms, 'Falta tag "impICMS"');
        }
        if ($this->imp) {
            $this->imp->insertBefore($this->impICMS, $this->imp->firstChild);
            $this->dom->appChild($this->infBPe, $this->imp, 'Falta tag "infCte"');
        }
        if ($this->pag) {
            $this->dom->addArrayChild($this->infBPe, $this->pag, 'Falta tag "infCte"');
        }
        if ($this->autXML) {
            $this->dom->addArrayChild($this->infBPe, $this->autXML, 'Falta tag "autXML"');
        }
        if ($this->infAdic) {
            $this->dom->appChild($this->infBPe, $this->infAdic, 'Falta tag "infAdic"');
        }
        //[1] tag infBPe
        $this->dom->appChild($this->BPe, $this->infBPe, 'Falta tag "BPe"');
        if ($this->infBPeSupl) {
            $this->dom->appChild($this->BPe, $this->infBPeSupl, 'Falta tag "BPe"');
        }
        //[0] tag BPe
        $this->dom->appendChild($this->BPe);
        $this->checkCTeKey($this->dom);
        $this->xml = $this->dom->saveXML();
        return true;
    }

    protected function checkCTeKey($dom)
    {
        $infCTe = $dom->getElementsByTagName("infBPe")->item(0);
        $ide = $dom->getElementsByTagName("ide")->item(0);
        $emit = $dom->getElementsByTagName("emit")->item(0);
        $cUF = $ide->getElementsByTagName('cUF')->item(0)->nodeValue;
        $dhEmi = $ide->getElementsByTagName('dhEmi')->item(0)->nodeValue;
        $cnpj = $emit->getElementsByTagName('CNPJ')->item(0)->nodeValue;
        $mod = $ide->getElementsByTagName('mod')->item(0)->nodeValue;
        $serie = $ide->getElementsByTagName('serie')->item(0)->nodeValue;
        $nNF = $ide->getElementsByTagName('nBP')->item(0)->nodeValue;
        $tpEmis = $ide->getElementsByTagName('tpEmis')->item(0)->nodeValue;
        $cCT = $ide->getElementsByTagName('cBP')->item(0)->nodeValue;
        $chave = str_replace('BPe', '', $infCTe->getAttribute("Id"));
        $dt = new DateTime($dhEmi);
        $chaveMontada = Keys::build(
            $cUF,
            $dt->format('y'),
            $dt->format('m'),
            $cnpj,
            $mod,
            $serie,
            $nNF,
            $tpEmis,
            $cCT
        );
        //caso a chave contida no BPe esteja errada
        //substituir a chave
        if ($chaveMontada != $chave) {
            $ide->getElementsByTagName('cDV')->item(0)->nodeValue = substr($chaveMontada, -1);
            $infBPe = $dom->getElementsByTagName("infBPe")->item(0);
            $infBPe->setAttribute("Id", "BPe" . $chaveMontada);
            $this->chBPe = $chaveMontada;
        }
    }
}
