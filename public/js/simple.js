    window.simple = window.simple || {};

    (function($) {

        'use strict';

        var models = simple.models = simple.models || {},     
            empty = simple.empty = simple.empty || {},
            canXHR = simple.canXHR;

        models.customModel = simple.models.customModel = Backbone.Model.extend({

            sync: function( method, object, options ) {
                options = options || {};
                if ( 'read' === method ) 
                    return Backbone.sync( method, object, options );
                var formattedJSON = this.toJSON();
                options.data = _.extend( options.data || {}, formattedJSON );
                options.emulateHTTP = true;
                options.emulateJSON = true;
                return Backbone.sync.call( this, 'create', object, options );          
            }

        });

        empty = simple.empty = function(d) {
            return ( "undefined" === typeof d || null === d || 0 === d || '' == d  );
        }

        canXHR = simple.canXHR = function() {
            return ( 
                ("boolean" === typeof jQuery.support.ajax && jQuery.support.ajax) || 
                ("undefined" !== typeof XMLHttpRequest) ||
                !empty( (function(obj) { 
                    for (var i = 0; i < obj.length; i++) {
                        try {
                            var test = new ActiveXObject(obj[i]);
                            test = null;
                            return obj[i];
                        } catch (e) {}
                    }
                    //throw Error("Browser doesn't supports AJAX"); 
                    console.log("Browser doesn't supports AJAX");
                    return 0;            
                })(["MSXML2.XMLHTTP.6.0", "MSXML2.XMLHTTP.3.0", "MSXML2.XMLHTTP", "Microsoft.XMLHTTP"]))
            );  
        }        

    })(jQuery);