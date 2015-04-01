(function( $, gp, _ ) {

	if ( ! gp || ! gp.customize ) { return; }
	var api = gp.customize;


	/**
	 * gp.customize.HeaderTool.CurrentView
	 *
	 * Displays the currently selected header image, or a placeholder in lack
	 * thereof.
	 *
	 * Instantiate with model gp.customize.HeaderTool.currentHeader.
	 *
	 * @constructor
	 * @augments gp.Backbone.View
	 */
	api.HeaderTool.CurrentView = gp.Backbone.View.extend({
		template: gp.template('header-current'),

		initialize: function() {
			this.listenTo(this.model, 'change', this.render);
			this.render();
		},

		render: function() {
			this.$el.html(this.template(this.model.toJSON()));
			this.setPlaceholder();
			this.setButtons();
			return this;
		},

		getHeight: function() {
			var image = this.$el.find('img'),
				saved, height, headerImageData;

			if (image.length) {
				this.$el.find('.inner').hide();
			} else {
				this.$el.find('.inner').show();
				return 40;
			}

			saved = this.model.get('savedHeight');
			height = image.height() || saved;

			// happens at ready
			if (!height) {
				headerImageData = api.get().header_image_data;

				if (headerImageData && headerImageData.width && headerImageData.height) {
					// hardcoded container width
					height = 260 / headerImageData.width * headerImageData.height;
				}
				else {
					// fallback for when no image is set
					height = 40;
				}
			}

			return height;
		},

		setPlaceholder: function(_height) {
			var height = _height || this.getHeight();
			this.model.set('savedHeight', height);
			this.$el
				.add(this.$el.find('.placeholder'))
				.height(height);
		},

		setButtons: function() {
			var elements = $('#customize-control-header_image .actions .remove');
			if (this.model.get('choice')) {
				elements.show();
			} else {
				elements.hide();
			}
		}
	});


	/**
	 * gp.customize.HeaderTool.ChoiceView
	 *
	 * Represents a choosable header image, be it user-uploaded,
	 * theme-suggested or a special Randomize choice.
	 *
	 * Takes a gp.customize.HeaderTool.ImageModel.
	 *
	 * Manually changes model gp.customize.HeaderTool.currentHeader via the
	 * `select` method.
	 *
	 * @constructor
	 * @augments gp.Backbone.View
	 */
	api.HeaderTool.ChoiceView = gp.Backbone.View.extend({
		template: gp.template('header-choice'),

		className: 'header-view',

		events: {
			'click .choice,.random': 'select',
			'click .close': 'removeImage'
		},

		initialize: function() {
			var properties = [
				this.model.get('header').url,
				this.model.get('choice')
			];

			this.listenTo(this.model, 'change:selected', this.toggleSelected);

			if (_.contains(properties, api.get().header_image)) {
				api.HeaderTool.currentHeader.set(this.extendedModel());
			}
		},

		render: function() {
			this.$el.html(this.template(this.extendedModel()));

			this.toggleSelected();
			return this;
		},

		toggleSelected: function() {
			this.$el.toggleClass('selected', this.model.get('selected'));
		},

		extendedModel: function() {
			var c = this.model.get('collection');
			return _.extend(this.model.toJSON(), {
				type: c.type
			});
		},

		getHeight: api.HeaderTool.CurrentView.prototype.getHeight,

		setPlaceholder: api.HeaderTool.CurrentView.prototype.setPlaceholder,

		select: function() {
			this.preventJump();
			this.model.save();
			api.HeaderTool.currentHeader.set(this.extendedModel());
		},

		preventJump: function() {
			var container = $('.gp-full-overlay-sidebar-content'),
				scroll = container.scrollTop();

			_.defer(function() {
				container.scrollTop(scroll);
			});
		},

		removeImage: function(e) {
			e.stopPropagation();
			this.model.destroy();
			this.remove();
		}
	});


	/**
	 * gp.customize.HeaderTool.ChoiceListView
	 *
	 * A container for ChoiceViews. These choices should be of one same type:
	 * user-uploaded headers or theme-defined ones.
	 *
	 * Takes a gp.customize.HeaderTool.ChoiceList.
	 *
	 * @constructor
	 * @augments gp.Backbone.View
	 */
	api.HeaderTool.ChoiceListView = gp.Backbone.View.extend({
		initialize: function() {
			this.listenTo(this.collection, 'add', this.addOne);
			this.listenTo(this.collection, 'remove', this.render);
			this.listenTo(this.collection, 'sort', this.render);
			this.listenTo(this.collection, 'change', this.toggleList);
			this.render();
		},

		render: function() {
			this.$el.empty();
			this.collection.each(this.addOne, this);
			this.toggleList();
		},

		addOne: function(choice) {
			var view;
			choice.set({ collection: this.collection });
			view = new api.HeaderTool.ChoiceView({ model: choice });
			this.$el.append(view.render().el);
		},

		toggleList: function() {
			var title = this.$el.parents().prev('.customize-control-title'),
				randomButton = this.$el.find('.random').parent();
			if (this.collection.shouldHideTitle()) {
				title.add(randomButton).hide();
			} else {
				title.add(randomButton).show();
			}
		}
	});


	/**
	 * gp.customize.HeaderTool.CombinedList
	 *
	 * Aggregates gp.customize.HeaderTool.ChoiceList collections (or any
	 * Backbone object, really) and acts as a bus to feed them events.
	 *
	 * @constructor
	 * @augments gp.Backbone.View
	 */
	api.HeaderTool.CombinedList = gp.Backbone.View.extend({
		initialize: function(collections) {
			this.collections = collections;
			this.on('all', this.propagate, this);
		},
		propagate: function(event, arg) {
			_.each(this.collections, function(collection) {
				collection.trigger(event, arg);
			});
		}
	});

})( jQuery, window.gp, _ );
