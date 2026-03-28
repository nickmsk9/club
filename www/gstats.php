<?php
    require_once("include/bittorrent.php"); 
    dbconn(); 
    stdhead("Статистика трекера");
	begin_main_frame();
	?>	
<script type="text/javascript" src="google/swf/swfobject.js"></script>

<table style="width:100%">
 <tr>
    <td colspan="2">
	    <div id="visitors_3" align="center" style="padding-bottom:80px"><strong>Для просмотра сожержимого, установите последнюю версию Adobe Flash Player</strong></div>
	    <script type="text/javascript">
	    // <![CDATA[
	    var so = new SWFObject("google/swf/amline.swf", "google/swf", "815", "400", "8", "#FFFFFF");
	    so.addVariable("path", "./google/swf/");
	    so.addVariable("settings_file", escape("google/settings/visitors_3_settings.xml?<?php echo mktime();?>"));
	    so.addVariable("data_file", escape("google/csv/visitors_3.csv?<?php echo mktime();?>"));
	    so.addVariable("preloader_color", "#BBBBBB");
	    so.write("visitors_3");
	    // ]]>
        </script>
	</td>
 </tr>
<!-- <tr>
	<td colspan="2">
	
	    <div id="visitors" align="center" style="padding-bottom:80px"><strong>Для просмотра сожержимого, установите последнюю версию Adobe Flash Player</strong></div>
        <script type="text/javascript">
	    // <![CDATA[
	    var so = new SWFObject("google/swf/amline.swf", "google/swf/amline_chart", "815", "400", "8", "#FFFFFF");
	    so.addVariable("path", "./amline/");
	    so.addVariable("settings_file", escape("google/settings/visitors_settings.xml?<?php echo mktime();?>"));
	    so.addVariable("data_file", escape("google/csv/visitors.csv?<?php echo mktime();?>"));
	    so.addVariable("preloader_color", "#BBBBBB");
	    so.write("visitors");
	    // ]]>
        </script>

	</td>
 </tr>-->
 <tr>
    <td>
	    <div id="country" align="center" style="padding-bottom:80px"><strong>Для просмотра сожержимого, установите последнюю версию Adobe Flash Player</strong></div>
        <script type="text/javascript">
	    // <![CDATA[
	    var so = new SWFObject("google/swf/ampie.swf", "google/swf", "407", "500", "8", "#FFFFFF");
	    so.addVariable("path", "./google/swf/");
	    so.addVariable("settings_file", escape("google/settings/country_settings.xml?<?php echo mktime();?>"));
	    so.addVariable("data_file", escape("google/csv/country.csv?<?php echo mktime();?>"));
	    so.addVariable("preloader_color", "#BBBBBB");
	    so.write("country");
	    // ]]>
        </script>
	</td>
	<td>
	    <div id="city" align="center" style="padding-bottom:80px"><strong>Для просмотра сожержимого, установите последнюю версию Adobe Flash Player</strong></div>
        <script type="text/javascript">
	    // <![CDATA[
	    var so = new SWFObject("google/swf/ampie.swf", "google/swf", "407", "500", "8", "#FFFFFF");
	    so.addVariable("path", "./google/swf/");
	    so.addVariable("settings_file", escape("google/settings/city_settings.xml?<?php echo mktime();?>"));
	    so.addVariable("data_file", escape("google/csv/city.csv?<?php echo mktime();?>"));
	    so.addVariable("preloader_color", "#BBBBBB");
	    so.write("city");
	    // ]]>
        </script>
	</td>
 </tr>
 <tr>
    <td>
	    <div id="browser" align="center" style="padding-bottom:80px"><strong>Для просмотра сожержимого, установите последнюю версию Adobe Flash Player</strong></div>
        <script type="text/javascript">
	    // <![CDATA[
	    var so = new SWFObject("google/swf/ampie.swf", "google/swf", "407", "500", "8", "#FFFFFF");
	    so.addVariable("path", "./google/swf/");
	    so.addVariable("settings_file", escape("google/settings/browser_settings.xml?<?php echo mktime();?>"));
	    so.addVariable("data_file", escape("google/csv/browser.csv?<?php echo mktime();?>"));
	    so.addVariable("preloader_color", "#BBBBBB");
	    so.write("browser");
	    // ]]>
        </script>	     
	</td>
	<td>
	    <div id="os" align="center" style="padding-bottom:80px"><strong>Для просмотра сожержимого, установите последнюю версию Adobe Flash Player</strong></div>
        <script type="text/javascript">
	    // <![CDATA[
	    var so = new SWFObject("google/swf/ampie.swf", "google/swf", "407", "500", "8", "#FFFFFF");
	    so.addVariable("path", "./google/swf/");
	    so.addVariable("settings_file", escape("google/settings/os_settings.xml?<?php echo mktime();?>"));
	    so.addVariable("data_file", escape("google/csv/os.csv?<?php echo mktime();?>"));
	    so.addVariable("preloader_color", "#BBBBBB");
	    so.write("os");
	    // ]]>
        </script>	
	</td>
 </tr>
 <tr>
    <td colspan="2">
	    <div id="resolution" align="center" style="padding-bottom:80px"><strong>Для просмотра сожержимого, установите последнюю версию Adobe Flash Player</strong></div>        	
		<script type="text/javascript">
	    // <![CDATA[
	    var so = new SWFObject("google/swf/ampie.swf", "google/swf", "815", "500", "8", "#FFFFFF");
	    so.addVariable("path", "./google/swf/");
	    so.addVariable("settings_file", escape("google/settings/resolution_settings.xml?<?php echo mktime();?>"));
	    so.addVariable("data_file", escape("google/csv/resolution.csv?<?php echo mktime();?>"));
	    so.addVariable("preloader_color", "#BBBBBB");
	    so.write("resolution");
	    // ]]>
        </script>
	</td>
 </tr>
</table>
<?php 
end_main_frame();
stdfoot(); ?>