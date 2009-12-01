<div id='layersMenu'>
<h2>Available layers</h2>
<form id='layerlist'>
	<ul>
		<li>
			<a class="selectAllLayers">Select all layers</a> | <a class="unselectAllLayers">Unselect all layers</a>
			
		</li>
	<% control layers %>
		<% if ogc_transparent %>
			<li>
				<span class='layervisible'>
					<input type='checkbox' name='$Name' class='change_visibility' value='$Name' <% if Visible %>checked<% end_if %> />
				</span>
				$Name
			</li>
		<% end_if %>
	<% end_control %>
	</ul>
</form>
</div>
