(function ($) {
	var debounce,
		loader = '<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> ' + CFGP.label.loading;
		
	/* Select all */
	$(document).on('click', '.cfgp-select-all', function( e ){
		e.preventDefault();
		var $this = $(this),
			$target = $( '#' + $this.attr('data-target') );
		$target.find('option').each(function(){
			var $option = $(this);
			if($option.is(':selected')) {
				$(this).prop('selected',false);
			} else {
				$(this).prop('selected',true);
			}
		}).promise().done(function(){
			$target.trigger('chosen:updated');
		});
	});
	
	/*
	 * Chosen initialization
	 * @since 7.0.0
	*/
	(function($$){
		if( $($$) )
		{
			$($$).each(function(index, element) {
				$(this).chosen({
					no_results_text: CFGP.label.chosen.not_found,
					width: "100%",
					search_contains:true
				});
			});
		}
	}('.chosen-select'));
	
	
	$(document)
	// Add SEO redirection
	.on('click', '.cfgp-add-seo-redirection', function(e){
		e.preventDefault();
		var $this = $(this),
			$item = $this.closest('.cfgp-repeater-item'),
			$repeater  = $item.closest('.cfgp-repeater'),
			$template = $repeater.find('.cfgp-repeater-item:first-child').html(),
			$index = $repeater.find('.cfgp-repeater-item').length;
			
		$template = $template.replace(/(\[0\])/gi, '[' + $index + ']');
		$template = $template.replace(/(-0-)/gi, '-' + $index + '-');
		
		console.log($index);
		
		var $html = $('<div/>').addClass('cfgp-row cfgp-repeater-item').html($template);
		$html.find('select option:selected').removeAttr('selected').prop('selected', false);
		$html.find('input[type="url"], input[type="text"]').val('');
		
		/* TODO - Reset chosen */
			
		$repeater.append($html);
	})
	// Remove SEO redirection
	.on('click', '.cfgp-remove-seo-redirection', function(e){
		e.preventDefault();
		var $this = $(this),
			$item = $this.closest('.cfgp-repeater-item');
		$item.remove();
	});
		
})(jQuery || window.jQuery || Zepto || window.Zepto);