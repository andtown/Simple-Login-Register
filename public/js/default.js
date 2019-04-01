    window.simple = window.simple || {};

    (function($) {

        'use strict';        

        var views = simple.views = simple.views || {},
            models = simple.models = simple.models || {};

        views.loginRegisterForm = simple.views.loginRegisterForm = Backbone.View.extend({            
            initialize : function( options ) {
                Backbone.View.prototype.initialize.call(this, options);
                this._model = (options && options._model) || new Backbone.Model({}); 
                this.params = (options && options.params) || {};
                this.canXHR = simple.canXHR();
                this.listenTo( this._model , 'change', this.modelChange ); 
                this.listenTo( this._model , 'viewChange', this.viewChange );
            },
            events: {
                'click button[type="submit"], input[type="submit"]' : function(e) {
                    if ( !this.params.force_page_refersh && this.canXHR ) {
                        e.preventDefault();
                        this.trigger('click:submit', e);
                    }
                },
                'change input[type="text"], input[type="password"]' : function(e) { 
                    this.trigger('change:input', e);
                }
            },
            render: function() {
                var $this = this;
                if ( typeof this.template == "function" ) this.$el.html(
                    function () {
                       try { 
                           return $this.template($this.params || {});
                       } catch(e) {
                          console.log('failed rendering simple template');                         
                       }
                    }
                );
                return this;
            },
            modelChange: function(e) {
                
            },
            viewChange: function(o, v) {
                this.params.response = v;
                this.template = wp.template(this.params.response.template) || this.template;
                this.render();
            }
        });           

        models.loginRegisterForm = simple.models.loginRegisterForm = simple.models.customModel.extend({            
            initialize: function( attributes, options ) {
                simple.models.customModel.prototype.initialize.call( this, attributes, options );                       
                this._view = new (views.loginRegisterForm.extend({
                //template: wp.template(options.params.response.template),
                el: '#'+options.params.template_container_id
                }))(_.defaults(options,{params: {}, _model: (options && options._model) || this}));
                this.listenTo(this._view, 'click:submit', this.submit);
                this.listenTo(this._view, 'change:input', this.inputChange);
                if ( options.params.response ) {                     
                    this.parse(options.params.response,options);
                }
            },
            submit: function(e) {
                //this.sync('create',this);
                this.save();
            },
            inputChange: function(e) {
                var target = e.target || e.srcElement;
                this.set(target.name,target.value);
            },
            parse: function( response, options ) {
                var $this = this;
                _.each(response, function(v,k) {
                    $this.set(k,v);
                });
                this.trigger('viewChange',this,response);   
            },            
            destroy: function( options ) {        
                Backbone.Model.prototype.destroy.call( this, options );
            }
        });

    })(jQuery);