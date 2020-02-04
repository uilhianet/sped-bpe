<?php

namespace NFePHP\BPe;

use DOMDocument;
use NFePHP\BPe\Common\Standardize;
use NFePHP\BPe\Exception\DocumentsException;
use NFePHP\Common\Strings;

class Complements
{
    protected static $urlPortal = 'http://www.portalfiscal.inf.br/bpe';

    /**
     * Authorize document adding his protocol
     * @param string $request
     * @param string $response
     * @return string
     */
    public static function toAuthorize($request, $response)
    {

        //return $request;
        $st = new Standardize();
        $key = ucfirst($st->whichIs($request));
        if ($key != 'BPe' && $key != 'EventoBPe') {
            //wrong document, this document is not able to recieve a protocol
            throw DocumentsException::wrongDocument(0, $key);
        }
        $func = "add" . $key . "Protocol";
        return self::$func($request, $response);
    }

    /**
     * Authorize BP
     * @param string $request
     * @param string $response
     * @return string
     * @throws InvalidArgumentException
     */
    protected static function addBPeProtocol($request, $response)
    {

        $req = new DOMDocument('1.0', 'UTF-8');
        $req->preserveWhiteSpace = false;
        $req->formatOutput = false;
        $req->loadXML($request);

        $bpe = $req->getElementsByTagName('BPe')->item(0);
        $infBPe = $req->getElementsByTagName('infBPe')->item(0);
        $versao = $infBPe->getAttribute("versao");
        $digBPe = $req->getElementsByTagName('DigestValue')
            ->item(0)
            ->nodeValue;
        $ret = new DOMDocument('1.0', 'UTF-8');
        $ret->preserveWhiteSpace = false;
        $ret->formatOutput = false;
        $ret->loadXML($response);
        $retProt = $ret->getElementsByTagName('protBPe')->item(0);
        if (!isset($retProt)) {
            throw DocumentsException::wrongDocument(3, "&lt;protBPe&gt;");
        }
        $infProt = $ret->getElementsByTagName('infProt')->item(0);
        $cStat = $infProt->getElementsByTagName('cStat')->item(0)->nodeValue;
        $xMotivo = $infProt->getElementsByTagName('xMotivo')->item(0)->nodeValue;
        $dig = $infProt->getElementsByTagName("digVal")->item(0);
        $digProt = '000';
        if (isset($dig)) {
            $digProt = $dig->nodeValue;
        }
        //100 Autorizado
        if ($cStat != '100') {
            throw DocumentsException::wrongDocument(4, "[$cStat] $xMotivo");
        }
        if ($digBPe !== $digProt) {
            throw DocumentsException::wrongDocument(5, "O digest é diferente");
        }
        return self::join(
            $req->saveXML($bpe),
            $ret->saveXML($retProt),
            'bpeProc',
            $versao
        );
    }

    /**
     * Authorize Event
     * @param string $request
     * @param string $response
     * @return string
     * @throws InvalidArgumentException
     */
    protected static function addEventoBPeProtocol($request, $response)
    {
        $ev = new \DOMDocument('1.0', 'UTF-8');
        $ev->preserveWhiteSpace = false;
        $ev->formatOutput = false;
        $ev->loadXML($request);
        //extrai tag evento do xml origem (solicitação)
        $event = $ev->getElementsByTagName('eventoBPe')->item(0);
        $versao = $event->getAttribute('versao');

        $ret = new \DOMDocument('1.0', 'UTF-8');
        $ret->preserveWhiteSpace = false;
        $ret->formatOutput = false;
        $ret->loadXML($response);
        //extrai a rag retEvento da resposta (retorno da SEFAZ)
        $retEv = $ret->getElementsByTagName('retEventoBPe')->item(0);
        $cStat = $retEv->getElementsByTagName('cStat')->item(0)->nodeValue;
        $xMotivo = $retEv->getElementsByTagName('xMotivo')->item(0)->nodeValue;
        $tpEvento = $retEv->getElementsByTagName('tpEvento')->item(0)->nodeValue;
        if ($tpEvento == '110111') {
            $node = 'procCancBPe';
        } elseif ($tpEvento == '110115') {
            $node = 'procNaoEmb';
        } elseif ($tpEvento == '110116') {
            $node = 'procAlteracaoPoltrona';
        } else {
            throw DocumentsException::wrongDocument(4, "Evento não disponivel.");
        }
        if ($cStat != '135') {
            throw DocumentsException::wrongDocument(4, "[$cStat] $xMotivo");
        }
        return self::join(
            $ev->saveXML($event),
            $ret->saveXML($retEv),
            $node,
            $versao
        );
    }

    /**
     * Join the pieces of the source document with those of the answer
     * @param string $first
     * @param string $second
     * @param string $nodename
     * @param string $versao
     * @return string
     */
    protected static function join($first, $second, $nodename, $versao)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
            . "<$nodename versao=\"$versao\" "
            . "xmlns=\"" . self::$urlPortal . "\">";
        $xml .= $first;
        $xml .= $second;
        $xml .= "</$nodename>";
        return $xml;
    }

    /**
     * Add cancel protocol to a autorized BPe
     * if event is not a cancellation will return
     * the same autorized BPe passing
     * NOTE: This action is not necessary, I use only for my needs to
     * leave the BPe marked as Canceled in order to avoid mistakes
     * after its cancellation.
     * @param string $BPe content of autorized BPe XML
     * @param string $cancelamento content of SEFAZ response
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function cancelRegister($bpe, $cancelamento)
    {
        $procXML = $bpe;
        $dombpe = new DOMDocument('1.0', 'utf-8');
        $dombpe->formatOutput = false;
        $dombpe->preserveWhiteSpace = false;
        $dombpe->loadXML($bpe);
        $proBPe = $dombpe->getElementsByTagName('protBPe')->item(0);
        if (empty($proBPe)) {
            //not protocoladed BPe
            throw DocumentsException::wrongDocument(1);
        }
        $chaveBPe = $proBPe->getElementsByTagName('chBPe')->item(0)->nodeValue;
        $domcanc = new DOMDocument('1.0', 'utf-8');
        $domcanc->formatOutput = false;
        $domcanc->preserveWhiteSpace = false;
        $domcanc->loadXML($cancelamento);
        $eventos = $domcanc->getElementsByTagName('retEventoBPe');
        foreach ($eventos as $evento) {
            $infEvento = $evento->getElementsByTagName('infEvento')->item(0);
            $cStat = $infEvento->getElementsByTagName('cStat')
                ->item(0)
                ->nodeValue;
            $nProt = $infEvento->getElementsByTagName('nProt')
                ->item(0)
                ->nodeValue;
            $chaveEvento = $infEvento->getElementsByTagName('chBPe')
                ->item(0)
                ->nodeValue;
            $tpEvento = $infEvento->getElementsByTagName('tpEvento')
                ->item(0)
                ->nodeValue;
            if (
                in_array($cStat, ['135', '136', '155'])
                && $tpEvento == '110111'
                && $chaveEvento == $chaveBPe
            ) {
                $proBPe->getElementsByTagName('cStat')
                    ->item(0)
                    ->nodeValue = '101';
                $proBPe->getElementsByTagName('nProt')
                    ->item(0)
                    ->nodeValue = $nProt;
                $proBPe->getElementsByTagName('xMotivo')
                    ->item(0)
                    ->nodeValue = 'Cancelamento de BP-e homologado';
                $procXML = Strings::clearProtocoledXML($dombpe->saveXML());
                break;
            }
        }
        return $procXML;
    }
}
