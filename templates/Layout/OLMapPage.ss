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

      <ul>
        <li>Streams: Feature Count <span id="stream_features">0</span></li>
        <li>Plat: Feature Count <span id="plat_features">0</span></li>
        <li>Roads: Feature Count <span id="road_features">0</span></li>
      </ul>
		$Content
		$Form
		$PageComments
	<% if Menu(2) %>
		</div>
	<% end_if %>

</div>
