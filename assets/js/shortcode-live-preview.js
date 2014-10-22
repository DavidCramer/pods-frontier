jQuery(function($){

	var media = wp.media;
	wp.mce.views.register( 'pods', {
		View: {
			template: media.template( 'pods-live-shortcode-preview' ),

			initialize: function( options ) {
				this.shortcode = options.shortcode;
				this.fetch();
			},
			loadingPlaceholder: function() {
				return '' +
					'<div class="loading-placeholder">' +
						'<div class="" style="margin: 0px auto; background: url(' + $('#frontier_logo').val() +') no-repeat scroll center center / 34px auto transparent; height: 35px; width: 35px;"></div>' +
						'<div class="wpview-loading"><ins></ins></div>' +
					'</div>';
			},
			fetch: function() {
				var self = this;
				
				options = {};
				options.context = this;
				options.data = {
					action:  'pods_shortcode_live_preview',
					post_id: $('#post_ID').val(),
					raw: this.encodedText
				};

				this.html = media.ajax( options );
				this.dfd = this.html.done( function(form) {
					this.html.data = form;
					self.render( true );
				} );
			},
			getHtml: function() {
				var attrs = this.shortcode.attrs.named,
					attachments = false,
					options;

				// Don't render errors while still fetching content
				if ( this.dfd && 'pending' === this.dfd.state() && ! this.html.length ) {
					return '';
				}

				return this.template( this.html.data );
			}
		}
	});


});//
