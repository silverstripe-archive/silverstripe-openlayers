<h4 class="popup">There are $stations.count points</h4>
<h5 class="popup">Please select one from the list below</h5>
<ul>
	<% loop stations %>
		<li><a class="popupLink" onClick="multipleStationSelect('$Station');return false">$Station</a></li>
	<%  end_loop %>
</ul>