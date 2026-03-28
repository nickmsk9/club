/*
*
*	Simple AJAX Loader for RIB
*
*/

path_to_rib = './rib/rib.php';

function mainGetPage(addr, post_data)
{

	var mainreq;

	if(window.XMLHttpRequest)
	{
		// code for IE7+, Firefox, Chrome, Opera, Safari
		mainreq = new XMLHttpRequest();
	}
	else if (window.ActiveXObject)
	{
		// code for IE6, IE5
		mainreq = new ActiveXObject("Microsoft.XMLHTTP");
	}
	else
	{
		//alert("Your browser does not support XMLHTTP!");
		document.getElementById("rib_block").innerHTML = '<div style="color: red; text-align: center; font-weight: bold;">Your browser does not support XMLHTTP!</div>';
	}



	mainreq.onreadystatechange = function handleStatus()
	{
		if(mainreq.readyState==4)
		{
			document.getElementById("rib_block").innerHTML = mainreq.responseText;
		}
	}

	if(post_data == null)
		mainreq.open("GET", addr, true);
	else
		mainreq.open("POST", addr, true);
	mainreq.send(post_data);
}

function RefreshRIB() 
{
	mainGetPage(path_to_rib, null)
	setInterval('mainGetPage(path_to_rib, null)',30000);
}
