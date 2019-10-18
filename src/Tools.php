<?php

namespace NFePHP\BPe;

/**
 * Class responsible for communication with SEFAZ extends
 * NFePHP\BPe\Common\Tools
 *
 * @category  NFePHP
 * @package   NFePHP\BPe\Tools
 * @copyright NFePHP Copyright (c) 2008-2017
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @author    Anderson Minuto Consoni Vaz <anderson at wdhouse dot com dot br>
 * @link      http://github.com/nfephp-org/sped-bpe for the canonical source repository
 */

use NFePHP\Common\Strings;
use NFePHP\Common\Signer;
use NFePHP\Common\UFList;
use NFePHP\BPe\Factories\Events;
use NFePHP\BPe\Common\Tools as ToolsCommon;
use RuntimeException;
use InvalidArgumentException;

class Tools extends ToolsCommon
{
    const EVT_CONFIRMACAO = 210200;
    const EVT_CIENCIA = 210210;
    const EVT_DESCONHECIMENTO = 210220;
    const EVT_NAO_REALIZADA = 210240;

    /**
     * Request authorization to issue BPe in batch with one or more documents
     * @param array $aXml array of bpe's xml
     * @param string $idLote lote number
     * @param bool $compactar flag to compress data with gzip
     * @return string soap response xml
     */
    public function sefazEnviaLote(
        $aXml
    ) {
        $servico = 'BPeRecepcao';
        $this->checkContingencyForWebServices($servico);
        if ($this->contingency->type != '') {
            throw new \Exception('Em contingencia');
        }
        $sxml = preg_replace("/<\?xml.*?\?>/", "", $aXml);
        $this->servico(
            $servico,
            $this->config->siglaUF,
            $this->tpAmb
        );
        $request = $sxml;
        $this->lastRequest = $request;
        $parameters = ['bpeDadosMsg' => $request];
        $request = base64_encode(gzencode($request, 9, FORCE_GZIP));
        $body = "<bpeDadosMsg xmlns=\"$this->urlNamespace\">$request</bpeDadosMsg>";
        $method = $this->urlMethod;
        $this->lastResponse = $this->sendRequest($body, $parameters);
        return $this->lastResponse;
    }

    /**
     * Check status of Batch of BPe sent by receipt of this shipment
     * @param string $recibo
     * @param int $tpAmb
     * @return string
     */
    public function sefazConsultaRecibo($recibo, $tpAmb = null)
    {
        if (empty($tpAmb)) {
            $tpAmb = $this->tpAmb;
        }
        //carrega servi�o
        $servico = 'BPeRetRecepcao';
        $this->checkContingencyForWebServices($servico);
        $this->servico(
            $servico,
            $this->config->siglaUF,
            $tpAmb
        );
        if ($this->urlService == '') {
            $msg = "A consulta de BPe n�o est� dispon�vel na SEFAZ {$this->config->siglaUF}!!!";
            throw new RuntimeException($msg);
        }
        $request = "<consReciBPe xmlns=\"$this->urlPortal\" versao=\"$this->urlVersion\">"
            . "<tpAmb>$tpAmb</tpAmb>"
            . "<nRec>$recibo</nRec>"
            . "</consReciBPe>";
        $this->isValid($this->urlVersion, $request, 'consReciBPe');
        $this->lastRequest = $request;
        $parameters = ['bpeDadosMsg' => $request];
        $body = "<bpeDadosMsg xmlns=\"$this->urlNamespace\">$request</bpeDadosMsg>";
        $this->lastResponse = $this->sendRequest($body, $parameters);
        return $this->lastResponse;
    }

