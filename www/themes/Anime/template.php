<?
  // ---------------------------------------------------------------------------------------------------------

  //-------- Begins a main frame

// Функция начала основного блока (главного контента)
function begin_main_frame(string $title = '') {
    print("<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
<tr><td class=\"c_1\">&nbsp;</td><td class=\"c_2\">&nbsp;</td><td class=\"c_3\">&nbsp;</td></tr>
<tr><td class=\"c_l\">&nbsp;</td><td class=\"brd\">
<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
<tr><td id=\"table_color\" class=\"brd\">
<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
<tr><td class=\"content\"><br>
<div class=\"c_title\">" . htmlspecialchars($title) . "</div>\n");
}

  //-------- Ends a main frame

  function end_main_frame()
  {
    print("</td></tr></table></td></tr></table></td><td class=\"c_r\">&nbsp;</td></tr><tr><td class=\"c_4\">&nbsp;</td><td class=\"c_5\">&nbsp;</td><td class=\"c_6\">&nbsp;</td></tr></table><br />\n");
  }

  
  
  
  
  
    // ---------------------------------------------------------------------------------------------------------

  //-------- Begins a my frame

  function begin_my_frame($title)
  {
    print("<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"c_1\">&nbsp;</td><td class=\"c_2\">&nbsp;</td><td class=\"c_3\">&nbsp;</td></tr><tr><td class=\"c_l\">&nbsp;</td><td class=\"brd\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td id=\"table_color\" class=\"brd\"><table width=\"100%\" border=\"0px\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"content\" border=\"0px\"><br><div class=\"c_title\">".$title."</div>\n");
  }

  //-------- Ends a my frame

  function end_my_frame()
  {
    print("</td></tr></table></td></tr></table></td><td class=\"c_r\">&nbsp;</td></tr><tr><td class=\"c_4\">&nbsp;</td><td class=\"c_5\">&nbsp;</td><td class=\"c_6\">&nbsp;</td></tr></table><br />\n");
  }

  // ---------------------------------------------------------------------------------------------------------

  function begin_table($fullwidth = false, $padding = 5)
  {
    $width = "";
    
    if ($fullwidth)
      $width .= " width=\"100%\"";
    print("<table $width border=\"0px\" cellspacing=\"0\" cellpadding=\"$padding\">\n");
  }

  function end_table()
  {
    print("</td></tr></table>\n");
  }
  
  // ---------------------------------------------------------------------------------------------------------

  function begin_frame($caption = "", $center = false, $padding = 10)
  {
    $tdextra = "";
    
    if ($caption)
      print("<h2>$caption</h2>\n");

    if ($center)
      $tdextra .= " id=\"frame_td\"";
	  

    print("<table width=\"100%\" $tdextra  border=\"0px\" cellspacing=\"0\" cellpadding=\"$padding\"><tr ><td $tdextra  border=\"0\">\n");

  }

  function attach_frame($padding = 10)
  {
    print("</td></tr><tr><td border=\"0\">\n");
  }

  function end_frame()
  {
    print("</td></tr></table>\n");
  }

	// ---------------------------------------------------------------------------------------------------------
  
  //-------- Inserts a smilies frame
  //         (move to globals)

  function insert_smilies_frame()
  {
    global $smilies, $DEFAULTBASEURL;

    begin_frame("Смайлы", true);

    begin_table(false, 5);

    print("<tr><td class=\"colhead\">Написание</td><td class=\"colhead\">Смайл</td></tr>\n");

    while (list($code, $url) = each($smilies))
      print("<tr><td>$code</td><td><img src=\"$DEFAULTBASEURL/pic/smilies/$url\"></td>\n");

    end_table();

    end_frame();
  }

  // Block menu function
  // Print out menu block!

function blok_menu($title, $content , $width="155") {
	global $ss_uri;
	print('<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td class="block">
	<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
	<td class="block" width="14" align="left"><img src="themes/'.$ss_uri.'/images/cellpic_left.gif" width="14" height="24"></td>
	<td class="block" width="100%" align="center" valign="middle" background="themes/'.$ss_uri.'/images/cellpic3.gif"><nobr><font class="block-title" valign="bottom"><strong>'.$title.'</strong></font></nobr></td>
	<td class="block" width="14" align="right"><img src="themes/'.$ss_uri.'/images/cellpic_right.gif" width="14" height="24"></td>
	</tr></table>
	<table width="100%" border="0" cellspacing="1" cellpadding="3"><tr>
	<td align="left">'.$content.'</td>
	</tr></table>
</td></tr></table><br>');
}

?>