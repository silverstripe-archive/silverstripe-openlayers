<div id='layersMenu'>
<h2>Available datasets</h2>
<form id='layerlist'>
	<ul>
	<% control layers %>
		<% if ogc_transparent %>
			<li>
				<span class='layerquery'>
					<% if Queryable %>
						<input type='radio' name='query_layer' class='query_layer' value='$Name' checked />
					<% else %>
						&nbsp;
					<% end_if %>
				</span>
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