    /**
     * Check the BPe status
     * @param string $chave
     * @param int $tpAmb
     * @return string
     */
    public function sefazConsultaChave($chave, $tpAmb = null)
    {
        $uf = UFList::getUFByCode(substr($chave, 0, 2));
        if (empty($tpAmb)) {
            $tpAmb = $this->tpAmb;
        }
        //carrega servi�o
        $servico = 'BPeConsulta';
        $this->checkContingencyForWebServices($servico);
        $this->servico(
            $servico,
            $uf,
            $tpAmb
        );
        $request = "<consSitBPe xmlns=\"$this->urlPortal\" versao=\"$this->urlVersion\">"
            . "<tpAmb>$tpAmb</tpAmb>"
            . "<xServ>CONSULTAR</xServ>"
            . "<chBPe>$chave</chBPe>"
            . "</consSitBPe>";
        $this->isValid($this->urlVersion, $request, 'consSitBPe');
        $this->lastRequest = $request;
        $parameters = ['bpeDadosMsg' => $request];
        $body = "<bpeDadosMsg xmlns=\"$this->urlNamespace\">$request</bpeDadosMsg>";
        $this->lastResponse = $this->sendRequest($body, $parameters);
        return $this->lastResponse;
    }

    /**
     * Request to disable one or an NFe sequence of a given series
     * @param int $nSerie
     * @param int $nIni
     * @param int $nFin
     * @param string $xJust
     * @param int $tpAmb
     * @return string
     */
    public function sefazInutiliza(
        $nSerie,
        $nIni,
        $nFin,
        $xJust,
        $tpAmb = null
    ) {
        if (empty($tpAmb)) {
            $tpAmb = $this->tpAmb;
        }
        $xJust = Strings::replaceSpecialsChars($xJust);
        $nSerie = (int) $nSerie;
        $nIni = (int) $nIni;
        $nFin = (int) $nFin;
        $servico = 'CteInutilizacao';
        $this->checkContingencyForWebServices($servico);
        //carrega servi�o
        $this->servico(
            $servico,
            $this->config->siglaUF,
            $tpAmb
        );
        $cnpj = $this->config->cnpj;
        $strAno = (string) date('y');
        $strSerie = str_pad($nSerie, 3, '0', STR_PAD_LEFT);
        $strInicio = str_pad($nIni, 9, '0', STR_PAD_LEFT);
        $strFinal = str_pad($nFin, 9, '0', STR_PAD_LEFT);
        $idInut = "ID"
            . $this->urlcUF
            . $cnpj
            . $this->modelo
            . $strSerie
            . $strInicio
            . $strFinal;
        //limpa os caracteres indesejados da justificativa
        $xJust = Strings::replaceSpecialsChars($xJust);
        //montagem do corpo da mensagem
        $msg = "<inutCTe xmlns=\"$this->urlPortal\" versao=\"$this->urlVersion\">" .
            "<infInut Id=\"$idInut\">" .
            "<tpAmb>$tpAmb</tpAmb>" .
            "<xServ>INUTILIZAR</xServ>" .
            "<cUF>$this->urlcUF</cUF>" .
            "<ano>$strAno</ano>" .
            "<CNPJ>$cnpj</CNPJ>" .
            "<mod>$this->modelo</mod>" .
            "<serie>$nSerie</serie>" .
            "<nCTIni>$nIni</nCTIni>" .
            "<nCTFin>$nFin</nCTFin>" .
            "<xJust>$xJust</xJust>" .
            "</infInut></inutCTe>";
        //assina a solicita��o
        $request = Signer::sign(
            $this->certificate,
            $msg,
            'infInut',
            'Id',
            $this->algorithm,
            $this->canonical
        );
        $request = Strings::clearXmlString($request, true);
        $this->isValid($this->urlVersion, $request, 'inutCTe');
        $this->lastRequest = $request;
        $parameters = ['cteDadosMsg' => $request];
        $body = "<cteDadosMsg xmlns=\"$this->urlNamespace\">$request</cteDadosMsg>";
        $this->lastResponse = $this->sendRequest($body, $parameters);
        return $this->lastResponse;
    }

    /**
     * Requires cte cancellation
     * @param  string $chave key of CTe
     * @param  string $xJust justificative 255 characters max
     * @param  string $nProt protocol number
     * @return string
     */
    public function sefazCancela($chave, $xJust, $nProt)
    {
        $uf = $this->validKeyByUF($chave);
        $xJust = Strings::replaceSpecialsChars(
            substr(trim($xJust), 0, 255)
        );
        $tpEvento = 110111;
        $nSeqEvento = 1;
        $tagAdic = "<evCancBPe>"
            . "<descEvento>Cancelamento</descEvento>"
            . "<nProt>$nProt</nProt>"
            . "<xJust>$xJust</xJust>"
            . "</evCancBPe>";
        return $this->sefazEvento(
            $uf,
            $chave,
            $tpEvento,
            $nSeqEvento,
            $tagAdic
        );
    }

