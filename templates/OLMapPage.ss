<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

  <head>
		<% base_tag %>
		<title>$Title &raquo; Your Site Name</title>
		$MetaTags(false)
		<link rel="shortcut icon" href="/favicon.ico" />
		<link href="$ThemeDir/css/blueprint/screen.css" type="text/css" rel="stylesheet">
		<% require themedCSS(layout) %> 
		<% require themedCSS(typography) %> 
		<% require themedCSS(form) %> 
		
		<!--[if IE 6]>
			<style type="text/css">
			 @import url(themes/blackcandy/css/ie6.css);
			</style> 
		<![endif]-->
		
		<!--[if IE 7]>
			<style type="text/css">
			 @import url(themes/blackcandy/css/ie7.css);
			</style> 
		<![endif]-->
	</head>
<body>
<div class="container">
	<div id="header" class="span-24 last">
	  <h1>Ocean Survey 20/20 Projects </h1>
		<div id="nav">
			<% include Navigation %>
			<!-- <ul>
				<li><a href="">About OS 20/20</a></li>
				<li><a href="surveys.html">Surveys</a></li>
				<li><a href="map.html">Interactive map</a></li>
				<li><a href="http://74.50.54.220/geonetwork/srv/en/main.home">Search Data</a></li>
				<li><a href="">Contact</a></li>
			</ul> -->
		</div>
	</div>
	<div id="Layout">
	  $Layout
	</div>	
	<div id="Footer">
		<% include Footer %>
	</div>
</div>
</body>
</html>
