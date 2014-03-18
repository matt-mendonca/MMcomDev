window.App = Ember.Application.create({});

App.Router.reopen({
   location: 'history'
});

App.LazyLoaderMixin = Ember.Mixin.create({

  beforeModel: function(){
    var scriptName = 'js/'+this.get('routeName')+'.js';
    if (!App.LazyLoaderMixin.loaded[scriptName]) {
      return $.getScript(scriptName).then(function(){
        App.LazyLoaderMixin.loaded[scriptName] = true;
      });
    }
  }

});


Ember.TextSupport.reopen({  
    attributeBindings: ["required"]  
});

Ember.LinkView.reopen({
  attributeBindings: ['data-sub-menu']
});

Ember.View.reopen({
    willInsertElement : function() {
        $(window).trigger('ember-dom-will-insert');
    },
    didInsertElement : function() {
        $(window).trigger('ember-dom-did-insert');
    },
    willDestroyElement : function() {
        $(window).trigger('ember-dom-will-destroy-element');
    },
    willRerender : function() {
        $(window).trigger('ember-dom-will-rerender');
    }
});