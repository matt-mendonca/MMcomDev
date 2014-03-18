App.Page = DS.Model.extend({
    type: DS.attr('string'),
    template: DS.attr('string'),
    route: DS.attr('string'),
    title: DS.attr('string'),
    body: DS.attr('string'),
    summary_text: function() {
        var max_length = 100,
            summary_text = jQuery(this.get('body')).text();

        summary_text = summary_text.substr(0, max_length);
        summary_text = summary_text.substr(0, Math.min(summary_text.length, summary_text.lastIndexOf(" ")));

        return summary_text + " [...]";
    }.property('body')
});