    /**
     * Send event to SEFAZ
     * @param string $uf
     * @param string $chave
     * @param int $tpEvento
     * @param int $nSeqEvento
     * @param string $tagAdic
     * @return string
     */
    public function sefazEvento(
        $uf,
        $chave,
        $tpEvento,
        $nSeqEvento = 1,
        $tagAdic = ''
    ) {
        $ignore = false;
        //        if ($tpEvento == 110140) {
        //            $ignore = true;
        //        }
        $servico = 'BPeRecepcaoEvento';
        $this->checkContingencyForWebServices($servico);
        $this->servico(
            $servico,
            $uf,
            $this->tpAmb,
            $ignore
        );
        $ev = $this->tpEv($tpEvento);
        $aliasEvento = $ev->alias;
        $descEvento = $ev->desc;
        $cnpj = $this->config->cnpj;
        $dt = new \DateTime();
        $dhEvento = $dt->format('Y-m-d\TH:i:sP');
        $sSeqEvento = str_pad($nSeqEvento, 2, "0", STR_PAD_LEFT);
        $eventId = "ID" . $tpEvento . $chave . $sSeqEvento;
        $cOrgao = UFList::getCodeByUF($uf);

        $request = "<eventoBPe xmlns=\"$this->urlPortal\" versao=\"$this->urlVersion\">"
            . "<infEvento Id=\"$eventId\">"
            . "<cOrgao>$cOrgao</cOrgao>"
            . "<tpAmb>$this->tpAmb</tpAmb>"
            . "<CNPJ>$cnpj</CNPJ>"
            . "<chBPe>$chave</chBPe>"
            . "<dhEvento>$dhEvento</dhEvento>"
            . "<tpEvento>$tpEvento</tpEvento>"
            . "<nSeqEvento>$nSeqEvento</nSeqEvento>"
            . "<detEvento versaoEvento=\"$this->urlVersion\">"
            . "$tagAdic"
            . "</detEvento>"
            . "</infEvento>"
            . "</eventoBPe>";

        //assinatura dos dados
        $request = Signer::sign(
            $this->certificate,
            $request,
            'infEvento',
            'Id',
            $this->algorithm,
            $this->canonical
        );

        $request = Strings::clearXmlString($request, true);
        $this->isValid($this->urlVersion, $request, 'eventoBPe');
        $this->lastRequest = $request;
        $parameters = ['bpeDadosMsg' => $request];
        $body = "<bpeDadosMsg xmlns=\"$this->urlNamespace\">$request</bpeDadosMsg>";
        $this->lastResponse = $this->sendRequest($body, $parameters);
        return $this->lastResponse;
    }


    /**
     *
     * @param  int $tpEvento
     * @return \stdClass
     * @throws Exception
     */
    private function tpEv($tpEvento)
    {
        $std = new \stdClass();
        $std->alias = '';
        $std->desc = '';
        switch ($tpEvento) {
                //            case 110110:
                //                //CCe
                //                $std->alias = 'CCe';
                //                $std->desc = 'Carta de Correcao';
                //                break;
            case 110111:
                //cancelamento
                $std->alias = 'CancBPe';
                $std->desc = 'Cancelamento';
                break;
                //            case 110113:
                //                //EPEC
                //                //emiss�o em conting�ncia EPEC
                //                $std->alias = 'EPEC';
                //                $std->desc = 'EPEC';
                //                break;
                //            case 610110:
                //                //Servi�o em desacordo
                //                $std->alias = 'EvPrestDesacordo';
                //                $std->desc = 'Servico em desacordo';
                //                break;
            default:
                $msg = "O c�digo do tipo de evento informado n�o corresponde a "
                    . "nenhum evento estabelecido.";
                throw new RuntimeException($msg);
        }
        return $std;
    }
}
