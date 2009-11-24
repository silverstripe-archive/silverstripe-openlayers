<div id='layersMenu'>
<h2>Available datasets</h2>
<form id='layerlist'>
	<ul>
	<% control layers %>
		<% if ogc_transparent %>
			<li>
				<span class='layervisible'>
					<input type='checkbox' name='change_visibility' class='change_visibility' value='$Name' <% if Visible %>checked<% end_if %> />
				</span>
				$Name
			</li>
		<% end_if %>
	<% end_control %>
	</ul>
</form>
</div>
