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
     * Requires bpe event não embarque
     * @param  string $chave key of BP-e
     * @param  string $xJust justificative 255 characters max
     * @param  string $nProt protocol number
     * @return string
     */
    public function sefazNaoEmbarque($chave, $xJust, $nProt)
    {
        $uf = $this->validKeyByUF($chave);
        $xJust = Strings::replaceSpecialsChars(
            substr(trim($xJust), 0, 255)
        );
        $tpEvento = 110115;
        $nSeqEvento = 1;
        $tagAdic = "<evNaoEmbBPe>"
            . "<descEvento>Não Embarque</descEvento>"
            . "<nProt>$nProt</nProt>"
            . "<xJust>$xJust</xJust>"
            . "</evNaoEmbBPe>";
        return $this->sefazEvento(
            $uf,
            $chave,
            $tpEvento,
            $nSeqEvento,
            $tagAdic
        );
    }


    /**
     * Requires bpe event alteração de poltrona
     * @param  string $chave key of BP-e
     * @param  string $xJust justificative 255 characters max
     * @param  string $nProt protocol number
     * @return string
     */
    public function sefazAlteracaoPoltrona($chave, $poltrona, $nProt, $nSeqEvento)
    {
        $uf = $this->validKeyByUF($chave);
        $tpEvento = 110116;
        $tagAdic = "<evAlteracaoPoltrona>"
            . "<descEvento>Alteração Poltrona</descEvento>"
            . "<nProt>$nProt</nProt>"
            . "<poltrona>$poltrona</poltrona>"
            . "</evAlteracaoPoltrona>";
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
}
