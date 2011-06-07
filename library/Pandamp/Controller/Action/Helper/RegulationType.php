<?php

/**
 * Description of RegulationType
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_RegulationType
{
    public function regulationType($rt)
    {
        switch(strtolower($rt))
        {
            case 'konstitusi':
                    $regulationOrder = 1;
                    break;
            case 'tap mpr':
                    $regulationOrder = 11;
                    break;
            case 'tus mpr':
                    $regulationOrder = 21;
                    break;
            case 'undang-undang':
            case 'uu':
                    $regulationOrder = 31;
                    break;
            case 'undang-undang darurat':
                    $regulationOrder = 41;
                    break;
            case 'perpu':
                    $regulationOrder = 51;
                    break;
            case 'pp':
                    $regulationOrder = 61;
                    break;
            case 'perpres':
                    $regulationOrder = 71;
                    break;
            case 'penpres':
                    $regulationOrder = 81;
                    break;
            case 'keppres':
                    $regulationOrder = 91;
                    break;
            case 'inpres':
                    $regulationOrder = 101;
                    break;
            case 'konvensi internasional':
                    $regulationOrder = 111;
                    break;
            case 'keputusan bersama':
                    $regulationOrder = 121;
                    break;
            case 'keputusan dewan':
                    $regulationOrder = 131;
                    break;
            case 'kepmen':
                    $regulationOrder = 141;
                    break;
            case 'permen':
                    $regulationOrder = 151;
                    break;
            case 'inmen':
                    $regulationOrder = 161;
                    break;
            case 'pengumuman menteri':
                    $regulationOrder = 171;
                    break;
            case 'surat edaran menteri':
                    $regulationOrder = 181;
                    break;
            case 'surat menteri':
                    $regulationOrder = 191;
                    break;
            case 'keputusan asisten menteri':
                    $regulationOrder = 201;
                    break;
            case 'surat asisten menteri':
                    $regulationOrder = 211;
                    break;
            case "keputusan menteri negara/ketua lembaga/badan":
                    $regulationOrder = 221;
                    break;
            case "peraturan menteri negara/ketua lembaga/badan":
                    $regulationOrder = 231;
                    break;
            case "instruksi menteri negara/ketua lembaga/badan":
                    $regulationOrder = 241;
                    break;
            case "pengumuman menteri negara/ketua lembaga/badan":
                    $regulationOrder = 251;
                    break;
            case "surat edaran menteri negara/ketua lembaga/badan":
                    $regulationOrder = 261;
                    break;
            case "surat menteri negara/ketua lembaga/badan":
                    $regulationOrder = 271;
                    break;
            case "keputusan lembaga/badan":
                    $regulationOrder = 281;
                    break;
            case "peraturan lembaga/badan":
                    $regulationOrder = 291;
                    break;
            case "instruksi lembaga/badan":
                    $regulationOrder = 301;
                    break;
            case "pengumuman lembaga/badan":
                    $regulationOrder = 311;
                    break;
            case "surat edaran lembaga/badan":
                    $regulationOrder = 321;
                    break;
            case "surat lembaga/badan":
                    $regulationOrder = 331;
                    break;
            case "keputusan kepala/ketua lembaga/badan":
                    $regulationOrder = 341;
                    break;
            case "peraturan kepala/ketua lembaga/badan":
                    $regulationOrder = 351;
                    break;
            case "instruksi kepala/ketua lembaga/badan":
                    $regulationOrder = 361;
                    break;
            case "pengumuman kepala/ketua lembaga/badan":
                    $regulationOrder = 371;
                    break;
            case "surat edaran kepala/ketua lembaga/badan":
                    $regulationOrder = 381;
                    break;
            case "surat kepala/ketua lembaga/badan":
                    $regulationOrder = 391;
                    break;
            case "keputusan komisi":
                    $regulationOrder = 401;
                    break;
            case "peraturan komisi":
                    $regulationOrder = 411;
                    break;
            case "instruksi komisi":
                    $regulationOrder = 421;
                    break;
            case "pengumuman komisi":
                    $regulationOrder = 431;
                    break;
            case "surat edaran komisi":
                    $regulationOrder = 441;
                    break;
            case "surat komisi":
                    $regulationOrder = 451;
                    break;
            case "keputusan panitia":
                    $regulationOrder = 461;
                    break;
            case "peraturan panitia":
                    $regulationOrder = 471;
                    break;
            case "instruksi panitia":
                    $regulationOrder = 481;
                    break;
            case "pengumuman panitia":
                    $regulationOrder = 491;
                    break;
            case "surat edaran panitia":
                    $regulationOrder = 501;
                    break;
            case "surat panitia":
                    $regulationOrder = 511;
                    break;
            case "keputusan direktur jenderal":
                    $regulationOrder = 521;
                    break;
            case "surat edaran direktur jenderal":
                    $regulationOrder = 531;
                    break;
            case "surat direktur jenderal":
                    $regulationOrder = 541;
                    break;
            case "instruksi direktur jenderal":
                    $regulationOrder = 551;
                    break;
            case "peraturan direktur jenderal":
                    $regulationOrder = 561;
                    break;
            case "peraturan inspektur jenderal":
                    $regulationOrder = 571;
                    break;
            case "instruksi inspektur jenderal":
                    $regulationOrder = 581;
                    break;
            case "pengumuman inspektur jenderal":
                    $regulationOrder = 591;
                    break;
            case "surat edaran inspektur jenderal":
                    $regulationOrder = 601;
                    break;
            case "surat inspektur jenderal":
                    $regulationOrder = 611;
                    break;
            case "peraturan daerah tingkat i":
                    $regulationOrder = 621;
                    break;
            case "peraturan daerah tingkat ii":
                    $regulationOrder = 631;
                    break;
            case "keputusan gubernur":
                    $regulationOrder = 641;
                    break;
            case "peraturan gubernur":
                    $regulationOrder = 651;
                    break;
            case "instruksi gubernur":
                    $regulationOrder = 661;
                    break;
            case "pengumuman gubernur":
                    $regulationOrder = 671;
                    break;
            case "surat edaran gubernur":
                    $regulationOrder = 681;
                    break;
            case "surat gubernur":
                    $regulationOrder = 691;
                    break;
            case "keputusan bupati/walikota":
                    $regulationOrder = 701;
                    break;
            case "peraturan bupati/walikota":
                    $regulationOrder = 711;
                    break;
            case "instruksi bupati/walikota":
                    $regulationOrder = 721;
                    break;
            case "pengumuman bupati/walikota":
                    $regulationOrder = 731;
                    break;
            case "surat edaran bupati/walikota":
                    $regulationOrder = 741;
                    break;
            case "surat bupati/walikota":
                    $regulationOrder = 751;
                    break;
            case "keputusan direksi":
                    $regulationOrder = 761;
                    break;
            case "peraturan direksi":
                    $regulationOrder = 771;
                    break;
            case "instruksi direksi":
                    $regulationOrder = 781;
                    break;
            case "pengumuman direksi":
                    $regulationOrder = 791;
                    break;
            case "surat edaran direksi":
                    $regulationOrder = 801;
                    break;
            case "surat direksi":
                    $regulationOrder = 811;
                    break;
            case "keputusan direktur":
                    $regulationOrder = 821;
                    break;
            case "peraturan direktur":
                    $regulationOrder = 831;
                    break;
            case "instruksi direktur":
                    $regulationOrder = 841;
                    break;
            case "pengumuman direktur":
                    $regulationOrder = 851;
                    break;
            case "surat edaran direktur":
                    $regulationOrder = 861;
                    break;
            case "surat direktur":
                    $regulationOrder = 871;
                    break;
            /*case :
                    $regulationOrder = 881;
                    break;*/
            default:
                    $regulationOrder = 9999;
                    break;
        }

        return $regulationOrder;
    }
}
