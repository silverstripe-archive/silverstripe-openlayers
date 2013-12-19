
	<div id="mapPanel">
		<div class='panelTop'>
			<span class="arrow"></span>
			<h3 class='layers'>Layers</h3>
		</div>
		<form id='layerlist'>
			<div id="allLayers">
				<a class="selectAllLayers" title="click to show all layers">Show all</a> | <a class="unselectAllLayers" title="click to hide all layers">Hide all</a>
			</div>
			<div id ="innerLayers">
				<!-- Overlay layers -->
				<% loop overlayLayers %>
					<div class="panelItem grip">
						<div class="sortableArea"><img src="openlayers/images/map_grip.png"></div>
						<input type='checkbox' name='$Title' class='change_visibility over' value='$Title' <% if Visible %>checked='checked'<% end_if %> />
						<% if GeometryType = Point %><img class="shapeImage" src="openlayers/images/marker.png"><% end_if %><% if GeometryType = Line %> <img class="shapeImage" src="openlayers/images/layer-shape-polyline.png"><% end_if %><% if GeometryType = Polygon %> <img class="shapeImage" src="openlayers/images/layer-shape-polygon.png"><% end_if %>
						<h5>$Title</h5>
					</div>
				<% end_loop %>
			</div>
			
				<!-- BackgroundLayers layers -->
				<% if backgroundLayers %>
					<div class="panelBg">
						<h3 class="bg">
							Backgrounds
						</h3>
					</div>
				<div id ="backgrounds">
					<% loop backgroundLayers %>
						<div class="panelItem">
							<input type='checkbox' name='$Title' class='change_visibility back' value='$Title' <% if Visible %>checked='checked'<% end_if %> />
							
							<h5 class="bg">$Title</h5>
						</div>
					<% end_loop %>
				</div>
				<% end_if %>
			
		</form>
		<div class="panelBottom"></div>
	</div>

