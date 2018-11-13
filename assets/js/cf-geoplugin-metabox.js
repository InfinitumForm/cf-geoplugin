(function($){
    (function($$){
		if($($$))
		{
			$($$).each(function(index, element) {
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
        var tags = section.find('input, label, select, div'), idx = section.index();
        tags.each(function() {
            var $this = $(this);
            $.each(attrs, function(i, attr) {
                var attr_val = $this.attr(attr);
                if (attr_val) {
                    $this.attr(attr, attr_val.replace(/\[\d+\]\[/, '\['+(idx)+'\]\['));
                    if( $this.prop( 'tagName' ) == 'DIV' && attr == 'id' )$this.attr(attr, attr_val.replace(/\_\d+\_/, '\_'+(idx)+'\_'));
                }
            })
        });
    }
    $( document ).on( 'click', 'a.cfgp-repeat', function( e ) {
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
    });


    /**
     * Delete Repeater
     */
    $( document ).on( 'click', 'a.cfgp-destroy-repeat', function( e ) {
        e.preventDefault();
        $( this ).closest( 'tr.repeating' ).remove();
        anchorGroup = $( '.repeating' ).last().find( '.cfgp-add-remove-redirection' );
        if( $( '.repeating' ).last().find( '.cfgp-repeat' ).length <= 0 ) $( anchorGroup ).prepend( '<a class="cfgp-repeat" href="#" title="' + CFGP_META.add_redirection + '"><i class="fa fa-plus-circle fa-2x" style="color: green;"></i></a>' );
    });

    /**
     * Reset first field
     */
    $( 'a.cfgp-reset-fields' ).click( function( e ) {
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