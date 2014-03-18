App.AddContentController = Ember.ObjectController.extend({
  createNode: function() {
    var save = this.get('content');

    console.log(save);
  }
});