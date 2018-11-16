(function($){
	
	var INDEX_MAX = $( '.repeating' ).length;
	var INDEX = 0;
	
	for(INDEX = 0; INDEX<INDEX_MAX; INDEX++)
	{
		$( $( '.repeating' ).get(INDEX) ).attr('data-index', INDEX);
	}
	
	INDEX = INDEX-1;
	
    (function($$){
		if($($$))
		{
			$($$).each(function() {
				$(this).chosen({
					no_results_text: CFGP_META.no_result,
					width: "100%",
					search_contains:true
				});
			});
		}
    }('.chosen-select'));
    
    /**
     * Repeater
     */
    var attrs = ['for', 'id', 'name'];
    function resetAttributeNames(section) 
    { 
        var tags = section.find('input, label, select, div');
		INDEX++;
        tags.each(function() {
            var $this = $(this);
            $.each(attrs, function(i, attr) {
				
                var attr_val = $this.attr(attr);
                if (attr_val) {
                    $this.attr(attr, attr_val.replace(/\[\d+\]\[/, '\['+(INDEX)+'\]\['));
                    if( $this.prop( 'tagName' ) == 'DIV' && attr == 'id' ) $this.attr(attr, attr_val.replace(/\_\d+\_/, '\_'+(INDEX)+'\_'));
                }
            })
        });
    }
	
    $( document ).on( 'click touchstart', 'a.cfgp-repeat', function( e ) {
        e.preventDefault();
		
        var lastRepeatingGroup = $( '.repeating' ).last();
        // Very important step. Before cloning we must destroy original chosen and after re-initialize it again
        lastRepeatingGroup.find('.chosen-select').each(function () {
            $(this).chosen( 'destroy' );
        });

        var lastRepeatingGroup_radio = lastRepeatingGroup.find( 'input[type=radio]:checked' ).val();

        var cloned = lastRepeatingGroup.clone(true); 
        lastRepeatingGroup.find('.chosen-select').each(function () {
            $(this).chosen();
        });

        cloned.insertAfter(lastRepeatingGroup);
        lastRepeatingGroup.find( 'a.cfgp-repeat' ).remove();
        resetAttributeNames(cloned);
        if( lastRepeatingGroup.find( 'a.cfgp-reset-fields' ) )
        {
            cloned.find( 'a.cfgp-reset-fields' ).replaceWith( '<a class="cfgp-destroy-repeat" href="#" title="' + CFGP_META.remove_redirection + '"><i class="fa fa-minus-circle fa-2x" style="color: red;"></i></a>' ).trigger( 'change' );
        }
        cloned.find( "input[type=text]" ).val( "" );
        cloned.find( ".http_select" ).val( "302" );
        cloned.find( "input[type=radio][value=0]" ).prop( 'checked', true );
        cloned.find( ".chosen-select" ).each( function() {
            $(this).chosen({
					no_results_text: CFGP_META.no_result,
					width: "100%",
					search_contains:true
			}).val( '' ).trigger( 'chosen:updated' );
        }); 
        lastRepeatingGroup.find( 'input[type=radio][value=' + lastRepeatingGroup_radio + ']' ).prop( 'checked', true );
		$( '.repeating' ).last().attr('data-index', INDEX);
		
		
		var firstElementOnly = $( '.repeating' ).get(0);
		
		if($( '.repeating' ).length === 1){
			$( firstElementOnly ).find('.cfgp-add-remove-redirection > .cfgp-reset-fields').remove();
			$( firstElementOnly ).find('.cfgp-add-remove-redirection').append('<a class="cfgp-reset-fields" href="#" title="' + CFGP_META.reset_redirection + '"><i class="fa fa-repeat fa-2x" style="color: red;"></i></a>');
			$( firstElementOnly ).find('.cfgp-add-remove-redirection > .cfgp-destroy-repeat').remove();
		} else {
			$( firstElementOnly ).find('.cfgp-add-remove-redirection > .cfgp-destroy-repeat').remove();
			$( firstElementOnly ).find('.cfgp-add-remove-redirection').append('<a class="cfgp-destroy-repeat" href="#" title="' + CFGP_META.remove_redirection + '"><i class="fa fa-minus-circle fa-2x" style="color: red;"></i></a>');
			$( firstElementOnly ).find('.cfgp-add-remove-redirection > .cfgp-reset-fields').remove();
		}
    });


    /**
     * Delete Repeater
     */
    $( document ).on( 'click touchstart', 'a.cfgp-destroy-repeat', function( e ) {
        e.preventDefault();
        $( this ).closest( 'tr.repeating' ).remove();
        anchorGroup = $( '.repeating' ).last().find( '.cfgp-add-remove-redirection' );
        if( $( '.repeating' ).last().find( '.cfgp-repeat' ).length <= 0 ) $( anchorGroup ).prepend( '<a class="cfgp-repeat" href="#" title="' + CFGP_META.add_redirection + '"><i class="fa fa-plus-circle fa-2x" style="color: green;"></i></a>' );
		
		var firstElementOnly = $( '.repeating' ).get(0);
		
		if($( '.repeating' ).length === 1){
			$( firstElementOnly ).find('.cfgp-add-remove-redirection > .cfgp-reset-fields').remove();
			$( firstElementOnly ).find('.cfgp-add-remove-redirection').append('<a class="cfgp-reset-fields" href="#" title="' + CFGP_META.reset_redirection + '"><i class="fa fa-repeat fa-2x" style="color: red;"></i></a>');
			$( firstElementOnly ).find('.cfgp-add-remove-redirection > .cfgp-destroy-repeat').remove();
		} else {
			$( firstElementOnly ).find('.cfgp-add-remove-redirection > .cfgp-destroy-repeat').remove();
			$( firstElementOnly ).find('.cfgp-add-remove-redirection').append('<a class="cfgp-destroy-repeat" href="#" title="' + CFGP_META.remove_redirection + '"><i class="fa fa-minus-circle fa-2x" style="color: red;"></i></a>');
			$( firstElementOnly ).find('.cfgp-add-remove-redirection > .cfgp-reset-fields').remove();
		}
    });

    /**
     * Reset first field
     */
    $( document ).on('click touchstart', 'a.cfgp-reset-fields', function( e ) {
        e.preventDefault();
        reset = $( '.repeating' ).first();
        reset.find( '.chosen-select' ).each( function() {
            $(this).chosen({
                no_results_text: CFGP_META.no_result,
                width: "100%",
                search_contains:true
            }).val( '' ).prop('checked', false).prop('selected', false).trigger( 'chosen:updated' );
        });
        reset.find( "input[type=text]" ).val("").prop('checked', false).prop('selected', false);
        reset.find( ".http_select" ).val( "302" ).prop('checked', false).prop('selected', false);
        reset.find( "input[type=radio][value=0]" ).prop( 'checked', true );
    });

})(jQuery || window.jQuery || Zepto || window.Zepto);