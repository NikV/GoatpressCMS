/*globals gp, _, Backbone */

/**
 * gp.media.view.MediaFrame.Manage
 *
 * A generic management frame workflow.
 *
 * Used in the media grid view.
 *
 * @class
 * @augments gp.media.view.MediaFrame
 * @augments gp.media.view.Frame
 * @augments gp.media.View
 * @augments gp.Backbone.View
 * @augments Backbone.View
 * @mixes gp.media.controller.StateMachine
 */
var MediaFrame = gp.media.view.MediaFrame,
	Library = gp.media.controller.Library,

	$ = Backbone.$,
	Manage;

Manage = MediaFrame.extend({
	/**
	 * @global gp.Uploader
	 */
	initialize: function() {
		_.defaults( this.options, {
			title:     '',
			modal:     false,
			selection: [],
			library:   {}, // Options hash for the query to the media library.
			multiple:  'add',
			state:     'library',
			uploader:  true,
			mode:      [ 'grid', 'edit' ]
		});

		this.$body = $( document.body );
		this.$window = $( window );
		this.$adminBar = $( '#gpadminbar' );
		this.$window.on( 'scroll resize', _.debounce( _.bind( this.fixPosition, this ), 15 ) );
		$( document ).on( 'click', '.add-new-h2', _.bind( this.addNewClickHandler, this ) );

		// Ensure core and media grid view UI is enabled.
		this.$el.addClass('gp-core-ui');

		// Force the uploader off if the upload limit has been exceeded or
		// if the browser isn't supported.
		if ( gp.Uploader.limitExceeded || ! gp.Uploader.browser.supported ) {
			this.options.uploader = false;
		}

		// Initialize a window-wide uploader.
		if ( this.options.uploader ) {
			this.uploader = new gp.media.view.UploaderWindow({
				controller: this,
				uploader: {
					dropzone:  document.body,
					container: document.body
				}
			}).render();
			this.uploader.ready();
			$('body').append( this.uploader.el );

			this.options.uploader = false;
		}

		this.gridRouter = new gp.media.view.MediaFrame.Manage.Router();

		// Call 'initialize' directly on the parent class.
		MediaFrame.prototype.initialize.apply( this, arguments );

		// Append the frame view directly the supplied container.
		this.$el.appendTo( this.options.container );

		this.createStates();
		this.bindRegionModeHandlers();
		this.render();
		this.bindSearchHandler();
	},

	bindSearchHandler: function() {
		var search = this.$( '#media-search-input' ),
			currentSearch = this.options.container.data( 'search' ),
			searchView = this.browserView.toolbar.get( 'search' ).$el,
			listMode = this.$( '.view-list' ),

			input  = _.debounce( function (e) {
				var val = $( e.currentTarget ).val(),
					url = '';

				if ( val ) {
					url += '?search=' + val;
				}
				this.gridRouter.navigate( this.gridRouter.baseUrl( url ) );
			}, 1000 );

		// Update the URL when entering search string (at most once per second)
		search.on( 'input', _.bind( input, this ) );
		searchView.val( currentSearch ).trigger( 'input' );

		this.gridRouter.on( 'route:search', function () {
			var href = window.location.href;
			if ( href.indexOf( 'mode=' ) > -1 ) {
				href = href.replace( /mode=[^&]+/g, 'mode=list' );
			} else {
				href += href.indexOf( '?' ) > -1 ? '&mode=list' : '?mode=list';
			}
			href = href.replace( 'search=', 's=' );
			listMode.prop( 'href', href );
		} );
	},

	/**
	 * Create the default states for the frame.
	 */
	createStates: function() {
		var options = this.options;

		if ( this.options.states ) {
			return;
		}

		// Add the default states.
		this.states.add([
			new Library({
				library:            gp.media.query( options.library ),
				multiple:           options.multiple,
				title:              options.title,
				content:            'browse',
				toolbar:            'select',
				contentUserSetting: false,
				filterable:         'all',
				autoSelect:         false
			})
		]);
	},

	/**
	 * Bind region mode activation events to proper handlers.
	 */
	bindRegionModeHandlers: function() {
		this.on( 'content:create:browse', this.browseContent, this );

		// Handle a frame-level event for editing an attachment.
		this.on( 'edit:attachment', this.openEditAttachmentModal, this );

		this.on( 'select:activate', this.bindKeydown, this );
		this.on( 'select:deactivate', this.unbindKeydown, this );
	},

	handleKeydown: function( e ) {
		if ( 27 === e.which ) {
			e.preventDefault();
			this.deactivateMode( 'select' ).activateMode( 'edit' );
		}
	},

	bindKeydown: function() {
		this.$body.on( 'keydown.select', _.bind( this.handleKeydown, this ) );
	},

	unbindKeydown: function() {
		this.$body.off( 'keydown.select' );
	},

	fixPosition: function() {
		var $browser, $toolbar;
		if ( ! this.isModeActive( 'select' ) ) {
			return;
		}

		$browser = this.$('.attachments-browser');
		$toolbar = $browser.find('.media-toolbar');

		// Offset doesn't appear to take top margin into account, hence +16
		if ( ( $browser.offset().top + 16 ) < this.$window.scrollTop() + this.$adminBar.height() ) {
			$browser.addClass( 'fixed' );
			$toolbar.css('width', $browser.width() + 'px');
		} else {
			$browser.removeClass( 'fixed' );
			$toolbar.css('width', '');
		}
	},

	/**
	 * Click handler for the `Add New` button.
	 */
	addNewClickHandler: function( event ) {
		event.preventDefault();
		this.trigger( 'toggle:upload:attachment' );
	},

	/**
	 * Open the Edit Attachment modal.
	 */
	openEditAttachmentModal: function( model ) {
		// Create a new EditAttachment frame, passing along the library and the attachment model.
		gp.media( {
			frame:       'edit-attachments',
			controller:  this,
			library:     this.state().get('library'),
			model:       model
		} );
	},

	/**
	 * Create an attachments browser view within the content region.
	 *
	 * @param {Object} contentRegion Basic object with a `view` property, which
	 *                               should be set with the proper region view.
	 * @this gp.media.controller.Region
	 */
	browseContent: function( contentRegion ) {
		var state = this.state();

		// Browse our library of attachments.
		this.browserView = contentRegion.view = new gp.media.view.AttachmentsBrowser({
			controller: this,
			collection: state.get('library'),
			selection:  state.get('selection'),
			model:      state,
			sortable:   state.get('sortable'),
			search:     state.get('searchable'),
			filters:    state.get('filterable'),
			date:       state.get('date'),
			display:    state.get('displaySettings'),
			dragInfo:   state.get('dragInfo'),
			sidebar:    'errors',

			suggestedWidth:  state.get('suggestedWidth'),
			suggestedHeight: state.get('suggestedHeight'),

			AttachmentView: state.get('AttachmentView'),

			scrollElement: document
		});
		this.browserView.on( 'ready', _.bind( this.bindDeferred, this ) );

		this.errors = gp.Uploader.errors;
		this.errors.on( 'add remove reset', this.sidebarVisibility, this );
	},

	sidebarVisibility: function() {
		this.browserView.$( '.media-sidebar' ).toggle( !! this.errors.length );
	},

	bindDeferred: function() {
		if ( ! this.browserView.dfd ) {
			return;
		}
		this.browserView.dfd.done( _.bind( this.startHistory, this ) );
	},

	startHistory: function() {
		// Verify pushState support and activate
		if ( window.history && window.history.pushState ) {
			Backbone.history.start( {
				root: window._gpMediaGridSettings.adminUrl,
				pushState: true
			} );
		}
	}
});

module.exports = Manage;
