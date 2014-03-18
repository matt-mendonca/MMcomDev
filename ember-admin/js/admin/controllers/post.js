App.PostController = Ember.ObjectController.extend({
  saveNode: function() {
    //var record = this.store.find('post', this.get('id'));
    //var test = this.store.updateRecord('post', record);

    //console.log(this.store.updateRecord);
    this.get('content').save().then(function(){
      console.log('sucess');
    }, function() {
      console.log('failed');
    });
  }
});