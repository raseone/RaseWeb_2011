<?php
/* this is actually file_put_contents. gallerator was written for 4.x, 
    and this chokes for 5.x. Fix it so it doesn't choke, and someday,
    when the world is greener and kinder and more up to date, make
    f_p_C into file_put_contents, and delete this declaration
*/
function f_p_C($filename,$datastring)
{
    if (false === ($fp = fopen($filename,"w"))) return false;
    if (false === fwrite($fp,$datastring)) {fclose($fp); return false;}
    if (false === fclose($fp)) return false;
    return true;
}

/* works only with imagskin.php

   configure the script here:

   THUMBS is the relative or absolute directory of the thumbnails
   IMAGES is the relative or absolute directory of the images
   BOTH images and thumbs MUST NOT end in a slash, but must be able to have
      a slash appended to them
   COLUMNS is the number of columns you want in the table of thumbnails
   TABLEDATA is an attribute that will be written on the table of thumbs.
   ROWDATA is an attribute that will be written on each row
   CELLDATA is an attribute that will be written for each cell
   SHOWERRORS is "" when you don't want to see any errors, and "yes"
      when you do want to see errors. If there are any orphan thumbs,
      or orphan images, their names will be listed at the end of the
      page, unless SHOWERRORS is "".
   UP is the relative or dynamic directory of the caller (added 1/9/2009)
      to accommodate dynahelp.php
   DATA is the relative or absolute directory of data that is associated
      with any of the images.
*/

