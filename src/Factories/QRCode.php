<?php

namespace NFePHP\BPe\Factories;

/**
 * Class QRCode create a string to make a QRCode string
 *
 * @author    Cleiton Perin <cperin20 at gmail dot com>
 * @package   NFePHP\BPe\Factories\QRCode
 * @copyright NFePHP Copyright (c) 2008-2019
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @category  NFePHP
 * @link      http://github.com/nfephp-org/sped-bpe for the canonical source repository
 */

use DOMDocument;
use NFePHP\BPe\Exception\DocumentsException;

class QRCode
{
    /**
     * putQRTag
     * @param DOMDocument $dom BP-e
     * @return string
     * @throws DocumentsException
     */
    public static function putQRTag(
        \DOMDocument $dom
    ) {

        $bpe = $dom->getElementsByTagName('BPe')->item(0);
        $infBPe = $dom->getElementsByTagName('infBPe')->item(0);
        $ide = $dom->getElementsByTagName('ide')->item(0);
        $chBPe = preg_replace('/[^0-9]/', '', $infBPe->getAttribute("Id"));
        $tpAmb = $ide->getElementsByTagName('tpAmb')->item(0)->nodeValue;
        $urlQRCode = "https://dfe-portal.svrs.rs.gov.br/bpe/qrCode?chBPe=$chBPe&tpAmb=$tpAmb";
        $infBPeSupl = $dom->createElement("infBPeSupl");
        $qrCode = $infBPeSupl->appendChild($dom->createElement('qrCodBPe'));
        $qrCode->appendChild($dom->createCDATASection($urlQRCode));
        $signature = $dom->getElementsByTagName('Signature')->item(0);
        $bpe->insertBefore($infBPeSupl, $signature);
        $dom->formatOutput = false;
        return $dom->saveXML();
    }
}
