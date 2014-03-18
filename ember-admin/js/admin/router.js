App.Router.map(function() {
    this.resource('dashboard', { path: '/' });
    this.resource('content');
    this.resource('account');
    this.resource('add-content', { path: '/content/add' });
    this.resource('pages', { path: '/content/pages' }, function() {
        this.resource('page', { path: ':id' });
    });
    this.resource('posts', { path: '/content/posts' }, function() {
        this.resource('post', { path: ':id' });
    });
    this.resource('archives', { path: '/content/archives' }, function() {
        this.resource('archive', { path: ':id' });
    });
});

App.PagesRoute = Ember.Route.extend({
    model: function () {
        return this.store.find('page');
    }
});

App.PageRoute = Ember.Route.extend({
    model: function (params) {
        return this.store.find('page', params.id);
    }
});

App.PostsRoute = Ember.Route.extend({
    model: function () {
        return this.store.find('post');
    }
});

App.PostRoute = Ember.Route.extend({
    model: function (params) {
        return this.store.find('post', params.id);
    }
});

App.ArchivesRoute = Ember.Route.extend({
    model: function () {
        return this.store.find('archive');
    }
});

App.ArchiveRoute = Ember.Route.extend({
    model: function (params) {
        return this.store.find('archive', params.id);
    }
});