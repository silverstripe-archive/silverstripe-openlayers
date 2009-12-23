<div id='mapPanelWrapper'>
	<div id="mapPanel">
		<div class='panelTop'>
			<span class="arrow"></span>
			<h3 class='layers'>Layers</h3>
		</div>
		<form id='layerlist'>
			<div id="allLayers">
				<a class="selectAllLayers" title="click to show all layers">Show all layers</a> | <a class="unselectAllLayers" title="click to hide all layers">Hide all layers</a>
			</div>
			<div id ="innerLayers">
				<% control layers %>
		
					<% if ogc_transparent %>
						<div class="panelItem grip">
							<div class="sortableArea"><img src="themes/niwa/images/map_grip.png"></div>
							<input type='checkbox' name='$Title' class='change_visibility' value='$Title' <% if Visible %>checked<% end_if %> />
							<% if GeometryType = Point %><img src="themes/niwa/images/marker.png"><% end_if %><% if GeometryType = Line %> <img src="themes/niwa/images/layer-shape-polyline.png">  <% end_if %>
							<h5><a class="methodLink" id="$MethodID" title="click for more details">$Title</a></h5>
						</div>
					<% end_if %>
		
				<% end_control %>
			</div>
		</form>
		<div class="panelBottom"></div>
	</div>
</div>
