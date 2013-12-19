// register style: $Name
var layerstyle = new OpenLayers.StyleMap({
    "default": $Default.RAW <% if Select.RAW %>,
    "select": $Select.RAW <% end_if %><% if Temporary.RAW %>,
	"temporary" : $Temporary.RAW<% end_if %>
});

OLStyleFactory.addStyle("$Name", layerstyle);