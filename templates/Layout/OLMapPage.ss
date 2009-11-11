<div class="typography">
	<% if Menu(2) %>
		<% include SideBar %>
		<div id="Content">
	<% end_if %>
			
	<% if Level(2) %>
	  	<% include BreadCrumbs %>
	<% end_if %>
	
		<h2>$Title</h2>
		TestMap:
		<hr />
		<div style="width:760px; height:500px" id="map"></div>
    	<hr />

		$FormLayerSwitcher
		<form>
		<ul>
			<li><input type="radio" name="query_layer" value="Beam trawl stations" class="query_layer"/><input type="checkbox" name="layers" value="beam" />Beam Trawl Stations</li>
			<li><input type="radio" name="query_layer" value="Dredge stations" class="query_layer"/><input type="checkbox" name="layers" value="dredge" />Dredge Stations</li>
		</ul>
		</form>

		$Content
		$Form
		$PageComments
	<% if Menu(2) %>
		</div>
	<% end_if %>

</div>
