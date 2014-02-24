(function($) {

    $.entwine('openlayers', function($) {

        $('#Form_ItemEditForm_UseTemplateForPopupWindow').entwine({

            onmatch: function() {
                this.updateForm();
            },

            onclick: function() {
                this.updateForm();
            },

            updateForm: function() {
                var checked = $(this)[0].checked;

                var divAttributes = $('.divAttributesTemplate');
                var divTemplate   = $('.divPopupTemplate');

                if (checked == true) {
                    divAttributes.hide();
                    divTemplate.show();
                } else {
                    divAttributes.show();
                    divTemplate.hide();
                }
            }

        }),

        $('.describeFeatureType').entwine({
                onclick: function() {
                    var item = $('.describeFeatureType');
                    var layerID = $(item).data('id')
                    var url = 'admin/wfs/describeFeature/' + layerID;

                    $.ajax({
                        url: url ,
                        success: function( data ){
                            var obj = $("#featureTypeAttributes");
                            obj[0].innerHTML='<strong>Result:</strong><br/><ul>';

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
                }

        });
    });
})(jQuery);