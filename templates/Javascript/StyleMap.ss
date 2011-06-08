// register style: $Name
var layerstyle = new OpenLayers.StyleMap({
    "default": $Default.RAW,
    "select": $Select.RAW,
	"temporary" : $Temporary.RAW
});

OLStyleFactory.addStyle("$Name", layerstyle);