<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" style="overflow:hidden" >

  <head>
		<% base_tag %>
		<title>$Title &raquo; Your Site Name</title>
		$MetaTags(false)
		<link rel="shortcut icon" href="/favicon.ico" />
	
		<% require themedCSS(typography) %> 
		<% require themedCSS(layout) %> 
		<% require themedCSS(form) %> 
		
	</head>
<body class="mapPage">

	<div id="Header">
		<h1>&nbsp;</h1>
		<div id="Menu1">
			<ul>
			 	<% loop Menu(1) %>
			  		<li><a href="$Link" title="Go to the $Title.XML page" class="$LinkingMode"><span>$MenuTitle.XML</span></a></li>
			   	<% end_loop %>
			 </ul>
		</div>
	</div>
	<div id="Layout">
	  $Layout
	</div>	

</body>
</html>