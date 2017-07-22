<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Gallery</title>
<link href="../Rase.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	background-image: url(../images/Backgrounds/dots.gif);
	background-color: #333;
	background-repeat: repeat;
}
-->
</style>
<link href="../raseone.css" rel="stylesheet" type="text/css" />
<link href="../rase_boxes.css" rel="stylesheet" type="text/css" />
<link href="../gallery/gallerator.css" rel="stylesheet" type="text/css" />
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<div class= "full_page2" align= "center">
  <div class="fta_skin">
    <div class="top_strip2"><div class= "logo"><img src="../images/fta_logos/fta_logo_image_520.png" width="469" height="150" /></div>
      <?php include"../includes/nav2.php"; ?></div>
    <br />
    <table width="1060" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="231" valign="top">
          <div class="left_box_black"><span class="TitleWhite">Gallery Section</span></div>
          <br />
          <div class="sidebox_rgba">
            <?php include"section_nav.php"; ?>
        </div>
        <br />
        <div class="sidebox_rgba">
          <div class="WhiteText">
            <p><span class="TitleWhite">Deposits &amp; Payments</span><br />
              <br />
              Welcome
              to our payment page. Thank you for your business. Please use the options on this page to configure your deposits, subscriptions &amp; payments.<br />
            </p>
          </div>
        </div></td>
        <td width="601" valign="top"><div class="content_box"><span class="TitleWhite">Gallery - Image Display</span></div>
          <br />          
          <div class="content_box">        <?php
require_once '/home/raseone/php/setpath.php';
require 'images.php';
?></div></td>
        <td width="220" valign="top"><div class="rightbox_rgba"><br />
          <br />
        </div>
        <br />
        <div class="rightbox_rgba"></div></td>
      </tr>
    </table>
    <br />
  </div><?php include"../includes/footer2.php"; ?></div>

</body>
</html>