function dothumbs($thumbs,$images,$columns,$tabledata,$rowdata,$celldata,$data,$showerrors,$up) {

require_once 'gallauth.php';
$errors = "";

/* fill in some helpful defaults.
*/
if (!isset($columns)) $columns = 4;
if (!isset($tabledata)) $tabledata = "";
if (!isset($rowdata)) $rowdata = "";
if (!isset($celldata)) $celldata = "";
if (!isset($showerrors)) $showerrors = "";
if (!isset($up)) $up = $_SERVER['PHP_SELF'];

/* check to see if we have any thumbs and images data that has been passed in 
   through the URL. If we have neither, then we can do the default processing
   for the folders that are specified on disk.
*/
if (!isset($_GET['navi']) && !isset($_GET['navt'])) {
    if (!isset($thumbs)) $thumbs = "./thumbs";
    if (!isset($images)) $images = "./images";
    $urlnav = false;
}
else {
    /* grab the list of thumbs and images that are coming in from the url.
       after error checking them, perhaps against the folders that they are 
       allowed to come from (continue editing here), use them as data.
       we must safeguard against people grabbing arbitrary files.
    */
    $goodi = unserialize(gzuncompress(base64_decode(strtr($_GET['navi'],'-_', '+/'))));
    if (false === $goodi) die("invalid URL");
    $goodt = unserialize(gzuncompress(base64_decode(strtr($_GET['navt'],'-_', '+/'))));
    if (false === $goodt) die("invalid URL");
    if (count($goodt) != count($goodi)) die("invalid URL");
    if (count($goodt) == 0) die("invalid URL");

    /* load the variables that the rest of the code uses for this stuff.
       seri and sert are serialized data structures. thumbs and images are the
       tables of thumbs and images
    */
    $seri = strtr(base64_encode(gzcompress(serialize($goodi))),'+/', '-_');
    $sert = strtr(base64_encode(gzcompress(serialize($goodt))),'+/', '-_');
    $thumbs = dirname($goodt[0]);
    $images = dirname($goodi[0]);

    /* set up the dynahelp support "up" link
    */
    $up = $up . "?navi=$seri&navt=$sert";
    $urlnav = true;
}

/* check to see if the directories even exist
*/
$dh = @opendir($thumbs);
if ($dh) closedir($dh);
else $errors = $errors . 
    "thumbs folder $thumbs does not exist or is not readable.<br>\n";

$dh = @opendir($images);
if ($dh) closedir($dh);
else $errors = $errors . 
    "images folder $images does not exist or is not readable.<br>\n";

/* check the thumbs folder for the navigation cache, in the fixed filenames
   _navcachet and _navcachei. If we find it, we use it for both the list of 
   good thumbs and good images. If we don't find it, then we write one out
   after we construct it.

   if showerrors is true, or if we are an admin, of if there are any errors,
   or if we already have navigation cache, then we do not want to
   load the navigation cache, as we want to get the galleries fresh each
   time, as they are changing, or we are looking for diagnostics from them.
*/

if (!isset($goodt) &&
    !isset($goodi) &&
    !isgalleratoradmin() &&
    (strlen($showerrors) == 0) && 
    (strlen($errors) == 0) && 
    file_exists("$thumbs/_navcachet") && 
    file_exists("$thumbs/_navcachei")) {
	if ((false !== ($seri = file_get_contents("$thumbs/_navcachei"))) &&
	    (false !== ($sert = file_get_contents("$thumbs/_navcachet")))) {
	    $goodt = unserialize(gzuncompress(base64_decode(
	        strtr($sert,'-_', '+/')	    )));
	    $goodi = unserialize(gzuncompress(base64_decode(
	        strtr($seri,'-_', '+/')	    )));

	    /* one final check on the integrity of the navigation caches.
	       ensure that we can get to the folders. If this folder has
	       been moved from another folder, the path to the images
	       could be wrong.
	    */
	    if (count($goodt) <= 0 || count($goodi) <= 0) {
	        unset($goodt);
		unset($goodi);
	    }
	    else if (!file_exists($goodt[0]) || !file_exists($goodi[0])) {
	        unset($goodt);
		unset($goodi);
	    }
        }
}

/* build the navigation cache, as one has not been provided, or has been
   intentionally ignored
*/
if (!isset($goodi) || !isset($goodt)) {

    /* get the list of thumbnails and images to look at
    */
    $suffixes = "JPG,jpg,GIF,gif,PNG,png";
    $extensions = "/*.{" . $suffixes . "}";
    $thumblist = glob($thumbs . $extensions, GLOB_BRACE);
    $imageslist = glob($images . $extensions, GLOB_BRACE);

    $tcount = count($thumblist);
    $icount = count($imageslist);

    /* check to see if there are any files in the directories
    */
    if ($tcount == 0 || $icount == 0) {
	if ($tcount == 0) $errors  = $errors . "thumbs folder $thumbs" .
	    " has no $suffixes files in it.<br>\n";
	if ($icount == 0) $errors  = $errors . "images folder $images" .
	    " has no $suffixes files in it.<br>\n";
	
    }

    /* force errors to show if we have any errors at this early time
    */
    if (0 != strcmp("",$errors)) $showerrors = "yes";

    sort($thumblist);
    sort($imageslist);

    $t = 0; $gt = 0; $goodt = array();
    $i = 0; $gi = 0; $goodi = array();

    /* two way merge, where we run down the list of images and the list
       of thumbs, and locate the ones that are identical, and identify
       the thumbs that don't have images and the images that don't have
       thumbs.
    */

    while (($t < $tcount) && ($i < $icount)) {

	if (0 == strcmp(basename($thumblist[$t]),basename($imageslist[$i]))) {
	    $goodt[$gt] = $thumblist[$t];
	    $goodi[$gi] = $imageslist[$i];
	    $t = $t + 1;
	    $i = $i + 1;
	    $gt = $gt + 1;
	    $gi = $gi + 1;
	}
	elseif (0 > strcmp(basename($thumblist[$t]),basename($imageslist[$i]))) { 
	    $errors = $errors . "no match for thumb  " 
		. $thumblist[$t] . "<br>\n";;
	    $t = $t + 1;
	}
	elseif (0 < strcmp(basename($thumblist[$t]),basename($imageslist[$i]))) { 
	    $errors = $errors . "no match for image  " 
		. $imageslist[$i] . "<br>\n";
	    $i = $i + 1;
	}
    }

    /* if either list ended prematurely, output everything on the other list
       as an error, so that someone can fix it someday.
    */
    while ($t < $tcount) {
	$errors = $errors . "no match for thumb  " . $thumblist[$t] . "<br>\n";;
	$t = $t + 1;
    }

    while ($i < $icount) {
	$errors = $errors . "no match for image  " . $imageslist[$i] . "<br>\n";
	$i = $i + 1;
    }

    /* at this point, we have a list of good thumbs and good images, both. They
       should match up exactly. We're just going to save them both, in two files
       in the thumbs folder: _navcachet and _navcachei. These lists of file specs
       both have the path embedded in them.
    */
    $seri = strtr(base64_encode(gzcompress(serialize($goodi))),'+/', '-_');
    $sert = strtr(base64_encode(gzcompress(serialize($goodt))),'+/', '-_');
    if (false === f_p_C("$thumbs/_navcachet",$sert)) ;
    if (false === f_p_C("$thumbs/_navcachei",$seri)) ;

    /* since we've just written the navigation caches, we should see if we
       should make the creation scripts for the side folders, to reduce the
       human overhead of typing things in that have to match exactly.
       We append the time to the name of the file so that multiple folders can
       use the same data folder, and not have their creation scripts clobbered.
    */
    if (isset($data) && is_dir($data)) {
	$creationfile = $data . '/' . '_Create_' . time() . '.php'; 
	$fp = fopen($creationfile,'w');
	if ($fp === false) {
	    $error .= "could not create $creationfile\n";
	}
	else {
	    fwrite($fp,"<?php\n");

            foreach ($goodi as $gi) {
		$dname = substr(basename($gi),0,strpos(basename($gi),'.'));
		fwrite($fp,
		    "if (!is_dir('$dname'))\n" . 
		    "    if (!mkdir('$dname',0711))\n" . 
		    "        print 'could not create folder $dname';\n" .
		    "\n");
	
	    }
	    fwrite($fp,"?>\n");
	    fwrite($fp,"<html><body>$creationfile completed</body</html>");
	    fclose($fp);
        }
    }
}

/* obtain a validated pointer to the "other" data, if it is to be 
   available to the image skin. 
*/
if (!isset($data) || !is_dir($data)) unset($data);

/* now run through the list of known good thumb/image pairs, and make some
   html for the thumbs list. 
*/

$tcount = count($goodt);
$icount = count($goodi);
$i = 0;
$t = 0;

print "\n<div class=g_table>\n";

/* hoist this code out of the loop. things will be faster.
*/
$serup = strtr(base64_encode(gzcompress($up)),'+/', '-_');

while ($i < $icount) {
    if ($t == 0) {
	/* used to be a tr tag */
    }

    print "<div class=g_tabledata>";
    
    /* construct the navigation links that we'll pass to the images script
       protect up with quotes, in case it has ampersands in it
    */

    $navlist = "i=" . $i;
    $navlist = $navlist . "&";

    $navlist = $navlist . "up=" . $serup;
    $navlist = $navlist . "&";

    /* need to differentiate when these two cases will be used. It seems that
       when we are doing disk based galleries that we own, we should just
       point to the items on disk.
       however, when we are doing stuff based on the url, when the user has
       passed in the navigation for us, then we should pass the url based 
       data structures in.
    */
    if ($urlnav)
        $navlist = $navlist . "nav=" . $seri;
    else $navlist = $navlist . "nav=" . 
	strtr(base64_encode(gzcompress(serialize($thumbs))),'+/', '-_');

    /* construct any admin authorization for the images script, and add a 
       reference to the thumb file name and folder. the expected situation is
       that you see a skanky 'ho' or pimp in the thumbs, and you blow them up
       big, and then you cap them once they're big. The censoring software 
       can then pull both the thumb and the image out.
    */
    $adminstring = "";
    if (isgalleratoradmin()) {
	$adminstring = '&gallauth=' . $_GET['gallauth'];
	$adminstring .= '&thumb=' . $goodt[$i];
    }

    /* obtain the folder name for the auxiliary information, and see if
	we can find that title that we are looking for. We're going to chop it
        off at 10 characters, maybe
    */
    if (isset($data)) {
	$gi = $goodt[$i];
	$dname = substr(basename($gi),0,strpos(basename($gi),'.'));
	if (file_exists("$data/$dname/title")) {
	    $thumbtitle = file_get_contents("$data/$dname/title");
	    if ($thumbtitle === false) $thumbtitle = "couldn't read file";
	    $thumbtitle = '<div style="display:inline;" class=g_thumbtitle>' .
		 $thumbtitle . '</div>';
	}
	else unset($thumbtitle);

        /* add the folder with the extra data to the navlist, without much 
	   concern. ed means extra data.
        */
	$navlist .= '&ed=' . 
	strtr(base64_encode(gzcompress(serialize("$data"))),'+/', '-_');
    }

    /* write out the reference to the html file
    */
    print "<a href=\"imagskin.php?&" . $navlist . $adminstring . "\">";
    print "<img class=g_thumbimage src=\"$goodt[$i]\"></a><br>";

    /* print out the small title, if defined
    */
    if (isset($thumbtitle)) print "$thumbtitle<br>\n";

    print "</div>"; /* close off the tabledata div */
   
    $i = $i + 1;
    $t = $t + 1;

    if ($t >= $columns) {
	/* used to be table row */
	$t = 0;
    }
}

if ($t != 0) /* used to be table row */;

print "\n</div>\n"; /* used to be table */

if (0 != strcmp("",$showerrors)) 
    if (0 != strcmp("",$errors)) print "<H3>Errors!</h3><br>$errors";
}
?>
