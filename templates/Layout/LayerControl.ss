<div id='layersMenu'>
<h2>Available layers</h2>
<form id='layerlist'>
	<ul>
		<li>
			<a class="selectAllLayers">Select all layers</a> | <a class="unselectAllLayers">Unselect all layers</a>
		</li>
	<div id ="innerLayers">
	<% control layers %>
		
		<% if ogc_transparent %>
			<li id="$Title">
				<span class='layervisible'>
					<input type='checkbox' name='$Title' class='change_visibility' value='$Title' <% if Visible %>checked<% end_if %> />
				</span>
				$Title
			</li>
		<% end_if %>
		
	<% end_control %>
	</div>
	</ul>
</form>
</div>