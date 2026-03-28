jQuery(function() {
jQuery(".vote").click(function() 
{

var id = jQuery(this).attr("id");
var name = jQuery(this).attr("name");
var dataString = 'id='+ id ;
var parent = jQuery(this);

if(name=='up')
{

jQuery(this).fadeIn(200).html('<img src="./pic/dot.gif" align="absmiddle">');
jQuery.ajax({
   type: "POST",
   url: "voting.php?act=up",
   data: dataString,
   cache: false,

   success: function(html)
   {
    parent.html(html);
  
  }  });
  
}
else
{

jQuery(this).fadeIn(200).html('<img src="./pic/dot.gif" align="absmiddle">');
jQuery.ajax({
   type: "POST",
   url: "voting.php?act=down",
   data: dataString,
   cache: false,

   success: function(html)
   {
       parent.html(html);
  }
   
 });

}
return false;
});
});