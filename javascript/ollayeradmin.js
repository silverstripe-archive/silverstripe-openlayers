(function($) {
	$(document).ready(function() {
		
		$('.describeFeatureType').livequery('click', function() {

			var LayerID = $('#Form_EditForm_ID').val();
			var url = 'admin/openlayers/OLLayer/' + LayerID + '/describeFeature?SecurityID=' + $('#Form_EditForm_SecurityID').val();
			var self = $(this);

			$.ajax({ 
				url: url ,
				success: function( data ){
					var obj = $("#featureTypeAttributes");
					obj[0].innerHTML='<strong>Available attributes</strong><br/><ul>';
					
					var ar = eval(data);
					$.each(ar, function( index, value) {
						obj[0].innerHTML += "<li style='padding-left: 20px'>"+value+"</li>";
					});
					obj[0].innerHTML+='</ul>';
				},
				error: function(){
					statusMessage('There was an error, please try again', 'bad');
				}
			});
			return false;
		});
	});

})(jQuery);