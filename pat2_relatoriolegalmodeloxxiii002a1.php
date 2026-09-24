<?php

require_once('libs/db_stdlib.php');
require_once('libs/db_conecta.php');
require_once('libs/db_sessoes.php');
require_once('libs/db_usuariosonline.php');
require_once('libs/db_conn.php');

//echo "tes";
//require_once('tcpdf_include.php');

//error_get_last();



    require_once('tcpdf_include.php');

    //$PDF_PAGE_ORIENTATION_LOCAL = "L";

    // create new PDF document
    //$pdf = new TCPDF();//$PDF_PAGE_ORIENTATION_LOCAL, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //define('K_TCPDF_THROW_EXCEPTION_ERROR', true);

    // set document information
    //$pdf->SetCreator(PDF_CREATOR);
    //$pdf->SetAuthor('Nicola Asuni');
    //$pdf->SetTitle('Relatório Legal XXIII');
    //$pdf->SetSubject('Relatório Legal XXIII');
    //$pdf->SetKeywords('Patrimônio, Relatório, Legal, XXIII');

    // set default header data
    //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

    // set header and footer fonts
    //$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    //$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    //$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    //$margin_top = 9;
    //$pdf->SetMargins(PDF_MARGIN_LEFT, $margin_top, PDF_MARGIN_RIGHT);
    //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
   // $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
   // $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
  //  if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
  //      require_once(dirname(__FILE__).'/lang/eng.php');
   //     $pdf->setLanguageArray($l);
   // }



?>
